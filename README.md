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

**URL:** POST /api/summary/day
**Content-Type:** application/json

Podsumowuje czas pracy pracownika za dany dzień

#### 🔸 Przykładowe żądanie:
json
{
  "unikalny identyfikator pracownika": "123e4567-e89b-12d3-a456-426614174000",
  "data": "01.01.1970"
}


### Endpoint: Podsumowanie czasu pracy miesiąc

**URL:** POST /api/summary/month
**Content-Type:** application/json

Podsumowuje czas pracy pracownika za cały miesiąc

#### 🔸 Przykładowe żądanie:
json
{
  "unikalny identyfikator pracownika": "123e4567-e89b-12d3-a456-426614174000",
  "data": "01.1970"
}
