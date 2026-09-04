<?php
declare(strict_types=1); // Kan ses som JavaScript's 'strict mode' för php type checking!

// För att vi ska skippa skriva htmlspecialchars 100 gånger! (DRY) 
// htmlspecialchars är security best practice när dynamisk data och användardata skrivs 
// ut i HTML, skyddar mot XSS
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
}