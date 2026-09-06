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
    // Man ska endast kunna skapa grupper om man är inloggad!
    require_auth();

    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? ''); 
    
    // Simpel validering: namn måste vara minst 3 karaktärer lång
    if (strlen($name) < 3) {
      $_SESSION['error_message'] = "Namnet på gruppen måste vara minst 3 karaktärer långt"; // `$_SESSION['error_message']`, inte `$errorMessage`!
      header('Location: /groups.php');
      exit;
      }
      
    $description = $description === '' ? null : $description; // `description` är nullable i databasen. Om den saknas i vår POST sätter vi den explicitly till NULL
    
    // Vi har valid data för att skapa en grupp. Lägg in gruppen i databasen och direkt efter; lägg till den inloggade användaren som 
    // admin i den skapade gruppen! Detta skulle kunna uppgraderas till en transaction med $mysqli->begin_transaction();
    $groupStatement = $mysqli->prepare("
      INSERT INTO groups(name, description)
      VALUES (?, ?)
    ");

    $groupStatement->bind_param("ss", $name, $description);

    // Fail early med `die()`!
    if (!$groupStatement->execute()) {
        die("Kunde inte skapa grupp: " . e($groupStatement->error));
    }

    // Plocka ut det nya grupp-ID:t direkt från MySQLi
    $newGroupId = (int)$mysqli->insert_id;

    // Nu med detta id lägger vi in den inloggade användaren som admin i group_members!
    $userId = (int)$_SESSION['user_id'];

    // joined_at är den enda timestamp i vår databas som kan vara NULL och *inte* hanteras automatiskt av databasen! 
    // Vi måste manuellt sätta den till NOW() här
    $memberStatement = $mysqli->prepare("
        INSERT INTO group_members (user_id, group_id, status, role, joined_at)
        VALUES (?, ?, 'approved', 'admin', NOW())
    ");
    $memberStatement->bind_param("ii", $userId, $newGroupId);

    if (!$memberStatement->execute()) {
        die("Kunde inte knyta användare till grupp: " . e($memberStatement->error));
    }

    // Nu är vi 100% in the clear att vi har ren data i databasen. Redirect till den nyskapade gruppen! 
    header("Location: /group.php?id={$newGroupId}");
    exit;
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

  <!-- Formuläret för att skapa en grupp visas vare sig det finns grupper eller inte. Men bara om man är inloggad! -->
  <?php if (is_logged_in()): ?>
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
  <?php else: ?>
  <p><a href="/login.php">Logga in</a> eller <a href="/register.php">skapa ett konto</a> för att starta en egen grupp.</p>
  <?php endif; ?>
</body>
</html>