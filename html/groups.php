<?php
  declare(strict_types=1);
  require_once __DIR__ . '/includes/helpers.php';
  require_once __DIR__ . '/includes/db.php'; // För vår SSR!

  $title = "The Retro Vibe";
  $subheader = "Alla grupper. Gå med i en idag!";

  // Hämta alla grupper direkt på servern som en array av associativa arrayer!
  $result = $mysqli->query("
    SELECT id, name, description, created_at
    FROM groups
    ORDER BY created_at DESC
  ");
  $groups = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ?></title>
</head>
<body>
  <header>
    <p>PHP <?= phpversion()?> running cleanly</p>
    <a href="index.php"><h1><?= e($title) ?></h1></a>
    <h2><?= e($subheader) ?></h2>
  </header>

  <?php if (empty($groups)): ?>
    <p>Inga grupper har skapats ännu. Var den första att skapa en!</p>
  <?php else: ?>
    <div>
      <?php foreach ($groups as $group): ?>
        <div>
          <h2><?= e($group['name']) ?></h2>
          <p><?= e($group['description']) ?></p>
          <a href="/group.php?id=<?= (int)$group['id'] ?>">Besök grupp</a>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</body>
</html>