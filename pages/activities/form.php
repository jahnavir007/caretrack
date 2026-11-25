<h1 class="h3 mb-3">Log activity</h1>
<div class="card"><div class="card-body">
<form method="post" class="needs-validation" novalidate>
  <?= Auth::csrfField() ?>
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label" for="child_id">Child</label>
      <select class="form-select <?= isset($errors['child_id']) ? 'is-invalid' : '' ?>" id="child_id" name="child_id" required>
        <option value="">— select —</option>
        <?php foreach ($children as $c): ?>
        <option value="<?= (int) $c['id'] ?>" <?= (int) $input['child_id'] === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['first_name'] . ' ' . $c['last_name']) ?></option>
        <?php endforeach; ?>
      </select>
      <div class="invalid-feedback"><?= e($errors['child_id'] ?? 'Select a child.') ?></div>
    </div>
    <div class="col-md-6">
      <label class="form-label" for="activity_type">Type</label>
      <select class="form-select" id="activity_type" name="activity_type">
        <?php foreach (['meal', 'nap', 'learning', 'play', 'incident', 'medication', 'other'] as $t): ?>
        <option value="<?= $t ?>" <?= $input['activity_type'] === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label" for="occurred_at">When</label>
      <input class="form-control <?= isset($errors['occurred_at']) ? 'is-invalid' : '' ?>" type="datetime-local" id="occurred_at" name="occurred_at" value="<?= e(str_replace(' ', 'T', substr($input['occurred_at'], 0, 16))) ?>" required>
      <div class="invalid-feedback"><?= e($errors['occurred_at'] ?? 'Enter a valid time.') ?></div>
    </div>
    <div class="col-12">
      <label class="form-label" for="description">Description</label>
      <textarea class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>" id="description" name="description" rows="3" required minlength="3" maxlength="500"><?= e($input['description']) ?></textarea>
      <div class="invalid-feedback"><?= e($errors['description'] ?? '3-500 characters.') ?></div>
    </div>
  </div>
  <div class="mt-4 d-flex gap-2">
    <button class="btn btn-teal">Save activity</button>
    <a class="btn btn-outline-secondary" href="index.php?page=children">Cancel</a>
  </div>
</form>
</div></div>
