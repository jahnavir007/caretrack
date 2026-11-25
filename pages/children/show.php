<div class="d-flex justify-content-between align-items-start mb-3">
  <div>
    <h1 class="h3 mb-0"><?= e($child['first_name'] . ' ' . $child['last_name']) ?></h1>
    <div class="text-muted">Born <?= e($child['date_of_birth']) ?> (<?= e(ageFromDob($child['date_of_birth'])) ?>) · Key staff: <?= e($child['staff_name'] ?? '—') ?></div>
  </div>
  <?php if (in_array($currentUser['role'], ['staff', 'admin'], true)): ?>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary" href="index.php?page=child_edit&id=<?= (int) $child['id'] ?>">Edit</a>
    <a class="btn btn-teal" href="index.php?page=activity_new&child_id=<?= (int) $child['id'] ?>">Log activity</a>
    <form method="post" action="index.php?page=child_delete" data-confirm="Delete this child and all related activity records? This cannot be undone.">
      <?= Auth::csrfField() ?><input type="hidden" name="id" value="<?= (int) $child['id'] ?>">
      <button class="btn btn-outline-danger">Delete</button>
    </form>
  </div>
  <?php endif; ?>
</div>

<div class="row g-4">
  <div class="col-lg-4">
    <div class="card mb-3"><div class="card-body">
      <h2 class="h6 text-uppercase text-muted">Health</h2>
      <p class="mb-1"><strong>Allergies:</strong> <?= $child['allergies'] ? e($child['allergies']) : 'None recorded' ?></p>
      <p class="mb-0"><strong>Notes:</strong> <?= $child['notes'] ? nl2br(e($child['notes'])) : '—' ?></p>
    </div></div>
    <div class="card"><div class="card-body">
      <h2 class="h6 text-uppercase text-muted">Guardians</h2>
      <?php if (!$child['guardians']): ?><p class="text-muted mb-0">No guardians linked.</p><?php endif; ?>
      <?php foreach ($child['guardians'] as $g): ?>
        <div class="mb-2"><strong><?= e($g['name']) ?></strong><br><span class="small text-muted"><?= e($g['email']) ?><?= $g['phone'] ? ' · ' . e($g['phone']) : '' ?></span></div>
      <?php endforeach; ?>
    </div></div>
  </div>
  <div class="col-lg-8">
    <div class="card"><div class="card-body">
      <h2 class="h5">Activity log</h2>
      <?php if (!$activities): ?><p class="text-muted mb-0">No activities logged yet.</p><?php endif; ?>
      <ul class="list-group list-group-flush">
        <?php foreach ($activities as $a): ?>
        <li class="list-group-item">
          <span class="badge bg-secondary me-1"><?= e($a['activity_type']) ?></span><?= e($a['description']) ?>
          <div class="text-muted small"><?= e(fmtDateTime($a['occurred_at'])) ?> · by <?= e($a['staff_name']) ?></div>
        </li>
        <?php endforeach; ?>
      </ul>
    </div></div>
  </div>
</div>
