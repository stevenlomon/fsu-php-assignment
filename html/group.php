<?php
  $title = "The Retro Vibe";

  // Skaffa grupp ID från URL som ska vara på format /groups/id. Vi passar även på att göra explicit type casting till int!
  $groupId = (int)($_GET['id'] ?? 0);

  if ($groupId <= 0) {
    // Detta betyder att grupp ID:t är invalid. Redirect till groups.php
    header('Location: /groups.php');
    exit;
  }

  $subheader = "Samlingssida för grupp med id #" . $groupId; // String concatenation med `.`!
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ?></title>
  <h1><?= $title ?></h1>
  <h2><?= $subheader ?></h2>
  <p>PHP <?= phpversion()?> running cleanly</p>
</head>
<body>
  
</body>
</html>