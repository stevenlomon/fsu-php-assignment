<?php
  require_once __DIR__ . '/includes/helpers.php';
  require_once __DIR__ . '/includes/db.php';

  $title = "The Retro Vibe";

  // Skaffa grupp ID från URL som ska vara på format /groups/id. Vi passar även på att göra explicit type casting till int!
  $groupId = (int)($_GET['id'] ?? 0);

  if ($groupId <= 0) {
    // Detta betyder att grupp ID:t är invalid. Redirect till groups.php
    header('Location: /groups.php');
    exit;
  }

  // Om vi når denna rad har vi ett valid groupId. Använd det för att plocka ut den matchande gruppen ur databasen! (Om den finns)
  // Det som skiljer detta från groups.php är att där kör vi en "GET all" och kan köra $mysqli->query direkt. 
  // Här har vi ett condition: WHERE id = groupId. Så vi kör prepared statement här. Denna är mer lik login.php på det sättet
  $statement = $mysqli->prepare("
    SELECT name, description
    FROM groups
    WHERE id = ?
    LIMIT 1
  ");
  // Här ska vi dock inte använda "s" eftersom groupId är en integer! Unless.. innan denna kan vi cast från int till string? Let's ask AI
  // Vi kan använda "i" för integer! Let's go
  $statement->bind_param("i", $groupId);

  // Execute, get results och lagra i en associative array!
  $statement->execute();
  $result = $statement->get_result(); // Jag ser nu att i groups.php kan vi "skip ahead" till detta steg i och med att vi inte behöver prepared statements!
  $group = $result->fetch_assoc();

  // Nu med $group kan vi istället för $groupId använda..
  $subheader = "Samlingssida för " . $group['name']; // String concatenation med `.`!
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

  <form method="POST" action="/apply_group.php">
    <input type="hidden" name="group_id" value="<?= $groupId ?>" />
    <button type="submit">Ansök om medlemskap</button>
  </form>
  
</body>
</html>