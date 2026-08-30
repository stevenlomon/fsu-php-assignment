<?php
  $dbHost = $_ENV['DB_HOST'] ?? 'db';
  $dbUser = $_ENV['DB_USER'] ?? 'root';
  $dbPass = $_ENV['DB_PASSWORD'] ?? 'very-stronk';
  $dbName = "forum_db";

  $mysqli = new mysqli($dbHost, $dbUser, $dbPass);

  if ($mysqli->connect_error) {
    // Om jag förstår rätt är `die` ett alias till `exit` och de funkar identiskt men `die` är vanligt till error
    // som är så fatal att applikationen inte kan köras, och ett prakt exempel är att vi inte kan koppla till vår db
    die("Database connection failed: " . $mysqli->connect_error);
  }

  // Dessa två rader ser till att vår databas finns och väljer den
  $mysqli->query("CREATE DATABASE IF NOT EXISTS `$dbName`");
  $mysqli->select_db($dbName);

  // Safety best practice om jag förstår rätt
  $mysqli->set_charset("utf8mb4");

  // Att göra: Nån typ av 404 eller liknande om man besöker /db.php vilket just nu bara ger en blank sida