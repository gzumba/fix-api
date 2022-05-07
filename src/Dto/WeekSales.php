<?php
declare(strict_types=1);

namespace App\Dto;

class WeekSales
{
    public function __construct(
        public string $week,
        public int $invoices,
        public float $amount
        )
    {
    }

}
