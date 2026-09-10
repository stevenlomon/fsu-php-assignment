<?php
  require_once __DIR__ . '/includes/helpers.php';
  require_once __DIR__ . '/includes/db.php';
  require_auth();

  $title = "The Retro Vibe";
  $subheader = "Dina grupper";

  // require_auth() ser till att vi är inloggad och get_user_groups() garanterar en array så denna kan vara en rad!
  $myGroups = get_user_groups($mysqli, (int)$_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <title><?= $title ?></title>
</head>
<body>
  <header>
    <p>PHP <?= phpversion()?> running cleanly (consider this an Easter Egg)</p>
    <a href="index.php"><h1><?= e($title) ?></h1></a>
    <h2><?= e($subheader) ?></h2>
  </header>

  <?php if (empty($myGroups)): ?>
    <p>Du är inte medlem i några grupper ännu.</p>
  <?php else: ?>
    <ul>
      <?php foreach ($myGroups as $group): ?>
        <li>
          <a href="/group.php?id=<?= (int)$group['id'] ?>">
            <strong><?= e($group['name']) ?></strong>
          </a>
          <span>- Roll: <?= e($group['role']) ?></span>
          <p><?= e($group['description']) ?></p>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
  
</body>
</html>