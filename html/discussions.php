<?php
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
  <h1><?= $title ?></h1>
  <h2><?= $subheader ?></h2>
  <p>PHP <?= phpversion()?> running cleanly</p>
</head>
<body>
  
</body>
</html>