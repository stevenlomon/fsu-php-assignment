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
  }

  $replies = get_discussion_replies($mysqli, $discussionId);
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

  <div>
    <span>(<?= e($discussion['created_at']) ?>)</span>
    <strong><?= e($discussion['username']) ?>:</strong>
    <span><?= e($discussion['content']) ?></span>

    <form method="POST" action="/reply.php" style="display:inline;">
      <input type="hidden" name="discussion_id" value="<?= $discussionId ?>" />
      <button type="submit">Svara</button>
    </form>
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

        <form method="POST" action="/reply.php" style="display:inline;">
          <input type="hidden" name="discussion_id" value="<?= $discussionId ?>" />
          <button type="submit">Svara</button>
        </form>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <?php if (is_logged_in()): ?>
   <h3>Bidra till diskussionen!</h3>

   <?php if($errorMessage): ?>
    <p style="color: red;"><?= e($errorMessage) ?></p>
    <?php endif; ?>

   <form method="POST" action="discussion.php?id=<?= $discussionId ?>">
      <label for="content">Inlägg</label>
      <textarea id="content" name="content" placeholder="Vilken ny tråd vill du starta?"></textarea>

      <button type="submit">Skicka</button>
   </form>
  <?php endif; ?>

</body>
</html>