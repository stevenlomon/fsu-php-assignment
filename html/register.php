<?php
  // Denna fil kommer vara en så kallad self-submitting form; den skickar en POST request till sig själv!

  require_once __DIR__ . '/includes/helpers.php';
  require_once __DIR__ . '/includes/db.php';

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // TODO: Backend validering av user input data

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Default sätt att hasha lösenord i php tydligen!

    // Uppgradering till prepared statements för security best practice. 
    // Funkar liknande till hur det funkar med Postgres + Next.js!
    $statement = $mysqli->prepare("
          INSERT INTO users (username, email, hashed_password, fname, lname)
          VALUES (?, ?, ?, ?, ?)
        ");

    // "sssss" ser löjligt ut haha men betyder att vi tar emot 5st string argument
    $statement->bind_param("sssss", $username, $email, $hashedPassword, $firstName, $lastName);

    if($statement->execute()) {
      header('Location: /login.php');
      exit;
    } else {
      echo "Database Error: " . e($statement->error);
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
</head>
<body>
  <header>
    <p>PHP <?= phpversion()?> running cleanly</p>
    <a href="index.php"><h1><?= e($title) ?></h1></a>
    <h2><?= e($subheader) ?></h2>
  </header>

  <form method="POST" action="register.php">
    <label for="username">Användarnamn</label>
    <input id="username" name="username" type="text" required />

    <label for="first-name">Förnamn</label>
    <input id="first-name" name="first_name" type="text" required />

    <label for="last-name">Efternamn</label>
    <input id="last-name" name="last_name" type="text" required />

    <label for="email">Email</label>
    <input id="email" name="email" type="email" required />

    <label for="password">Lösenord</label>
    <input id="password" name="password" type="password" required />

    <!-- TODO: Frontend error validering ifall dessa två inte matchar -->
    <label for="password-repeat">Upprepa Lösenord</label>
    <input id="password-repeat" name="password_repeat" type="password" required />

    <button type="submit">Skapa konto</button>
  </form>
</body>
</html>