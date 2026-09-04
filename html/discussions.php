<?php
  require_once __DIR__ . '/includes/helpers.php';

  $title = "The Retro Vibe";

  // Skaffa group ID på samma sätt som vi gör i group.php. Här tänker jag att URL är på formatet /discussions.php?groupId=id
  $groupId = (int)($_GET['groupId'] ?? 0);

  if ($groupId <= 0) {
    // Om groupId är invalid gör vi en redirect till.. groups.php här oxå. For now iaf
    header('Location: /groups.php');
    exit;
  }

  $subheader = "Alla diskussioner i grupp med id #" . $groupId;
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