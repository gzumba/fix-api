<?php
declare(strict_types=1);

namespace App\Dto;

class OrderDetails
{
    public ?\DateTimeInterface $created = null;
    /** @var Note[] */
    public array $notes = [];

    public function __construct(
        public ?int $id,
        public ?string $deviceType,
        public ?string $deviceManufacturer,
        public ?string $deviceBrand,
        public ?string $technician,
        public array $status,
        string $created,
        array $notes,
        public array $invoices,
        )
    {
        if ($created){
            $this->created = new \DateTimeImmutable($created);
        }

        foreach ($notes as $note) {
            $this->notes[] = new Note(...$note);
        }
    }

   
    public static function createFromRow(array $row): self
    {
        return new self(...$row);
    }
}
