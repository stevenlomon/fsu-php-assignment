<?php
// logout.php är ett praktexempel på att en php fil behöver inte rendrera HTML för att vara en public route!
declare(strict_types=1); // Ja, vi har inga egna funkioner här, men denna påverkar built-in php funktioner också! Plus det är bra att behålla för codebase consistency

// Töm $_SESSION superglobalen i minnet, förstör sessionen och redirect till startsidan som gäst!
session_start();
$_SESSION = [];
session_destroy();

header('Location: /index.php');
exit;