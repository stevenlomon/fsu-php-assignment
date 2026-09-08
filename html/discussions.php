<?php
  require_once __DIR__ . '/includes/helpers.php';
  require_once __DIR__ . '/includes/db.php';

  $title = "The Retro Vibe";

  // Skaffa group ID på samma sätt som vi gör i group.php. Här tänker jag att URL är på formatet /discussions.php?groupId=id
  $groupId = (int)($_GET['groupId'] ?? 0);

  if ($groupId <= 0) {
    // Om groupId är invalid gör vi en redirect till.. groups.php här oxå. For now iaf
    header('Location: /groups.php');
    exit;
  }

  $group = get_group($mysqli, $groupId);

  $subheader = "Alla diskussioner i gruppen " . $group['name'];

  $discussions = get_discussions_for_group($mysqli, $groupId);
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

  <!-- Alla diskussioner visas vare sig man är inloggad eller inte bestämmer jag nu -->
  <?php if (empty($discussions)): ?>
    <p>Inga diskussioner har skapats ännu.</p>
    <?php if (is_logged_in()): ?>
      <p>Var den första att skapa en!</p>
    <?php else: ?>
      <p><a href="/login.php">Logga in</a> eller <a href="/register.php">skapa ett konto</a> för att vara den första att skapa en!</p>
    <?php endif; ?>

  <?php else: ?>
    <div>
      <?php foreach ($discussions as $disc): ?>
        <div>
          <h2><?= e($disc['title']) ?></h2>
          <a href="/discussion.php?id=<?= (int)$disc['id'] ?>">Ta del av diskussion</a>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  
</body>
</html>