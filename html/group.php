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

  // // Om vi når denna rad har vi ett valid groupId. Använd det för att plocka ut den matchande gruppen ur databasen! (Om den finns)
  // // Det som skiljer detta från groups.php är att där kör vi en "GET all" och kan köra $mysqli->query direkt. 
  // // Här har vi ett condition: WHERE id = groupId. Så vi kör prepared statement här. Denna är mer lik login.php på det sättet
  // $statement = $mysqli->prepare("
  //   SELECT name, description
  //   FROM groups
  //   WHERE id = ?
  //   LIMIT 1
  // ");
  // // Här ska vi dock inte använda "s" eftersom groupId är en integer! Unless.. innan denna kan vi cast från int till string? Let's ask AI
  // // Vi kan använda "i" för integer! Let's go
  // $statement->bind_param("i", $groupId);

  // // Execute, get results och lagra i en associative array!
  // $statement->execute();
  // $result = $statement->get_result(); // Jag ser nu att i groups.php kan vi "skip ahead" till detta steg i och med att vi inte behöver prepared statements!
  // $group = $result->fetch_assoc();

  // Eftersom vi vill använda exakt samma kod som ovan nu i discussions.php; kalla på vår helper istället!
  $group = get_group($mysqli, $groupId);

  // Nu med $group kan vi istället för $groupId använda..
  $subheader = "Samlingssida för " . $group['name']; // String concatenation med `.`!

  $members = get_group_members($mysqli, $groupId);

  $membership = null;
  if (is_logged_in()) {
      $membership = get_group_membership($mysqli, $groupId, (int)$_SESSION['user_id']);
  }

  // Följande körs endast om den inloggade användaren är admin över gruppen i fråga!
  $pendingApplications = [];
  if ($membership && $membership['role'] === 'admin') {
    $pendingApplications = get_pending_group_applications($mysqli, $groupId);
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

  <!-- Detta visas både för gäster och inloggade -->
  <h3>Medlemmar</h3>
    <section>
      <?php if (empty($members)): ?>
        <p>Inga godkända medlemmar i gruppen än.</p>
      <?php else: ?>
        <ul>
          <?php foreach ($members as $member): ?>
            <li>
              <strong><?= e($member['username']) ?></strong>
              <span>— Roll: <?= e($member['role']) ?></span>
              <?php if (!empty($member['joined_at'])): ?>
                <small>(Gick med: <?= e(date('Y-m-d', strtotime($member['joined_at']))) ?>)</small>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>
  
  <a href="/discussions.php?groupId=<?=$groupId?>">Se alla diskussioner</a>

  <?php if (!is_logged_in()): ?>

    <p><a href="/login.php">Logga in</a> eller <a href="/register.php">skapa ett konto</a> för att ansöka om medlemskap.</p>

  <?php elseif ($membership === null): ?>

    <!-- Användaren är inloggad men har inte ansökt ännu -->
    <form method="POST" action="/apply_group.php">
      <input type="hidden" name="group_id" value="<?= $groupId ?>" />
      <button type="submit">Ansök om medlemskap</button>
    </form>

  <?php elseif ($membership['status'] === 'pending'): ?>

    <!-- Ansökan är inskickad men inte godkänd än -->
    <p>Din medlemsansökan har skickats och väntar på godkännande.</p>
  
  <?php elseif ($membership['status'] === 'denied'): ?>

    <!-- Vår nya status i group_members! -->
    <p style="color: red;">
      Din ansökan om medlemskap i denna grupp har avslagits.
    </p>

  <?php elseif ($membership['status'] === 'approved'): ?>

    <!-- Fullvärdig medlem. Visa roll, ansökningar (om man är admin!), medlemmar, och knapp till alla diskussioner -->
    <p style="color: green; font-weight: bold;">
      Du är medlem (Roll: <?= e($membership['role']) ?>)
    </p>

    <?php if($membership['role'] === 'admin'): ?>
      <h3>Ansökningar (<?= count($pendingApplications) ?>)</h3>

      <?php if(empty($pendingApplications)): ?>
        <p>Inga väntande ansökningar.</p>
      <?php else: ?>
          <ul>
            <?php foreach ($pendingApplications as $app): ?>
              <li>
                <strong><?= e($app['username']) ?></strong> 
                (Ansökte: <?= e($app['applied_at']) ?>)
                
                <!-- En form, en fil för att båda acceptera och neka! -->
                <form method="POST" action="/handle_application.php" style="display:inline;">
                  <input type="hidden" name="group_id" value="<?= $groupId ?>">
                  <input type="hidden" name="application_id" value="<?= (int)$app['application_id'] ?>">
                  
                  <!-- Det här är helt nytt för mig; `name=` på en button! Men det är härifrån vi
                       kommer kunna plocka ut `$action = $_POST['action'] i handle_application.php! -->
                  <button type="submit" name="action" value="accept">Acceptera</button>
                  <button type="submit" name="action" value="deny">Neka</button>
                </form>
              </li>
            <?php endforeach; ?>
          </ul>
    <?php endif; ?>
    
    <?php endif; ?>
  <?php endif; ?>
</body>
</html>