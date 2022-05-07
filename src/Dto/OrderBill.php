<?php
declare(strict_types=1);

namespace App\Dto;

class OrderBill
{
    public ?\DateTimeInterface $created = null;

    public function __construct(
        public ?int $id,
        public ?int $orderId,
        public ?float $amount,
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
