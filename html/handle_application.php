<?php
  // Ännu en pure server action endpoint!
  require_once __DIR__ . '/includes/helpers.php';
  require_once __DIR__ . '/includes/db.php';
  require_auth();

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // User Id (admin i detta fall!) och Group Id på precis samma sätt som i apply_group.php
    $adminId = (int)$_SESSION['user_id'];
    $groupId = (int)($_POST['group_id'] ?? 0);
    if ($groupId <= 0) {
      header('Location: /groups.php');
      exit;
    }

    $applicationId = (int)($_POST['application_id'] ?? 0);
    // Från knappen som användaren tröck på! Antingen 'accept' eller 'deny'
    $action = $_POST['action'] ?? '';

    if ($applicationId <= 0 || !in_array($action, ['accept', 'deny'], true)) { // Den tredje parametern i `in_array` är `$strict` vilket gör att vi också kollar type!
      header("Location: /group.php?id={$groupId}");
      exit;
    }

    // Säkerställ att den inloggade admin är admin över DENNA grupp
    $adminMembership = get_group_membership($mysqli, $groupId, $adminId);
    if (!$adminMembership || $adminMembership['role'] !== 'admin') {
        header("Location: /group.php?id={$groupId}");
        exit;
    }

    // Conditional databasoperation
    if ($action === 'accept') {
        $statement = $mysqli->prepare("
            UPDATE group_members 
            SET status = 'approved', joined_at = NOW() 
            WHERE id = ? AND group_id = ? AND status = 'pending'
        ");
        $statement->bind_param("ii", $applicationId, $groupId);
        $statement->execute();
    } elseif ($action === 'deny') {
        $statement = $mysqli->prepare("
            UPDATE group_members 
            SET status = 'denied' 
            WHERE id = ? AND group_id = ? AND status = 'pending'
        ");
        $statement->bind_param("ii", $applicationId, $groupId);
        $statement->execute();
    }

    header("Location: /group.php?id={$groupId}");
    exit; 
  }