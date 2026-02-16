<?php

declare(strict_types=1);

namespace XMLToDB\XmlParser\Entities;

readonly class ParsedEntity
{
    public function __construct(
        public string $content,
        public string $source,
        public int $startPosition,
        public int $endPosition,
        public string $status,
        public ?int $id,
        public ?string $created_at,
        public ?string $updated_at,
    )
    {
    }
}
