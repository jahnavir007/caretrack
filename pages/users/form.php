<h1 class="h3 mb-3">Add user</h1>
<div class="card"><div class="card-body">
<form method="post" class="needs-validation" novalidate>
  <?= Auth::csrfField() ?>
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label" for="name">Full name</label>
      <input class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" id="name" name="name" value="<?= e($input['name']) ?>" required minlength="2">
      <div class="invalid-feedback"><?= e($errors['name'] ?? 'At least 2 characters.') ?></div>
    </div>
    <div class="col-md-6">
      <label class="form-label" for="email">Email</label>
      <input class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" type="email" id="email" name="email" value="<?= e($input['email']) ?>" required>
      <div class="invalid-feedback"><?= e($errors['email'] ?? 'Enter a valid email.') ?></div>
    </div>
    <div class="col-md-4">
      <label class="form-label" for="role">Role</label>
      <select class="form-select" id="role" name="role">
        <?php foreach (['parent', 'staff', 'admin'] as $r): ?><option value="<?= $r ?>" <?= $input['role'] === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label" for="phone">Phone</label>
      <input class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" id="phone" name="phone" value="<?= e($input['phone'] ?? '') ?>" pattern="^\+?[0-9 ]{7,15}$">
      <div class="invalid-feedback"><?= e($errors['phone'] ?? 'Digits, spaces, optional +.') ?></div>
    </div>
    <div class="col-md-4">
      <label class="form-label" for="password">Temporary password</label>
      <input class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" type="password" id="password" name="password" required minlength="8">
      <div class="invalid-feedback"><?= e($errors['password'] ?? 'At least 8 characters.') ?></div>
    </div>
  </div>
  <div class="mt-4 d-flex gap-2">
    <button class="btn btn-teal">Create user</button>
    <a class="btn btn-outline-secondary" href="index.php?page=users">Cancel</a>
  </div>
</form>
</div></div>
