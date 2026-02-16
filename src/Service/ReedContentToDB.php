<?php

declare(strict_types=1);

namespace XMLToDB\XmlParser\Service;

use XMLToDB\XmlParser\Connection\Contracts\RepositoryInterface;
use XMLToDB\XmlParser\Connection\Contracts\StorageInterface;
use XMLToDB\XmlParser\Parser\Contracts\ParserInterface;

readonly class ReedContentToDB
{
    public function __construct(
        private StorageInterface $storage,
        private RepositoryInterface $repository,
        private ParserInterface $parser,
    )
    {
    }

    public function reed(string $filePath, string $pattern, string $tableName): void
    {
        $lastRecord = $this->repository->getLatestRecord($tableName);
        $lastReadPosition = $lastRecord?->endPosition ?? 0;
        echo $lastReadPosition . PHP_EOL;

        $generator = $this->parser->readData([
            'filePath' => $filePath,
            'pattern' => $pattern,
            'lastReadPosition' => $lastReadPosition,
        ]);

        foreach($generator as $bunch) {
            $this->storage->upsertMany($bunch, $tableName);
        }
    }
}
