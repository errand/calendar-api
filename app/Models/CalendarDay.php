<?php

namespace App\Models;

class CalendarDay {
    public function __construct(
        public int $id,
        public string $date,
        public int $dayType,
        public ?string $comment
    ) {}
}