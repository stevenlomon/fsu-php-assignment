<?php
  require_once __DIR__ . '/includes/helpers.php';
  require_once __DIR__ . '/includes/db.php';

  $title = "The Retro Vibe";

  // Skaffa group ID på samma sätt som vi gör i group.php. Här tänker jag att URL är på formatet /discussions.php?groupId=id
  $groupId = (int)($_GET['groupId'] ?? 0);

  if ($groupId <= 0) {
    // Om groupId är invalid gör vi en redirect till.. groups.php här oxå. For now iaf
    header('Location: /groups.php');
    exit;
  }

  $group = get_group($mysqli, $groupId);
  if (!$group) {
      header('Location: /groups.php');
      exit;
  }

  $subheader = "Alla diskussioner i gruppen " . $group['name'];

  $discussions = get_discussions_for_group($mysqli, $groupId);

  $errorMessage = $_SESSION['error_message'] ?? null;
  unset($_SESSION['error_message']);

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Man ska endast kunna skapa diskussioner om man är inloggad!
    require_auth();

    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? ''); 
    
    // Simpel validering: titel måste vara minst 3 karaktärer lång, innehåller måste vara minst 10 (for now, låter rimligt?)
    if (strlen($title) < 3) {
      $_SESSION['error_message'] = "Titeln på diskussionen måste vara minst 3 karaktärer långt"; 
      header("Location: /discussions.php?groupId={$groupId}");
      exit;
    }
    if (strlen($content) < 10) {
      $_SESSION['error_message'] = "Innehålle måste vara minst 10 karaktärer långt"; 
      header("Location: /discussions.php?groupId={$groupId}");
      exit;
    }
      
    // På DENNA sida är title *inte* nullable. På discussion.php kommer den vara det. Här kräver vi ett värde på både titel och innehåll
    
    // Vi har valid data för att skapa en diskussion
    // På denna sida är reply_to alltid NULL
    $statment = $mysqli->prepare("
      INSERT INTO posts(user_id, group_id, title, reply_to, content)
      VALUES (?, ?, ?, NULL, ?)
    ");

    // int, int, string, string. Intressant kombination haha. Vi binder aldrig NULL!
    $statment->bind_param("iiss", $_SESSION['user_id'], $groupId, $title, $content);

    // Fail early med `die()`
    if (!$statment->execute()) {
        die("Kunde inte skapa diskussion: " . e($statment->error));
    }

    // Plocka ut det nya grupp-ID:t direkt från MySQLi för redirect
    $newDiscussionId = (int)$mysqli->insert_id;

    // Nu är vi 100% in the clear att vi har ren data i databasen. Redirect till den nyskapade diskusisonen! 
    header("Location: /discussion.php?id={$newDiscussionId}");
    exit;
  }
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

  <!-- Alla diskussioner visas vare sig man är inloggad eller inte bestämmer jag nu -->
  <?php if (empty($discussions)): ?>
    <p>Inga diskussioner har skapats ännu.</p>
    <?php if (is_logged_in()): ?>
      <p>Var den första att skapa en!</p>
    <?php else: ?>
      <p><a href="/login.php">Logga in</a> eller <a href="/register.php">skapa ett konto</a> för att vara den första att skapa en!</p>
    <?php endif; ?>

  <?php else: ?>
    <div>
      <?php foreach ($discussions as $disc): ?>
        <div>
          <h2><?= e($disc['title']) ?></h2>
          <a href="/discussion.php?id=<?= (int)$disc['id'] ?>">Ta del av diskussion</a>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <!-- Formuläret för att skapa en duskussion visas vare sig det finns grupper eller inte. Men bara om man är inloggad! -->
  <?php if (is_logged_in()): ?>
   <h3>Skapa ny diskussion här!</h3>

   <?php if($errorMessage): ?>
    <p style="color: red;"><?= e($errorMessage) ?></p>
    <?php endif; ?>

   <form method="POST" action="discussions.php?groupId=<?= $groupId ?>">
      <label for="title">Titel</label>
      <input id="title" name="title" type="text" required>
      
      <label for="content">Inlägg</label>
      <textarea id="content" name="content" placeholder="Vad har du på ditt gamer hjärta?"></textarea> <!-- 2012 era internet cringe; I *love* it haha -->

      <button type="submit">Skapa diskussion</button>
   </form>
  <?php endif; ?>
  
</body>
</html>