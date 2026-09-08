<?php
declare(strict_types=1); // Kan ses som JavaScript's 'strict mode' för php type checking!

// Vi startar sessionen automatiskt om anropande fil inte redan gjort det!
// Tack vare att vi flyttar denna hit istället för att binda den till is_logged_in() kommer alla filer som importerar helpers.php 
// (alla eftersom vi vill använda `htmlspecialchars` i alla våra filer) ha en valid session
if (session_status() === PHP_SESSION_NONE) {
      session_start();
}

// För att vi ska skippa skriva htmlspecialchars 100 gånger! (DRY) 
// htmlspecialchars är security best practice när dynamisk data och användardata skrivs 
// ut i HTML, skyddar mot XSS
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function is_logged_in(): bool {
  return isset($_SESSION['user_id']); // "Är vi inloggad?" direkt översatt till php!
}

// Nu kan vår DRY helper function för auth kontroll använda is_logged_in()!
function require_auth(): void {
  // Session status hämtas nu via is_logged_in();

  if (!is_logged_in()) {
    $_SESSION['error_message'] = "Du måste vara inloggad för att se denna sida!";
    header('Location: /login.php');
    exit; // Denna är EXTRA viktig vid auth kontroll! Utan denna körs resten av sidan i bakgrunden!
  }
}

function get_group_membership(mysqli $mysqli, int $groupId, int $userId): ?array { // `?array` betyder nullable!
  $statement = $mysqli->prepare("
    SELECT status, role
    FROM group_members
    WHERE group_id = ? AND user_id = ?
    LIMIT 1
  ");
  $statement->bind_param("ii", $groupId, $userId);

  $statement->execute();
  $result = $statement->get_result();

  return $result->fetch_assoc() ?: null; // ?: kallas the Elvis operator, låter bissart men sant haha! `$a ?: $b` är shorthand för `$a ? $a : $b`
}

function get_group(mysqli $mysqli, int $groupId): ?array {
 $statement = $mysqli->prepare("
    SELECT name, description
    FROM groups
    WHERE id = ?
    LIMIT 1
  ");
  $statement->bind_param("i", $groupId);

  $statement->execute();
  $result = $statement->get_result();

  return $result->fetch_assoc() ?: null; 
}

function get_pending_group_applications(mysqli $mysqli, int $groupId): ?array {
  $statement = $mysqli->prepare("
    SELECT gm.id AS application_id, gm.user_id, gm.applied_at, u.username
    FROM group_members gm
    INNER JOIN users u ON gm.user_id = u.id
    WHERE gm.group_id = ? AND gm.status = 'pending'
    ORDER BY gm.applied_at ASC
  ");
  $statement->bind_param("i", $groupId);

  $statement->execute();
  $result = $statement->get_result();

  // Returnera *alla* rader. Om det inte finns några, blir det en tom array []
  return $result->fetch_all(MYSQLI_ASSOC);
}

function get_user_groups(mysqli $mysqli, int $userId): array {
    $statement = $mysqli->prepare("
        SELECT 
            g.id, 
            g.name, 
            g.description, 
            gm.role, 
            gm.joined_at
        FROM `groups` g
        INNER JOIN group_members gm ON g.id = gm.group_id
        WHERE gm.user_id = ? AND gm.status = 'approved'
        ORDER BY gm.joined_at DESC
    ");
    $statement->bind_param("i", $userId);
    $statement->execute();

    $result = $statement->get_result();
    return $result->fetch_all(MYSQLI_ASSOC); // Samma resonemang som get_pending_group_applications()
}

function get_discussions_for_group(mysqli $mysqli, int $groupId): array {
    $statement = $mysqli->prepare("
        SELECT 
            p.id, 
            p.title, 
            p.created_at, 
            u.username,
            COUNT(replies.id) AS reply_count
        FROM posts p
        INNER JOIN users u ON p.user_id = u.id
        LEFT JOIN posts replies ON replies.reply_to = p.id
        WHERE p.group_id = ? AND p.reply_to IS NULL
        GROUP BY p.id
        ORDER BY p.created_at DESC
    ");
    $statement->bind_param("i", $groupId);
    $statement->execute();

    $result = $statement->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}