### Endpoint: Tworzenie pracownika

**URL:** POST /api/employee
**Content-Type:** application/json

Tworzy pracownika

#### 🔸 Przykładowe żądanie:
json
{
  "firstName": "Jan",
  "lastName": "Kowalski"
}

### Endpoint: Rejestracja czasu pracy

**URL:** POST /api/work-day  
**Content-Type:** application/json

Rejestruje przedział czasu pracy dla pracownika.

#### 🔸 Przykładowe żądanie:
json
{
  "employeeId": "0c8d2ea6-d75e-4f17-a9ef-739dcd8477b4",
  "shiftStartTime": "2025-04-14T08:00:00",
  "shiftEndTime": "2025-04-14T16:00:00"
}


### Endpoint: Podsumowanie czasu pracy dzień

**URL:** GET /api/summary/day
**Content-Type:** application/json

Podsumowuje czas pracy pracownika za dany dzień

#### 🔸 Przykładowe żądanie:
http://localhost:8000/api/summary/day?employeeId=01963e32-ac9c-7f3e-bcec-8df46b6d060d&date=01.04.2025


### Endpoint: Podsumowanie czasu pracy miesiąc

**URL:** GET /api/summary/month
**Content-Type:** application/json

Podsumowuje czas pracy pracownika za cały miesiąc

#### 🔸 Przykładowe żądanie:
http://localhost:8000/api/summary/month?employeeId=01963e32-ac9c-7f3e-bcec-8df46b6d060d&date=04.2025
