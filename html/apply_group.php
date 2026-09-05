<?php
  // Vi kan se denna som en pure server action endpoint!! Och i med att denna inte kommer rendrera någon HTML..
  require_once __DIR__ . '/includes/helpers.php';
  require_once __DIR__ . '/includes/db.php';

  //..kan vi använda require_auth() här!
  require_auth();

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vi från group Id från vår server action request
    $groupId = (int)($_POST['group_id'] ?? 0);

    if ($groupId <= 0) {
      // Detta betyder att grupp ID:t är invalid. Redirect till groups.php
      header('Location: /groups.php');
      exit;
    }

    // Och userId.. får vi från vår session!! Vi har validerat mha require_auth() att vi har en valid session!
    $userId = (int)$_SESSION['user_id']; // Så här behöver vi inte `?? 0`!

    // Databas interaktionen: lägg in en ny rad i group_members
    // Vi behöver bara skicka våra två ID:n, databasen tar hand om resten med default värden!
    $statement = $mysqli->prepare("
      INSERT INTO group_members (user_id, group_id)
      VALUES (?, ?)
    ");

    $statement->bind_param("ii", $userId, $groupId);

    if($statement->execute()) {
      /* header('Location: /group.php?id=<?= $groupId ?>'); */
      // Vi måste använda string concatenation eller gamla hederliga måsvingar! Och värt
      // att notera; för att använda måsvingar med variabler måste vi ha dubbelfnuttar!!
      // Funkar inte med enkelfnuttar!
      header("Location: /group.php?id={$groupId}"); 
      exit;
    } else {
      echo "Database Error: " . e($statement->error);
    }
    // That should be it!
  }