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
}
