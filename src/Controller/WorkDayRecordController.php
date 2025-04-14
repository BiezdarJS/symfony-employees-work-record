<?php

namespace App\Controller;

use App\Entity\WorkDayRecord;
use App\Entity\Employee;
use Doctrine\ORM\EntityManagerInterface;
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
        $start = \DateTime::createFromFormat('d.m.Y H:i', $data['shiftStartTime']);
        $end = \DateTime::createFromFormat('d.m.Y H:i', $data['shiftEndTime']);

        if (!$start || !$end) {
            return $this->json(['error' => 'Niepoprawny format daty. Użyj: dd.mm.yyyy HH:ii'], 400);
        }

        // Sprawdź maks. 12 godzin
        $interval = $start->diff($end);
        $workedHours = $interval->h + ($interval->i / 60);
        if ($workedHours > 12) {
            return $this->json(['error' => 'Nie można zarejestrować więcej niż 12 godzin.'], 400);
        }

        // Wyciągnij dzień rozpoczęcia
        $workDate = \DateTime::createFromFormat('Y-m-d', $start->format('Y-m-d'));

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
    public function summaryForDay(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['unikalny identyfikator pracownika'], $data['data'])) {
            return $this->json(['error' => 'Brak wymaganych danych.'], 400);
        }

        $employeeId = $data['unikalny identyfikator pracownika'];
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

        // Oblicz ilość godzin
        $start = $record->getShiftStartTime();
        $end = $record->getShiftEndTime();
        $diff = $start->diff($end);
        $hours = $diff->h + round($diff->i / 60, 1);

        $rate = 20; // PLN
        $total = $hours * $rate;

        return $this->json([
            'response' => [
                'suma po przeliczeniu' => "{$total} PLN",
                'ilość godzin z danego dnia' => $hours,
                'stawka' => "{$rate} PLN",
            ]
        ]);
    }
}
