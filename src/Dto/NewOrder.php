<?php
declare(strict_types=1);

namespace App\Dto;

class NewOrder
{
    private const ALLOWED_TYPES = ['Laptop', 'Phone', 'Tablet'];
    public ?int $id = null;

    public function __construct(
        public string $deviceManufacturer,
        public string $deviceBrand,
        public string $deviceType,
        public string $issue,
    )
    {
        if (!in_array($this->deviceType, self::ALLOWED_TYPES, true)) {
            throw new \InvalidArgumentException("Type {$this->deviceType} not supported");
        }
    }
}
