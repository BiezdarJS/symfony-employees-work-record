<?php

namespace App\Entity;

use App\Repository\EmployeeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
class Employee
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255)]
    private ?string $fullName = null;

    #[ORM\OneToMany(mappedBy: 'employee', targetEntity: WorkDayRecord::class, cascade: ['persist', 'remove'])]
    private Collection $workDayRecords;

    public function __construct()
    {
        $this->workDayRecords = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getWorkDayRecords(): Collection
    {
        return $this->workDayRecords;
    }

    public function addWorkDayRecord(WorkDayRecord $workDayRecord): static
    {
        if (!$this->workDayRecords->contains($workDayRecord)) {
            $this->workDayRecords->add($workDayRecord);
            $workDayRecord->setEmployee($this);
        }

        return $this;
    }

    public function removeWorkDayRecord(WorkDayRecord $workDayRecord): static
    {
        if ($this->workDayRecords->removeElement($workDayRecord)) {
            if ($workDayRecord->getEmployee() === $this) {
                $workDayRecord->setEmployee(null);
            }
        }

        return $this;
    }
}
