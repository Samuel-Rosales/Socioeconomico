<?php
require_once 'vendor/autoload.php';
$seeder = new \App\Seeds\MainSeeder();
$seeder->run();