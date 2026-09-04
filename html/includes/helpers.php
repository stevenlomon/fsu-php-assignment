<?php
declare(strict_types=1); // Kan ses som JavaScript's 'strict mode' för php type checking!

// För att vi ska skippa skriva htmlspecialchars 100 gånger! (DRY) 
// htmlspecialchars är security best practice när dynamisk data och användardata skrivs 
// ut i HTML, skyddar mot XSS
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function is_logged_in(): bool {
   // Vi startar sessionen automatiskt om anropande fil inte redan gjort det!
  if (session_status() === PHP_SESSION_NONE) {
        session_start();
  }

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