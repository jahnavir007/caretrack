<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CareTrack</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/app.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-teal mb-4">
  <div class="container">
    <a class="navbar-brand fw-semibold" href="index.php?page=dashboard">CareTrack</a>
    <?php if ($currentUser): ?>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="index.php?page=dashboard">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php?page=children">Children</a></li>
        <?php if (in_array($currentUser['role'], ['staff', 'admin'], true)): ?>
        <li class="nav-item"><a class="nav-link" href="index.php?page=activity_new">Log activity</a></li>
        <?php endif; ?>
        <?php if ($currentUser['role'] === 'admin'): ?>
        <li class="nav-item"><a class="nav-link" href="index.php?page=users">Users</a></li>
        <?php endif; ?>
      </ul>
      <span class="navbar-text me-3"><?= e($currentUser['name']) ?> <span class="badge bg-light text-dark"><?= e($currentUser['role']) ?></span></span>
      <a class="btn btn-outline-light btn-sm" href="index.php?page=logout">Log out</a>
    </div>
    <?php endif; ?>
  </div>
</nav>
<main class="container pb-5">
<?php if (!empty($flash)): ?>
  <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible" role="alert">
    <?= e($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>
