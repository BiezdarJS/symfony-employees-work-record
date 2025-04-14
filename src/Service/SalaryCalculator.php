<?php

namespace App\Service;

class SalaryCalculator
{
    public function __construct(
        private int $monthlyNorm,
        private float $baseRate,
        private float $overtimeMultiplier
    ) {}

    public function calculateSalary(float $workedHours): float
    {
        if ($workedHours <= $this->monthlyNorm) {
            return $workedHours * $this->baseRate;
        }

        $overtime = $workedHours - $this->monthlyNorm;
        return ($this->monthlyNorm * $this->baseRate) + ($overtime * $this->baseRate * $this->overtimeMultiplier);
    }
}