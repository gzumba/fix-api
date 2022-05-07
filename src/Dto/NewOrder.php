<?php
declare(strict_types=1);

namespace App\Dto;

class NewOrder
{
    public ?int $id = null;

    public function __construct(
        public string $deviceManufacturer,
        public string $deviceBrand,
        public string $deviceType,
        public string $issue,
    )
    {}
}
