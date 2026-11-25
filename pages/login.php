<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card shadow-sm">
      <div class="card-body">
        <h1 class="h4 mb-3">Log in to CareTrack</h1>
        <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <form method="post" action="index.php?page=login" class="needs-validation" novalidate>
          <?= Auth::csrfField() ?>
          <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input class="form-control" type="email" id="email" name="email" value="<?= e($email) ?>" required>
            <div class="invalid-feedback">Enter a valid email.</div>
          </div>
          <div class="mb-3">
            <label class="form-label" for="password">Password</label>
            <input class="form-control" type="password" id="password" name="password" required minlength="8">
            <div class="invalid-feedback">Password is required (min 8 characters).</div>
          </div>
          <button class="btn btn-teal w-100">Log in</button>
        </form>
        <p class="text-muted small mt-3 mb-0">Demo accounts (password <code>Password123!</code>): admin@caretrack.dev · staff@caretrack.dev · parent@caretrack.dev</p>
      </div>
    </div>
  </div>
</div>
