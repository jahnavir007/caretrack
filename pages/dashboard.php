<h1 class="h3 mb-3">Dashboard</h1>
<?php if ($counts): ?>
<div class="row g-3 mb-4">
  <?php foreach (['children' => 'Children', 'parents' => 'Parents', 'staff' => 'Staff', 'activities' => 'Activities logged'] as $k => $label): ?>
  <div class="col-6 col-md-3">
    <div class="card stat"><div class="card-body"><div class="text-muted small text-uppercase"><?= $label ?></div><div class="h3 mb-0"><?= (int) $counts[$k] ?></div></div></div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="row g-4">
  <div class="col-lg-6">
    <div class="card"><div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h2 class="h5 mb-0"><?= $currentUser['role'] === 'parent' ? 'My children' : 'Children' ?></h2>
        <a class="btn btn-sm btn-outline-secondary" href="index.php?page=children">View all</a>
      </div>
      <?php if (!$children): ?><p class="text-muted mb-0">No children on record yet.</p><?php endif; ?>
      <ul class="list-group list-group-flush">
        <?php foreach (array_slice($children, 0, 6) as $c): ?>
        <li class="list-group-item d-flex justify-content-between">
          <a href="index.php?page=child&id=<?= (int) $c['id'] ?>"><?= e($c['first_name'] . ' ' . $c['last_name']) ?></a>
          <span class="text-muted small"><?= e(ageFromDob($c['date_of_birth'])) ?></span>
        </li>
        <?php endforeach; ?>
      </ul>
    </div></div>
  </div>
  <div class="col-lg-6">
    <div class="card"><div class="card-body">
      <h2 class="h5 mb-2">Recent activity</h2>
      <?php if (!$activities): ?><p class="text-muted mb-0">Nothing logged yet.</p><?php endif; ?>
      <ul class="list-group list-group-flush">
        <?php foreach ($activities as $a): ?>
        <li class="list-group-item">
          <span class="badge bg-secondary me-1"><?= e($a['activity_type']) ?></span>
          <strong><?= e($a['first_name'] . ' ' . $a['last_name']) ?></strong> — <?= e($a['description']) ?>
          <div class="text-muted small"><?= e(fmtDateTime($a['occurred_at'])) ?> · by <?= e($a['staff_name']) ?></div>
        </li>
        <?php endforeach; ?>
      </ul>
    </div></div>
  </div>
</div>
