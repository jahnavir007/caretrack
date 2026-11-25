<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 mb-0">Users</h1>
  <a class="btn btn-teal" href="index.php?page=user_new">Add user</a>
</div>
<?php foreach (['Admins' => $admins, 'Staff' => $staff, 'Parents' => $parents] as $title => $rows): ?>
<div class="card mb-3"><div class="card-body">
  <h2 class="h5"><?= $title ?> <span class="badge bg-secondary"><?= count($rows) ?></span></h2>
  <?php if (!$rows): ?><p class="text-muted mb-0">None.</p><?php else: ?>
  <table class="table table-sm mb-0"><thead><tr><th>Name</th><th>Email</th><th>Phone</th></tr></thead><tbody>
    <?php foreach ($rows as $u): ?><tr><td><?= e($u['name']) ?></td><td><?= e($u['email']) ?></td><td><?= e($u['phone'] ?? '—') ?></td></tr><?php endforeach; ?>
  </tbody></table>
  <?php endif; ?>
</div></div>
<?php endforeach; ?>
