<?php
  require_once __DIR__ . '/includes/helpers.php';

  $title = "The Retro Vibe";

  // Skaffa discussion ID på samma sätt som vi gör i group.php
  $discussionId = (int)($_GET['id'] ?? 0);

  if ($discussionId <= 0) {
    header('Location: /discussions.php');
    exit;
  }

  $subheader = "Diskussions sida med id #" . $discussionId;
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
    <h1><?= e($title) ?></h1>
    <h2><?= e($subheader) ?></h2>
  </header>
  
</body>
</html>