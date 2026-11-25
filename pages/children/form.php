<h1 class="h3 mb-3"><?= $child ? 'Edit child' : 'Add child' ?></h1>
<div class="card"><div class="card-body">
<form method="post" class="needs-validation" novalidate>
  <?= Auth::csrfField() ?>
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label" for="first_name">First name</label>
      <input class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>" id="first_name" name="first_name" value="<?= e($input['first_name']) ?>" required minlength="2">
      <div class="invalid-feedback"><?= e($errors['first_name'] ?? 'At least 2 characters.') ?></div>
    </div>
    <div class="col-md-6">
      <label class="form-label" for="last_name">Last name</label>
      <input class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>" id="last_name" name="last_name" value="<?= e($input['last_name']) ?>" required minlength="2">
      <div class="invalid-feedback"><?= e($errors['last_name'] ?? 'At least 2 characters.') ?></div>
    </div>
    <div class="col-md-4">
      <label class="form-label" for="date_of_birth">Date of birth</label>
      <input class="form-control <?= isset($errors['date_of_birth']) ? 'is-invalid' : '' ?>" type="date" id="date_of_birth" name="date_of_birth" value="<?= e($input['date_of_birth']) ?>" max="<?= date('Y-m-d') ?>" required>
      <div class="invalid-feedback"><?= e($errors['date_of_birth'] ?? 'Enter a valid past date.') ?></div>
    </div>
    <div class="col-md-8">
      <label class="form-label" for="allergies">Allergies</label>
      <input class="form-control <?= isset($errors['allergies']) ? 'is-invalid' : '' ?>" id="allergies" name="allergies" value="<?= e($input['allergies'] ?? '') ?>" maxlength="255" placeholder="e.g. peanuts, dairy">
      <div class="invalid-feedback"><?= e($errors['allergies'] ?? '') ?></div>
    </div>
    <div class="col-md-6">
      <label class="form-label" for="staff_id">Key staff member</label>
      <select class="form-select" id="staff_id" name="staff_id">
        <option value="">— none —</option>
        <?php foreach ($staff as $s): ?>
        <option value="<?= (int) $s['id'] ?>" <?= (int) ($input['staff_id'] ?? 0) === (int) $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label" for="parent_ids">Guardians (hold Ctrl/Cmd to select several)</label>
      <select class="form-select" id="parent_ids" name="parent_ids[]" multiple size="4">
        <?php foreach ($parents as $p): ?>
        <option value="<?= (int) $p['id'] ?>" <?= in_array((int) $p['id'], array_map('intval', $selected), true) ? 'selected' : '' ?>><?= e($p['name']) ?> (<?= e($p['email']) ?>)</option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-12">
      <label class="form-label" for="notes">Notes</label>
      <textarea class="form-control" id="notes" name="notes" rows="3"><?= e($input['notes'] ?? '') ?></textarea>
    </div>
  </div>
  <div class="mt-4 d-flex gap-2">
    <button class="btn btn-teal"><?= $child ? 'Save changes' : 'Add child' ?></button>
    <a class="btn btn-outline-secondary" href="index.php?page=children">Cancel</a>
  </div>
</form>
</div></div>
