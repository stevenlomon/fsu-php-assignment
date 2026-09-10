<?php
  require_once __DIR__ . '/includes/helpers.php';
  require_once __DIR__ . '/includes/db.php';
  require_auth();

  $title = "The Retro Vibe";
  $subheader = "Din profil";

  $userId = (int)$_SESSION['user_id'];

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $username          = trim($_POST['username'] ?? '');
      $firstName         = trim($_POST['first_name'] ?? '');
      $lastName          = trim($_POST['last_name'] ?? '');
      $email             = trim($_POST['email'] ?? '');
      $newPassword       = $_POST['new_password'] ?? '';
      $newPasswordRepeat = $_POST['new_password_repeat'] ?? '';

      // Samma validering som vid registrering
      if (mb_strlen($username) < 3) {
          $_SESSION['error_message'] = "Användarnamnet måste vara minst 3 tecken långt.";
      } elseif (mb_strlen($firstName) < 2) {
          $_SESSION['error_message'] = "Förnamnet måste vara minst 2 tecken långt.";
      } elseif (mb_strlen($lastName) < 2) {
          $_SESSION['error_message'] = "Efternamnet måste vara minst 2 tecken långt.";
      } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
          $_SESSION['error_message'] = "Ange en giltig e-postadress.";
      } elseif ($newPassword !== '' && strlen($newPassword) < 8) {
          $_SESSION['error_message'] = "Det nya lösenordet måste vara minst 8 tecken långt.";
      } elseif ($newPassword !== '' && $newPassword !== $newPasswordRepeat) {
          $_SESSION['error_message'] = "De nya lösenorden matchade inte varandra.";
      }

      if (isset($_SESSION['error_message'])) {
          header('Location: /profile.php');
          exit;
      }

      // Kontrollera om användarnamnet eller e-posten redan används av någon ANNAN
      $checkStmt = $mysqli->prepare("
          SELECT id FROM users 
          WHERE (username = ? OR email = ?) AND id != ? 
          LIMIT 1
      ");
      $checkStmt->bind_param("ssi", $username, $email, $userId);
      $checkStmt->execute();

      if ($checkStmt->get_result()->fetch_assoc()) {
          $_SESSION['error_message'] = "Användarnamnet eller e-postadressen är redan upptagen.";
          header('Location: /profile.php');
          exit;
      }

      // Uppdatera databasen: med eller utan lösenord
      if ($newPassword !== '') {
          $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
          $updateStmt = $mysqli->prepare("
              UPDATE users 
              SET username = ?, fname = ?, lname = ?, email = ?, hashed_password = ? 
              WHERE id = ?
          ");
          $updateStmt->bind_param("sssssi", $username, $firstName, $lastName, $email, $passwordHash, $userId);
      } else {
          $updateStmt = $mysqli->prepare("
              UPDATE users 
              SET username = ?, fname = ?, lname = ?, email = ? 
              WHERE id = ?
          ");
          $updateStmt->bind_param("ssssi", $username, $firstName, $lastName, $email, $userId);
      }

      if (!$updateStmt->execute()) {
          die("Kunde inte uppdatera profilen: " . e($updateStmt->error));
      }

      $_SESSION['success_message'] = "Dina profiluppgifter har sparats!";

      header('Location: /profile.php');
      exit;
  }

  $statement = $mysqli->prepare("
      SELECT id, username, fname, lname, email, created_at 
      FROM users 
      WHERE id = ? 
      LIMIT 1
  ");
  $statement->bind_param("i", $userId);
  $statement->execute();
  $user = $statement->get_result()->fetch_assoc();

  if (!$user) {
      header('Location: /logout.php');
      exit;
  }

  $myGroups = get_user_groups($mysqli, $userId);

  $title = "The Retro Vibe";
  $subheader = "Redigera profil: " . $user['username'];

  $errorMessage = $_SESSION['error_message'] ?? null;
  $successMessage = $_SESSION['success_message'] ?? null;
  unset($_SESSION['error_message'], $_SESSION['success_message']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <title><?= $title ?></title>
</head>
<body>
  <header>
    <p>PHP <?= phpversion()?> running cleanly (consider this an Easter Egg)</p>
    <a href="index.php"><h1><?= e($title) ?></h1></a>
    <h2><?= e($subheader) ?></h2>
  </header>

  <?php if ($errorMessage): ?>
    <p style="color: #ff6b6b; font-weight: bold;"><?= e($errorMessage) ?></p>
  <?php endif; ?>

  <?php if ($successMessage): ?>
    <p style="color: #51cf66; font-weight: bold;"><?= e($successMessage) ?></p>
  <?php endif; ?>

  <h3>Redigera dina uppgifter</h3>
      <form method="POST" action="/profile.php">
        <div>
          <label for="username">Användarnamn</label><br>
          <input 
            type="text" 
            id="username" 
            name="username" 
            value="<?= e($user['username']) ?>" 
            required 
            minlength="3"
          >
        </div>

        <div>
          <label for="first_name">Förnamn</label><br>
          <input 
            type="text" 
            id="first_name" 
            name="first_name" 
            value="<?= e($user['fname']) ?>" 
            required 
            minlength="2"
          >
        </div>

        <div>
          <label for="last_name">Efternamn</label><br>
          <input 
            type="text" 
            id="last_name" 
            name="last_name" 
            value="<?= e($user['lname']) ?>" 
            required 
            minlength="2"
          >
        </div>

        <div>
          <label for="email">E-post</label><br>
          <input 
            type="email" 
            id="email" 
            name="email" 
            value="<?= e($user['email']) ?>" 
            required
          >
        </div>

        <fieldset style="margin-top: 1rem; padding: 1rem;">
          <legend><strong>Ändra lösenord (frivilligt)</strong></legend>
          <p><small>Lämna fälten tomma om du vill behålla ditt nuvarande lösenord.</small></p>

          <!-- `autocomplete="new-password"` är viktigt här på båda så att inte password blir för ifyllt! -->
          <div>
            <label for="new_password">Nytt lösenord</label><br>
            <input 
              type="password" 
              id="new_password" 
              name="new_password" 
              minlength="8"
              autocomplete="new-password"
            >
          </div>

          <div>
            <label for="new_password_repeat">Upprepa nytt lösenord</label><br>
            <input 
              type="password" 
              id="new_password_repeat" 
              name="new_password_repeat"
              autocomplete="new-password"
            >
          </div>
        </fieldset>

        <br>
        <button type="submit">Spara ändringar</button>
      </form>
  
</body>
</html>