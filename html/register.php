<?php
  $title = "The Retro Vibe";
  $subheader = "Registrera dig för att diskutera allt från SNES till PS2 idag!";
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ?></title>
  <p>PHP <?= phpversion()?> running cleanly</p>
  <h1><?= $title ?></h1>
  <h2><?= $subheader ?></h2>

  <form action="">
    <label htmlfor="username">Användarnamn</label>
    <input id="username" type="text" />

    <label htmlfor="email">Email</label>
    <input id="email" type="text" />

    <label htmlfor="password">Lösenord</label>
    <input id="password" type="password" />

    <label htmlfor="password-repeat">Upprepa Lösenord</label>
    <input id="password-repeat" type="password" />
  </form>
</head>
<body>
  
</body>
</html>