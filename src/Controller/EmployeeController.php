<?php

namespace App\Controller;

use App\Entity\Employee;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class EmployeeController extends AbstractController
{
    #[Route('/api/employee', name: 'create_employee', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Walidacja
        if (
            !isset($data['firstName'], $data['lastName']) ||
            empty($data['firstName']) ||
            empty($data['lastName'])
        ) {
            return $this->json(['error' => 'Imię i nazwisko są wymagane.'], 400);
        }

        // Tworzenie nowego pracownika
        $employee = new Employee();
        $employee->setFirstName($data['firstName']);
        $employee->setLastName($data['lastName']);

        $em->persist($employee);
        $em->flush();

        return $this->json([
            'id' => $employee->getId(),
        ]);
    }
}
