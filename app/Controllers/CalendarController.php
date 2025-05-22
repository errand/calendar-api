<?php

namespace App\Controllers;

use App\Services\CalendarService;

class CalendarController
{
    private CalendarService $service;

    public function __construct(CalendarService $service)
    {
        $this->service = $service;
    }

    public function handleRequest(): void
    {
        try {
            $method = $_SERVER['REQUEST_METHOD'];
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $pathParts = explode('/', trim($path, '/'));

            // GET /calendar/{year}
            if ($method === 'GET' && count($pathParts) === 2 && $pathParts[0] === 'calendar') {
                $this->getCalendar($pathParts[1]);
            // GET /calendar/id/{id}
            } elseif ($method === 'GET' && count($pathParts) === 3 && $pathParts[0] === 'calendar' && $pathParts[1] === 'id') {
                $this->getDayById($pathParts[2]);
            // GET /calendar/date/{date}
            } elseif ($method === 'GET' && count($pathParts) === 3 && $pathParts[0] === 'calendar' && $pathParts[1] === 'date') {
                $this->getDayByDate($pathParts[2]);
            // POST /calendar
            } elseif ($method === 'POST' && $pathParts[0] === 'calendar') {
                $this->addSpecialDay();
            // PUT /calendar/{id}
            } elseif ($method === 'PUT' && $pathParts[0] === 'calendar' && isset($pathParts[1])) {
                $this->updateSpecialDay($pathParts[1]);
            // DELETE /calendar/{id}
            } elseif ($method === 'DELETE' && $pathParts[0] === 'calendar' && isset($pathParts[1])) {
                $this->deleteSpecialDay($pathParts[1]);
            } else {
                $this->sendResponse(404, ['error' => 'Not found']);
            }
        } catch (\Exception $e) {
            $this->sendResponse(400, ['error' => $e->getMessage()]);
        }
    }

    private function getCalendar(string $year): void
    {
        if (!is_numeric($year)) {
            throw new \InvalidArgumentException("Year must be numeric");
        }
        $calendar = $this->service->getCalendarByYear((int)$year);
        $this->sendResponse(200, array_map(function ($day) {
            return [
                'id' => $day->id,
                'date' => $day->date,
                'dayType' => $day->dayType,
                'comment' => $day->comment
            ];
        }, $calendar));
    }

    private function getDayById(string $id): void
    {
        if (!is_numeric($id) || (int)$id <= 0) {
            throw new \InvalidArgumentException('ID must be a positive number');
        }
        $day = $this->service->getById((int)$id);
        if (!$day) {
            $this->sendResponse(404, ['error' => 'Day not found']);
        }
        $this->sendResponse(200, [
            'id' => $day->id,
            'date' => $day->date,
            'dayType' => $day->dayType,
            'comment' => $day->comment
        ]);
    }

    private function getDayByDate(string $date): void
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !strtotime($date)) {
            throw new \InvalidArgumentException('Invalid date format. Use YYYY-MM-DD');
        }
        $day = $this->service->getByDate($date);
        if (!$day) {
            $this->sendResponse(404, ['error' => 'Day not found']);
        }
        $this->sendResponse(200, [
            'id' => $day->id,
            'date' => $day->date,
            'dayType' => $day->dayType,
            'comment' => $day->comment
        ]);
    }

    private function addSpecialDay(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['date'], $data['dayType'])) {
            throw new \InvalidArgumentException("Required fields: date, dayType");
        }
        $id = $this->service->addSpecialDay(
            $data['date'],
            $data['dayType'],
            $data['comment'] ?? null
        );
        $this->sendResponse(201, ['id' => $id]);
    }

    private function updateSpecialDay(string $id): void
    {
        if (!is_numeric($id)) {
            throw new \InvalidArgumentException("ID must be numeric");
        }
        $day = $this->service->getById($id);
        if (!$day) {
            $this->sendResponse(404, ['error' => 'Day not found']);
        }
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['date'], $data['dayType'])) {
            throw new \InvalidArgumentException("Required fields: date, dayType");
        }
        $success = $this->service->updateSpecialDay(
            (int)$id,
            $data['date'],
            $data['dayType'],
            $data['comment'] ?? null
        );
        $this->sendResponse($success ? 200 : 404, ['success' => $success]);
    }

    private function deleteSpecialDay(string $id): void
    {
        if (!is_numeric($id)) {
            throw new \InvalidArgumentException("ID must be numeric");
        }
        $day = $this->service->getById($id);
        if (!$day) {
            $this->sendResponse(404, ['error' => 'Day not found']);
        }
        $success = $this->service->deleteSpecialDay((int)$id);
        $this->sendResponse($success ? 200 : 404, ['success' => $success]);
    }

    private function sendResponse(int $code, array $data): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}