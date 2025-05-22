<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Database\Database;
use App\Repositories\CalendarRepository;
use App\Services\CalendarService;
use App\Controllers\CalendarController;

try {
    $db = new Database('db', 'user', 'password', 'calendar_db');
    $repository = new CalendarRepository($db);
    $service = new CalendarService($repository);
    $controller = new CalendarController($service);
    $controller->handleRequest();
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Internal server error']);
}