# RESTful API for Calendar

Simple demo API to demonstrate my demonstrate an approach to programming. 

## TechStack

- PHP 8.4
- MySQL/MariaDB
- Nginx
- Docker

## Installation

1. **Clone the repo**:
   ```bash
   git clone git@github.com:errand/calendar-api.git
   cd calendar-api
   ```
2. **Run Docker containers**
   ```bash
   docker compose -f docker/docker-compose.yml up -d --build
   ``` 

## Installation

### Run PHPUnit tests

```bash
docker exec calendar_api_php vendor/bin/phpunit tests/CalendarApiTest.php
```

### Postman collection

Import _Calendar-API.postman_collection.json_ to Postman for manual testing.

## Available endpoints

### Calendar
- **GET** `/calendar/{year}` - get year calendar
- **GET** `/calendar/date/{date}` - get day by date (YYYY-MM-DD format)
- **GET** `/calendar/id/{id}` - get date by ID

### Data manipulation

- **POST** `/calendar` - add date
  
  payload format
  ```
  {
    "date": "2024-12-31",
    "dayType": 3,
    "comment": "Новый год"
  }
  ```
- **PUT** `/calendar/{id}` - Update record
- **DELETE** `/calendar/{id}` - Delete record
