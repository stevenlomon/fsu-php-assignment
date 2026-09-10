<?php
  require_once __DIR__ . '/includes/helpers.php';
  require_once __DIR__ . '/includes/db.php';

  $title = "The Retro Vibe";

  // Skaffa discussion ID på samma sätt som vi gör i group.php
  $discussionId = (int)($_GET['id'] ?? 0);

  if ($discussionId <= 0) {
    header('Location: /groups.php'); // Säkrare ifall denna bara leder tillbara till groups.php så det inte blir en redirect trap
    exit;
  }

  $discussion = get_discussion($mysqli, $discussionId);
  $subheader = "Diskussions sida: " . $discussion['title'];

  $errorMessage = $_SESSION['error_message'] ?? null;
  unset($_SESSION['error_message']);

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Man ska endast kunna svara på trådar i diskussionen om man är inloggad
    require_auth();

    $userId = (int)$_SESSION['user_id'];
    $content = trim($_POST['content'] ?? '');

    // Är användaren godkänd medlem i gruppens diskussion?
    $membership = get_group_membership($mysqli, (int)$discussion['group_id'], $userId);
    if (!$membership || $membership['status'] !== 'approved') {
        header("Location: /group.php?id=" . (int)$discussion['group_id']);
        exit;
    }

    if (mb_strlen($content) < 2) {
        $_SESSION['error_message'] = "Svaret måste innehålla minst 2 tecken.";
        header("Location: /discussion.php?id={$discussionId}");
        exit;
    }

    // Spara svaret i databasen med reply_to satt till trådens ID
    $statement = $mysqli->prepare("
        INSERT INTO posts (user_id, group_id, title, reply_to, content)
        VALUES (?, ?, NULL, ?, ?)
    ");
    $statement->bind_param("iiis", $userId, $discussion['group_id'], $discussionId, $content);

    if (!$statement->execute()) {
        die("Kunde inte spara svar: " . e($statement->error));
    }

    // Ladda om sidan via GET (Post/Redirect/Get) så svaret visas direkt i listan!
    header("Location: /discussion.php?id={$discussionId}");
    exit;
  }

  $replies = get_discussion_replies($mysqli, $discussionId);
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

  <div>
    <span>(<?= e($discussion['created_at']) ?>)</span>
    <strong><?= e($discussion['username']) ?>:</strong>
    <span><?= e($discussion['content']) ?></span>
  </div>
  
  <!-- Här också: alla svar visas vare sig man är inloggad eller inte -->
  <?php if (empty($replies)): ?>
    <p>Inga svar på denna tråd har skapats ännu.</p>
    <?php if (is_logged_in()): ?>
      <p>Var den första att skapa en!</p>
    <?php else: ?>
      <p><a href="/login.php">Logga in</a> eller <a href="/register.php">skapa ett konto</a> för att vara den första att skapa en!</p>
    <?php endif; ?>

  <?php else: ?>
    <?php foreach ($replies as $reply): ?>
      <div>
        <span>(<?= e($reply['created_at']) ?>)</span>
        <strong><?= e($reply['username']) ?>:</strong>
        <span><?= e($reply['content']) ?></span>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <?php if (is_logged_in()): ?>
   <h3>Bidra till diskussionen!</h3>

   <?php if($errorMessage): ?>
    <p style="color: red;"><?= e($errorMessage) ?></p>
    <?php endif; ?>

   <form method="POST" action="discussion.php?id=<?= $discussionId ?>">
    <div>
      <textarea 
        name="content" 
        rows="4" 
        cols="50" 
        placeholder="Vad vill du svara?" 
        required
      ></textarea>
    </div>
    <button type="submit">Skicka svar</button>
  </form>
  <?php endif; ?>

</body>
</html>