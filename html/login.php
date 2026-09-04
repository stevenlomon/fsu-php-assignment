<?php
  require_once __DIR__ . '/includes/helpers.php';
  require_once __DIR__ . '/includes/db.php';

  $title = "The Retro Vibe";
  $subheader = "Logga in för att diskutera allt från SNES till PS2 idag!";

  if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Hitta matchande användare i vår databas. Vi kommer behöva göra detta i två steg! Eftersom användarnas lösenord är 
    // hashade *one-way* kan vi INTEköra en `WHERE username = ? AND hashed_password = ?`.
    // Vi måste matcha på username först *och sen* verifiera plain-text input:et för lösenordet mot det lagrade hashade
    // lösenordet
    $statement = $mysqli->prepare("
      SELECT id, username, hashed_password
      FROM users
      WHERE username = ?
      LIMIT 1
    ");
    $statement->bind_param("s", $username);
    
    // Nu kan vi tydeligen köra en `execute()` utan att deklarera det som en variabel, hämta resultatet 
    // och lagra det som en associative array!
    $statement->execute();
    $result = $statement->get_result();
    $user = $result->fetch_assoc();

    // Nu när vi har vår user verifierar vi lösenordet mot det hashade lösenordet i databasen
    if ($user && password_verify($password, $user['hashed_password'])) {
      // TODO: Sätt user ID som session ID!

      header('Location: /index.php');
      exit;
    } else {
      // To make up för att jag ignorerade säkerhet helt i min tidigare inlämningsuppgift kommer jag
      // prioritera säkerhet och göra det till en non-negotiable här haha. Oxå för att fördjupa mina
      // security best practices across programming languages för att compare and contrast
      $errorMessage = "Felaktigt användarnam eller lösenord"; // TODO: Integrerar denna ordentligt för felhantering
    }
  }
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

  <!-- Endast username och password för att logga in. For now -->
  <form method="POST" action="login.php">
    <label for="username">Användarnamn</label>
    <input id="username" name="username" type="text" required />

    <label for="password">Lösenord</label>
    <input id="password" name="password" type="password" required />

    <button type="submit">Logga in</button>
  </form>
</body>
</html>