<?php
  require_once __DIR__ . '/includes/helpers.php';
  require_once __DIR__ . '/includes/db.php';

  $title = "The Retro Vibe";

  // Skaffa discussion ID på samma sätt som vi gör i group.php
  $discussionId = (int)($_GET['id'] ?? 0);

  if ($discussionId <= 0) {
    header('Location: /groups.php'); // Säkrare ifall denna bara leder tillbara till groups.php så det inte blir en redirect trap
    exit;
  }

  $discussion = get_discussion($mysqli, $discussionId);
  $subheader = "Diskussions sida: " . $discussion['title'];

  $replies = get_discussion_replies($mysqli, $discussionId);
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

  <p>
    <span>(<?= e($discussion['created_at']) ?>)</span>
    <strong><?= e($discussion['username']) ?>:</strong>
    <span><?= e($discussion['content']) ?></span>
  </p>
  
  <!-- Här också: alla svar visas vare sig man är inloggad eller inte -->
  <?php if (empty($replies)): ?>
    <p>Inga svar på denna tråd har skapats ännu.</p>
    <?php if (is_logged_in()): ?>
      <p>Var den första att skapa en!</p>
    <?php else: ?>
      <p><a href="/login.php">Logga in</a> eller <a href="/register.php">skapa ett konto</a> för att vara den första att skapa en!</p>
    <?php endif; ?>

  <?php else: ?>
    <div>
      <?php foreach ($replies as $reply): ?>
        <p>
          <span>(<?= e($reply['created_at']) ?>)</span>
          <strong><?= e($reply['username']) ?>:</strong>
          <span><?= e($reply['content']) ?></span>
        </p>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</body>
</html>