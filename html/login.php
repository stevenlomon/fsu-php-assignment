<?php
  require_once __DIR__ . '/includes/helpers.php';

  $title = "The Retro Vibe";
  $subheader = "Logga in för att diskutera allt från SNES till PS2 idag!";
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