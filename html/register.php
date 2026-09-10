<?php
  // Denna fil kommer vara en så kallad self-submitting form; den skickar en POST request till sig själv!

  require_once __DIR__ . '/includes/helpers.php';
  require_once __DIR__ . '/includes/db.php';

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? ''; // Vi använder INTE trim för lösenord! Whitespace kan vara avsiktligt
    $passwordRepeat = $_POST['password_repeat'] ?? '';

    // Data validering:
    // * username måste vara minst 3 karaktärer långt
    // * firstName måste vara minst 2 karaktärer långt
    // * lastName.. måste också vara minst 2 karaktärer långt?
    // * email har en egen validator i php om jag förstår rätt?
    // * Och med det borde password också ha en? 

    // passwords har inte en equivalent till `filter_var($email, FILTER_VALIDATE_EMAIL)`
    // Jag väljer då endast att lösenord ska vara minst 8 karaktärer långt
    // mb_strlen() ser till att vi faktiskt räknar karaktärer och inte bytes!
    if (mb_strlen($username) < 3) {
        $_SESSION['error_message'] = "Användarnamnet måste vara minst 3 tecken långt.";
    } elseif (mb_strlen($firstName) < 2) {
        $_SESSION['error_message'] = "Förnamnet måste vara minst 2 tecken långt.";
    } elseif (mb_strlen($lastName) < 2) {
        $_SESSION['error_message'] = "Efternamnet måste vara minst 2 tecken långt.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "Ange en giltig e-postadress.";
    } elseif (strlen($password) < 8) {
        $_SESSION['error_message'] = "Lösenordet måste vara minst 8 tecken långt.";
    } elseif ($password !== $passwordRepeat) {
        $_SESSION['error_message'] = "Lösenorden matchade inte varandra.";
    }

    // Om något fel uppstod: studsa tillbaka
    if (isset($_SESSION['error_message'])) {
        header('Location: /register.php');
        exit;
    }

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

    <label for="password-repeat">Upprepa Lösenord</label>
    <input id="password-repeat" name="password_repeat" type="password" required />

    <!-- För att se till att båda lösenord matchar "frontend mässigt" kan vi tydligen använda 
     `setCustomValidity` från Browsern's native Constraint Validation API! -->
     <!-- Det här lär bli den enda JavaScript vi använder! -->
     <script>
      const password = document.getElementById('password');
      const passwordRepeat = document.getElementById('password-repeat');

      function checkPasswordMatch() {
        if (passwordRepeat.value !== '' && password.value !== passwordRepeat.value) {
          passwordRepeat.setCustomValidity('Lösenorden matchar inte');
        } else {
          // Empty string resets the validity state to valid
          passwordRepeat.setCustomValidity('');
        }
      }

      password.addEventListener('input', checkPasswordMatch);
      passwordRepeat.addEventListener('input', checkPasswordMatch);
    </script>

    <button type="submit">Skapa konto</button>
  </form>
</body>
</html>