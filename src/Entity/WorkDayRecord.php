<?php

namespace App\Entity;

use App\Repository\WorkDayRecordRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WorkDayRecordRepository::class)]
class WorkDayRecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private ?\DateTimeInterface $shiftStartTime = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private ?\DateTimeInterface $shiftEndTime = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: false)]
    private ?\DateTimeInterface $workingDayDate = null;

    #[ORM\ManyToOne(inversedBy: 'workDayRecords')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Employee $employee = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getShiftStartTime(): ?\DateTimeInterface
    {
        return $this->shiftStartTime;
    }

    public function setShiftStartTime(\DateTimeInterface $shiftStartTime): static
    {
        $this->shiftStartTime = $shiftStartTime;

        return $this;
    }

    public function getShiftEndTime(): ?\DateTimeInterface
    {
        return $this->shiftEndTime;
    }

    public function setShiftEndTime(\DateTimeInterface $shiftEndTime): static
    {
        $this->shiftEndTime = $shiftEndTime;

        return $this;
    }

    public function getWorkingDayDate(): ?\DateTimeInterface
    {
        return $this->workingDayDate;
    }

    public function setWorkingDayDate(\DateTimeInterface $workingDayDate): static
    {
        $this->workingDayDate = $workingDayDate;

        return $this;
    }

    public function getEmployee(): ?Employee
    {
        return $this->employee;
    }

    public function setEmployee(?Employee $employee): static
    {
        $this->employee = $employee;

        return $this;
    }
}
