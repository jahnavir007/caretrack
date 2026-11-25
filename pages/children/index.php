<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0">Children</h1>
  <?php if (in_array($currentUser['role'], ['staff', 'admin'], true)): ?>
  <a class="btn btn-teal" href="index.php?page=child_new">Add child</a>
  <?php endif; ?>
</div>
<div class="card"><div class="table-responsive">
<table class="table table-hover mb-0 align-middle">
  <thead class="table-light"><tr><th>Name</th><th>Age</th><th>Allergies</th><th>Key staff</th><th></th></tr></thead>
  <tbody>
  <?php if (!$children): ?><tr><td colspan="5" class="text-muted">No children on record.</td></tr><?php endif; ?>
  <?php foreach ($children as $c): ?>
    <tr>
      <td><a href="index.php?page=child&id=<?= (int) $c['id'] ?>"><?= e($c['first_name'] . ' ' . $c['last_name']) ?></a></td>
      <td><?= e(ageFromDob($c['date_of_birth'])) ?></td>
      <td><?= $c['allergies'] ? '<span class="badge bg-warning text-dark">' . e($c['allergies']) . '</span>' : '<span class="text-muted">None</span>' ?></td>
      <td><?= e($c['staff_name'] ?? '—') ?></td>
      <td class="text-end">
        <?php if (in_array($currentUser['role'], ['staff', 'admin'], true)): ?>
        <a class="btn btn-sm btn-outline-secondary" href="index.php?page=child_edit&id=<?= (int) $c['id'] ?>">Edit</a>
        <a class="btn btn-sm btn-outline-primary" href="index.php?page=activity_new&child_id=<?= (int) $c['id'] ?>">Log</a>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div></div>
