<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Service\Database;

try {
    $db = new Database();
    $connection = $db->getConnection();
    echo "Uspesna konekcija!";
} catch (\Exception $e) {
    echo "Greska: " . $e->getMessage();
}
