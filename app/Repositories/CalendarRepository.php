<?php

namespace App\Repositories;

use App\Database\Database;
use App\Models\CalendarDay;
use App\Repositories\Interfaces\CalendarRepositoryInterface;

class CalendarRepository implements CalendarRepositoryInterface {
    private Database $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function getByYear(int $year): array {
        $stmt = $this->db->getConnection()->prepare(
            "SELECT * FROM calendar WHERE YEAR(date) = ? ORDER BY date"
        );
        $stmt->bind_param("i", $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $days = [];
        while ($row = $result->fetch_assoc()) {
            $days[] = new CalendarDay($row['id'], $row['date'], $row['day'], $row['comment']);
        }
        $stmt->close();
        return $days;
    }

    public function getById(int $id): ?CalendarDay {
        $stmt = $this->db->getConnection()->prepare(
            "SELECT * FROM calendar WHERE id = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ? new CalendarDay($row['id'], $row['date'], $row['day'], $row['comment']) : null;
    }

    public function getByDate(string $date): ?CalendarDay
    {
        $stmt = $this->db->getConnection()->prepare(
            "SELECT * FROM calendar WHERE date = ?"
        );
        $stmt->bind_param("s", $date);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        if ($row) {
            return new CalendarDay($row['id'], $row['date'], $row['day'], $row['comment']);
        }
        return null;
    }

    public function add(string $date, int $dayType, ?string $comment): int {
        $stmt = $this->db->getConnection()->prepare(
            "INSERT INTO calendar (date, day, comment) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("sis", $date, $dayType, $comment);
        $stmt->execute();
        $id = $this->db->getConnection()->insert_id;
        $stmt->close();
        return $id;
    }

    public function update(int $id, string $date, int $dayType, ?string $comment): bool {
        $stmt = $this->db->getConnection()->prepare(
            "UPDATE calendar SET date = ?, day = ?, comment = ? WHERE id = ?"
        );
        $stmt->bind_param("sisi", $date, $dayType, $comment, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function delete(int $id): bool {
        $stmt = $this->db->getConnection()->prepare(
            "DELETE FROM calendar WHERE id = ?"
        );
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}