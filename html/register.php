<?php
  // Denna fil kommer vara en så kallad self-submitting form; den skickar en POST request till sig själv!

  require_once __DIR__ . '/db.php';

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($POST['username'] ?? ''); //TODO: Varför blir denna tom i databasen?
    $password = trim($POST['password'] ?? '');

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Default sätt att hasha lösenord i php tydligen!

    // *Det* simplaste sättet vi kan insert data i vår databas verkar det som
    // Bör uppgraderas till prepared statements när vi får detta att funka!
    $sql = "INSERT INTO users (username, email, hashed_password, fname, lname)
            VALUES ('$username', NULL, '$hashedPassword', NULL, NULL)";

    if($mysqli->query($sql)) {
      header('Location: /login.php');
      exit;
    } else {
      echo "Database Error: " . $mysqli->error;
    }
  }

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
    <input id="username" name="username" type="text" required />

    <label for="password">Lösenord</label>
    <input id="password" name="password" type="password" required />

    <label for="password-repeat">Upprepa Lösenord</label>
    <input id="password-repeat" name="password_repeat" type="password" required />

    <button type="submit">Skapa konto</button>
  </form>
</body>
</html>