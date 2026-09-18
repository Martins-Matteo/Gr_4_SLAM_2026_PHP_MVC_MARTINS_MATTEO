<?php
try {
    $bdd = new PDO(
        'mysql:host=localhost;dbname=gsbV2;charset=utf8',
        'visiteur',
        'visiteur123'
    );
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}