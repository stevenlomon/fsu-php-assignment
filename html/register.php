<?php
  // Denna fil kommer vara en så kallad self-submitting form; den skickar en POST request till sig själv!
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
</head>
<body>
  <form method="POST" action="register.php">
    <label for="username">Användarnamn</label>
    <input id="username" name="username" type="text" />

    <label for="email">Email</label>
    <input id="email" name="email" type="text" />

    <label for="password">Lösenord</label>
    <input id="password" name="password" type="password" />

    <label for="password-repeat">Upprepa Lösenord</label>
    <input id="password-repeat" name="password-repeat" type="password" />

    <button type="submit">Skapa konto</button>
  </form>
</body>
</html>