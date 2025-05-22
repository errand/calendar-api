<?php

namespace App\Services;

use App\Models\CalendarDay;
use App\Repositories\Interfaces\CalendarRepositoryInterface;

class CalendarService {
    private CalendarRepositoryInterface $repository;

    public function __construct(CalendarRepositoryInterface $repository) {
        $this->repository = $repository;
    }

    public function getCalendarByYear(int $year): array {
        return $this->repository->getByYear($year);
    }

    public function getById(int $id): ?CalendarDay
    {
        return $this->repository->getById($id);
    }

    public function getByDate(string $date): ?CalendarDay
    {
        return $this->repository->getByDate($date);
    }

    public function addSpecialDay(string $date, int $dayType, ?string $comment): int {
        if (!$this->validateDayType($dayType)) {
            throw new \InvalidArgumentException("Invalid day type");
        }
        if (!$this->validateDate($date)) {
            throw new \InvalidArgumentException("Invalid date format");
        }
        return $this->repository->add($date, $dayType, $comment);
    }

    public function updateSpecialDay(int $id, string $date, int $dayType, ?string $comment): bool {
        if (!$this->validateDayType($dayType)) {
            throw new \InvalidArgumentException("Invalid day type");
        }
        if (!$this->validateDate($date)) {
            throw new \InvalidArgumentException("Invalid date format");
        }
        return $this->repository->update($id, $date, $dayType, $comment);
    }

    public function deleteSpecialDay(int $id): bool {
        return $this->repository->delete($id);
    }

    private function validateDayType(int $dayType): bool {
        return in_array($dayType, [1, 2, 3, 4, 5, 6, 7, 8, 9]);
    }

    private function validateDate(string $date): bool {
        return strtotime($date) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date);
    }
}