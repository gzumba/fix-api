<?php
declare(strict_types=1);

namespace App\Dto;

class Order
{
    public ?\DateTimeInterface $created = null;

    public function __construct(
        public ?int $id,
        public ?string $deviceType,
        public ?string $deviceManufacturer,
        public ?string $deviceBrand,
        public ?string $technician,
        public ?string $status,
        ?string $created,
        )
    {
        if ($created){
            $this->created = new \DateTimeImmutable($created);
        }
    }

   
    public static function createFromRow(array $row): self
    {
        return new self(...$row);
    }
}
