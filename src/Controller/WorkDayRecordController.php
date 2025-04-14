<?php

namespace App\Controller;

use App\Entity\WorkDayRecord;
use App\Entity\Employee;
use App\Service\SalaryCalculator;
use App\Repository\WorkDayRecordRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;



final class WorkDayRecordController extends AbstractController
{
    #[Route('/api/work-day', name: 'create_work_day_record', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Walidacja danych wejściowych
        if (
            !isset($data['employeeId'], $data['shiftStartTime'], $data['shiftEndTime']) ||
            empty($data['employeeId']) || empty($data['shiftStartTime']) || empty($data['shiftEndTime'])
        ) {
            return $this->json(['error' => 'Wszystkie pola są wymagane.'], 400);
        }

        $employee = $em->getRepository(Employee::class)->find($data['employeeId']);

        if (!$employee) {
            return $this->json(['error' => 'Pracownik nie istnieje.'], 404);
        }

        // Parsuj daty
        $start = new \DateTime($data['shiftStartTime']);
        $end = new \DateTime($data['shiftEndTime']);

        if (!$start || !$end) {
            return $this->json(['error' => 'Niepoprawny format daty. Użyj: dd.mm.yyyy HH:ii'], 400);
        }

        // Czas zakończenia nie może być mniejszy niż szac rozpoczęcia - walidacja
        if ($end <= $start) {
            return $this->json(['error' => 'Czas zakończenia musi być późniejszy niż rozpoczęcia.'], 400);
        }

        // Sprawdź maks. 12 godzin
        $interval = $start->diff($end);
        $workedHours = $interval->h + ($interval->i / 60);
        if ($workedHours > 12) {
            return $this->json(['error' => 'Nie można zarejestrować więcej niż 12 godzin.'], 400);
        }

        // Wyciągnij dzień rozpoczęcia
        $workDate = (clone $start)->setTime(0, 0);

        // Czy już istnieje rekord tego dnia?
        $existing = $em->getRepository(WorkDayRecord::class)->findOneBy([
            'employee' => $employee,
            'workingDayDate' => $workDate,
        ]);

        if ($existing) {
            return $this->json(['error' => 'Pracownik ma już zarejestrowany czas dla tego dnia.'], 400);
        }

        // Utwórz i zapisz rekord
        $record = new WorkDayRecord();
        $record->setEmployee($employee);
        $record->setShiftStartTime($start);
        $record->setShiftEndTime($end);
        $record->setWorkingDayDate($workDate);

        $em->persist($record);
        $em->flush();

        return $this->json([
            'response' => [
                'Czas pracy został dodany!'
            ]
        ]);
    }


    #[Route('/api/summary/day', name: 'work_summary_day', methods: ['POST'])]
    public function summaryForDay(
        Request $request, 
        EntityManagerInterface $em,
        SalaryCalculator $calculator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['unikalny identyfikator pracownika'], $data['data'])) {
            return $this->json(['error' => 'Brak wymaganych danych.'], 400);
        }

        $employeeId = Uuid::fromString($data['unikalny identyfikator pracownika']);
        $dateInput = \DateTime::createFromFormat('d.m.Y', $data['data']);

        if (!$dateInput) {
            return $this->json(['error' => 'Nieprawidłowy format daty. Użyj DD.MM.RRRR'], 400);
        }

        // Znajdź pracownika
        $employee = $em->getRepository(Employee::class)->find($employeeId);
        if (!$employee) {
            return $this->json(['error' => 'Nie znaleziono pracownika.'], 404);
        }

        // Szukamy rekordu pracy dla tego dnia
        $record = $em->getRepository(WorkDayRecord::class)->findOneBy([
            'employee' => $employee,
            'workingDayDate' => $dateInput,
        ]);

        if (!$record) {
            return $this->json(['error' => 'Brak zarejestrowanej pracy w tym dniu.'], 404);
        }

        $start = $record->getShiftStartTime();
        $end = $record->getShiftEndTime();

        $diff = $start->diff($end);
        $minutes = $diff->h * 60 + $diff->i;
        $rounded = round($minutes / 30) * 30;
        $workedHours = $rounded / 60;

        $baseRate = $calculator->getBaseRate();
        $total = $calculator->calculateSalary($workedHours);

        return $this->json([
            'response' => [
                'suma po przeliczeniu' => "{$total} PLN",
                'ilość godzin z danego dnia' => $workedHours,
                'stawka' => "{$baseRate} PLN",
            ]
        ]);
    }

    

    #[Route('/api/summary/month', name: 'work_summary_month', methods: ['POST'])]
    public function summaryForMonth(
        Request $request, 
        EntityManagerInterface $em,
        SalaryCalculator $calculator,
        WorkDayRecordRepository $workDayRecordRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        

        if (!isset($data['unikalny identyfikator pracownika'], $data['data'])) {
            return $this->json(['error' => 'Brak wymaganych danych.'], 400);
        }

        $employeeId = Uuid::fromString($data['unikalny identyfikator pracownika']);
        $monthInput = \DateTime::createFromFormat('m.Y', $data['data']);

        if (!$monthInput) {
            return $this->json(['error' => 'Nieprawidłowy format daty. Użyj MM.RRRR'], 400);
        }

        // Znajdź pracownika
        $employee = $em->getRepository(\App\Entity\Employee::class)->find($employeeId);
        
        if (!$employee) {
            return $this->json(['error' => 'Nie znaleziono pracownika.'], 404);
        }

        

        // Oblicz początek i koniec miesiąca
        $timezone = new \DateTimeZone('Europe/Warsaw');
        $startDate = \DateTime::createFromFormat('Y-m-d', $monthInput->format('Y-m-01'), $timezone);
        $startDate->setTime(0, 0, 0);
        $endDate = clone $startDate;
        $endDate->modify('last day of this month')->setTime(23, 59, 59);


        // Pobierz wszystkie rekordy czasu pracy z danego miesiąca
        $records = $workDayRecordRepository->findByEmployeeIdAndDateRange(
            $employeeId,
            $startDate,
            $endDate
        );            

        $totalMinutes = 0;


        foreach ($records as $record) {
            $start = $record->getShiftStartTime();
            $end = $record->getShiftEndTime();

            $diff = $start->diff($end);
            $minutes = $diff->h * 60 + $diff->i;
            

            // Zaokrąglenie do 30 minut
            $rounded = round($minutes / 30) * 30;
            $totalMinutes += $rounded;
        }

        

        // Przelicz na godziny
        $totalHours = $totalMinutes / 60;

        $monthlyNorm = $calculator->getMonthlyNorm();
        $baseRate = $calculator->getBaseRate();
        $overtimeMultiplier = $calculator->getOvertimeMultiplier();

        // Przelicz na godziny        
        $normalHours = min($totalHours, $monthlyNorm);
        $overtimeHours = max($totalHours - $monthlyNorm, 0);
        $totalPay = $calculator->calculateSalary($totalHours);


        return $this->json([
            'response' => [
                'ilość normalnych godzin z danego miesiąca' => $normalHours,
                'stawka' => "{$baseRate} PLN",
                'ilość nadgodzin z danego miesiąca' => $overtimeHours,
                'stawka nadgodzinowa' => $baseRate * $overtimeMultiplier,
                'suma po przeliczeniu' => "{$totalPay} PLN"
            ]
        ]);
    }
}