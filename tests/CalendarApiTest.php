<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class CalendarApiTest extends TestCase
{
    private $baseUrl = 'http://nginx:80/calendar';
    private $http;

    private function generateTestDayPayload(array $overrides = []): array
    {
        $randomDays = mt_rand(1, 365);
        $defaults = [
            'date' => date('Y-m-d', strtotime("+$randomDays days")),
            'dayType' => mt_rand(1, 9),
            'comment' => 'Тестовый день ' . uniqid()
        ];

        return array_merge($defaults, $overrides);
    }

    protected function setUp(): void
    {
        $this->http = curl_init();
        curl_setopt($this->http, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->http, CURLOPT_HEADER, false);
    }

    protected function tearDown(): void
    {
        curl_close($this->http);
    }

    public function testGetCalendarFor2024()
    {
        curl_setopt($this->http, CURLOPT_URL, $this->baseUrl . '/2024');
        $response = curl_exec($this->http);
        $status = curl_getinfo($this->http, CURLINFO_HTTP_CODE);

        $this->assertEquals(200, $status, 'Expected status 200 for GET /calendar/2024');
        $data = json_decode($response, true);
        $this->assertIsArray($data, 'Response should be an array');
        $this->assertNotEmpty($data, 'Calendar should not be empty');
        $this->assertArrayHasKey('id', $data[0], 'First item should have id');
        $this->assertEquals('Новогодние каникулы', $data[0]['comment'], 'First comment should be correct');
    }

    public function testPostNewDay()
    {
        $payload = $this->generateTestDayPayload([
            'dayType' => 4,
        ]);

        curl_setopt($this->http, CURLOPT_URL, $this->baseUrl);
        curl_setopt($this->http, CURLOPT_POST, true);
        curl_setopt($this->http, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($this->http, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);

        $response = curl_exec($this->http);
        $status = curl_getinfo($this->http, CURLINFO_HTTP_CODE);

        $this->assertEquals(201, $status, 'Expected status 201 for POST /calendar');
        $data = json_decode($response, true);

        $this->assertIsArray($data, 'Response should be an array');
        $this->assertArrayHasKey('id', $data, 'Response should include id');

        return $data['id'];
    }

    public function testGetDayById()
    {
        $id = $this->testPostNewDay();

        curl_setopt($this->http, CURLOPT_URL, $this->baseUrl . '/id/' . $id);
        curl_setopt($this->http, CURLOPT_CUSTOMREQUEST, 'GET');
        $response = curl_exec($this->http);
        $status = curl_getinfo($this->http, CURLINFO_HTTP_CODE);

        $this->assertEquals(200, $status);
        $data = json_decode($response, true);
        $this->assertEquals($id, $data['id']);
    }

    public function testGetDayByDate()
    {
        $date = '2024-01-01';
        curl_setopt($this->http, CURLOPT_URL, $this->baseUrl . '/date/' . $date);
        curl_setopt($this->http, CURLOPT_CUSTOMREQUEST, 'GET');
        $response = curl_exec($this->http);
        $status = curl_getinfo($this->http, CURLINFO_HTTP_CODE);

        $this->assertEquals(200, $status);
        $data = json_decode($response, true);
        $this->assertEquals($date, $data['date']);
    }

    /**
     * @depends testPostNewDay
     */
    public function testPutUpdateDay($id)
    {
        $payload = $this->generateTestDayPayload([
            'dayType' => 9,
        ]);

        curl_setopt($this->http, CURLOPT_URL, $this->baseUrl . '/' . $id);
        curl_setopt($this->http, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($this->http, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($this->http, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $response = curl_exec($this->http);
        $status = curl_getinfo($this->http, CURLINFO_HTTP_CODE);

        $this->assertEquals(200, $status, 'Expected status 200 for PUT /calendar/' . $id);
        $data = json_decode($response, true);
        $this->assertIsArray($data, 'Response should be an array');
        $this->assertTrue($data['success'], 'Success should be true');

        return $id;
    }

    /**
     * @depends testPutUpdateDay
     */
    public function testDeleteDay($id)
    {
        curl_setopt($this->http, CURLOPT_URL, $this->baseUrl . '/' . $id);
        curl_setopt($this->http, CURLOPT_CUSTOMREQUEST, 'DELETE');
        $response = curl_exec($this->http);
        $status = curl_getinfo($this->http, CURLINFO_HTTP_CODE);

        $this->assertEquals(200, $status, 'Expected status 200 for DELETE /calendar/' . $id);
        $data = json_decode($response, true);
        $this->assertIsArray($data, 'Response should be an array');
        $this->assertTrue($data['success'], 'Success should be true');
    }

    public function testPostInvalidData()
    {
        $payload = [
            'date' => 'invalid-date',
            'dayType' => 99
        ];
        curl_setopt($this->http, CURLOPT_URL, $this->baseUrl);
        curl_setopt($this->http, CURLOPT_POST, true);
        curl_setopt($this->http, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($this->http, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_exec($this->http);
        $status = curl_getinfo($this->http, CURLINFO_HTTP_CODE);

        $this->assertEquals(400, $status);
    }

    public function testGetNonExistentDay()
    {
        curl_setopt($this->http, CURLOPT_URL, $this->baseUrl . '/id/999999');
        curl_setopt($this->http, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_exec($this->http);
        $status = curl_getinfo($this->http, CURLINFO_HTTP_CODE);

        $this->assertEquals(404, $status);
    }
}