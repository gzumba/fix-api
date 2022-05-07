<?php
declare(strict_types=1);

namespace App\Dto;

class Note
{
    public ?\DateTimeInterface $created = null;

    public function __construct(
        public ?int $id,
        public string $type,
        public ?string $description,
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
