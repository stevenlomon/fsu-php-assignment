<?php
  declare(strict_types=1);
  require_once __DIR__ . '/includes/helpers.php';
  require_once __DIR__ . '/includes/db.php'; // För vår SSR!

  $title = "The Retro Vibe";
  $subheader = "Alla grupper. Gå med i en idag!";

  session_start(); // För felmeddelanden när vi skickar POST till filen själv: PRG

  $errorMessage = $_SESSION['error_message'] ?? null;
  unset($_SESSION['error_message']);

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? ''); 
    
    // Simpel validering: namn måste vara minst 3 karaktärer lång
    if (strlen($name) < 3) {
      $errorMessage = "Namnet på gruppen måste vara minst 3 karaktärer långt";
      header('Location: /groups.php');
      exit;
      }
      
    $description = $description === '' ? null : $description; // `description` är nullable i databasen. Om den saknas i vår POST sätter vi den explicitly till NULL
    
    // Vi har valid data för att skapa en grupp. Lägg in gruppen i databasen
    $statement = $mysqli->prepare("
      INSERT INTO groups(name, description)
      VALUES (?, ?)
    ");

    $statement->bind_param("ss", $name, $description);

    if($statement->execute()) {
      header('Location: /groups.php');
      exit;
    } else {
      echo "Database Error: " . e($statement->error);
    }
  }

  // Hämta alla grupper direkt på servern som en array av associativa arrayer!
  $result = $mysqli->query("
    SELECT id, name, description, created_at
    FROM groups
    ORDER BY created_at DESC
  ");
  $groups = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
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

  <?php if (empty($groups)): ?>
    <p>Inga grupper har skapats ännu. Var den första att skapa en!</p>
  <?php else: ?>
    <div>
      <?php foreach ($groups as $group): ?>
        <div>
          <h2><?= e($group['name']) ?></h2>
          <p><?= e($group['description']) ?></p>
          <a href="/group.php?id=<?= (int)$group['id'] ?>">Besök grupp</a>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <!-- Formuläret för att skapa en grupp visas vare sig det finns grupper eller inte -->
   <h3>Skapar du hellre en egen grupp för att diskutera något som inte finns ovan? Gör det här!</h3>

   <?php if($errorMessage): ?>
    <p style="color: red;"><?= e($errorMessage) ?></p>
    <?php endif; ?>

   <form method="POST" action="groups.php">
      <label for="name">Namn</label>
      <input id="name" name="name" type="text" required>
      
      <label for="description">Beskrivning</label>
      <textarea id="description" name="description" type="" placeholder="Vad vill du ska diskuteras i denna grupp?"></textarea>

      <button type="submit">Skapa grupp</button>
   </form>
</body>
</html>