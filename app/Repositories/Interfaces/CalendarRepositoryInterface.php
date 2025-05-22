<?php

namespace App\Repositories\Interfaces;


use App\Models\CalendarDay;

interface CalendarRepositoryInterface {
    public function getByYear(int $year): array;
    public function getById(int $id): ?CalendarDay;
    public function getByDate(string $date): ?CalendarDay;
    public function add(string $date, int $dayType, ?string $comment): int;
    public function update(int $id, string $date, int $dayType, ?string $comment): bool;
    public function delete(int $id): bool;
}
