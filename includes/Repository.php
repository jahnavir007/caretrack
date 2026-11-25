<?php
declare(strict_types=1);

/**
 * All database access lives here (prepared statements only).
 * Multi-table writes run in transactions so related rows stay consistent.
 */
final class Repository
{
    public function __construct(private PDO $pdo) {}

    // ---------------------------------------------------------------- users
    public function createUser(string $name, string $email, string $password, string $role, ?string $phone = null): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO users (name, email, password_hash, role, phone) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([trim($name), strtolower(trim($email)), password_hash($password, PASSWORD_DEFAULT), $role, $phone ?: null]);
        return (int) $this->pdo->lastInsertId();
    }

    public function findUserByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([strtolower(trim($email))]);
        return $stmt->fetch() ?: null;
    }

    public function findUser(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, name, email, role, phone, created_at FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function usersByRole(string $role): array
    {
        $stmt = $this->pdo->prepare('SELECT id, name, email, phone FROM users WHERE role = ? ORDER BY name');
        $stmt->execute([$role]);
        return $stmt->fetchAll();
    }

    public function verifyLogin(string $email, string $password): ?array
    {
        $user = $this->findUserByEmail($email);
        if ($user && password_verify($password, $user['password_hash'])) {
            unset($user['password_hash']);
            return $user;
        }
        return null;
    }

    // ------------------------------------------------------------- children
    /** Create a child and link guardians in ONE transaction. */
    public function createChild(array $data, array $parentIds): int
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO children (first_name, last_name, date_of_birth, allergies, notes, staff_id) VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                trim($data['first_name']), trim($data['last_name']), $data['date_of_birth'],
                $data['allergies'] ?: null, $data['notes'] ?: null, $data['staff_id'] ?: null,
            ]);
            $childId = (int) $this->pdo->lastInsertId();
            $this->replaceGuardians($childId, $parentIds);
            $this->pdo->commit();
            return $childId;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /** Update child details and guardian links atomically. */
    public function updateChild(int $id, array $data, array $parentIds): void
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                'UPDATE children SET first_name = ?, last_name = ?, date_of_birth = ?, allergies = ?, notes = ?, staff_id = ? WHERE id = ?'
            );
            $stmt->execute([
                trim($data['first_name']), trim($data['last_name']), $data['date_of_birth'],
                $data['allergies'] ?: null, $data['notes'] ?: null, $data['staff_id'] ?: null, $id,
            ]);
            $this->replaceGuardians($id, $parentIds);
            $this->pdo->commit();
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function replaceGuardians(int $childId, array $parentIds): void
    {
        $this->pdo->prepare('DELETE FROM child_parent WHERE child_id = ?')->execute([$childId]);
        $ins = $this->pdo->prepare('INSERT INTO child_parent (child_id, parent_id) VALUES (?, ?)');
        foreach (array_unique(array_map('intval', $parentIds)) as $pid) {
            if ($pid > 0) {
                $ins->execute([$childId, $pid]);
            }
        }
    }

    public function deleteChild(int $id): void
    {
        // child_parent and activities rows go with it via ON DELETE CASCADE
        $this->pdo->prepare('DELETE FROM children WHERE id = ?')->execute([$id]);
    }

    public function findChild(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT c.*, s.name AS staff_name FROM children c LEFT JOIN users s ON s.id = c.staff_id WHERE c.id = ?'
        );
        $stmt->execute([$id]);
        $child = $stmt->fetch();
        if (!$child) {
            return null;
        }
        $g = $this->pdo->prepare(
            'SELECT u.id, u.name, u.email, u.phone FROM child_parent cp JOIN users u ON u.id = cp.parent_id WHERE cp.child_id = ? ORDER BY u.name'
        );
        $g->execute([$id]);
        $child['guardians'] = $g->fetchAll();
        return $child;
    }

    /** Staff/admin: every child. Parent: only their own children. */
    public function listChildrenFor(array $user): array
    {
        if ($user['role'] === 'parent') {
            $stmt = $this->pdo->prepare(
                'SELECT c.*, s.name AS staff_name FROM children c
                 JOIN child_parent cp ON cp.child_id = c.id
                 LEFT JOIN users s ON s.id = c.staff_id
                 WHERE cp.parent_id = ? ORDER BY c.last_name, c.first_name'
            );
            $stmt->execute([$user['id']]);
            return $stmt->fetchAll();
        }
        return $this->pdo->query(
            'SELECT c.*, s.name AS staff_name FROM children c LEFT JOIN users s ON s.id = c.staff_id ORDER BY c.last_name, c.first_name'
        )->fetchAll();
    }

    public function parentCanSeeChild(int $parentId, int $childId): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM child_parent WHERE parent_id = ? AND child_id = ?');
        $stmt->execute([$parentId, $childId]);
        return (bool) $stmt->fetchColumn();
    }

    // ----------------------------------------------------------- activities
    public function logActivity(int $childId, int $staffId, string $type, string $description, string $occurredAt): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO activities (child_id, staff_id, activity_type, description, occurred_at) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$childId, $staffId, $type, trim($description), date('Y-m-d H:i:s', strtotime($occurredAt))]);
        return (int) $this->pdo->lastInsertId();
    }

    public function activitiesForChild(int $childId, int $limit = 50): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT a.*, u.name AS staff_name FROM activities a JOIN users u ON u.id = a.staff_id
             WHERE a.child_id = ? ORDER BY a.occurred_at DESC LIMIT ' . (int) $limit
        );
        $stmt->execute([$childId]);
        return $stmt->fetchAll();
    }

    public function recentActivities(array $user, int $limit = 20): array
    {
        if ($user['role'] === 'parent') {
            $stmt = $this->pdo->prepare(
                'SELECT a.*, u.name AS staff_name, c.first_name, c.last_name FROM activities a
                 JOIN users u ON u.id = a.staff_id JOIN children c ON c.id = a.child_id
                 JOIN child_parent cp ON cp.child_id = c.id AND cp.parent_id = ?
                 ORDER BY a.occurred_at DESC LIMIT ' . (int) $limit
            );
            $stmt->execute([$user['id']]);
            return $stmt->fetchAll();
        }
        return $this->pdo->query(
            'SELECT a.*, u.name AS staff_name, c.first_name, c.last_name FROM activities a
             JOIN users u ON u.id = a.staff_id JOIN children c ON c.id = a.child_id
             ORDER BY a.occurred_at DESC LIMIT ' . (int) $limit
        )->fetchAll();
    }

    public function counts(): array
    {
        return [
            'children'   => (int) $this->pdo->query('SELECT COUNT(*) FROM children')->fetchColumn(),
            'parents'    => (int) $this->pdo->query("SELECT COUNT(*) FROM users WHERE role = 'parent'")->fetchColumn(),
            'staff'      => (int) $this->pdo->query("SELECT COUNT(*) FROM users WHERE role IN ('staff','admin')")->fetchColumn(),
            'activities' => (int) $this->pdo->query('SELECT COUNT(*) FROM activities')->fetchColumn(),
        ];
    }
}
