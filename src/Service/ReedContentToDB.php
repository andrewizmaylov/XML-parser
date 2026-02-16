<?php

declare(strict_types=1);

namespace XMLToDB\XmlParser\Service;

use XMLToDB\XmlParser\Connection\Contracts\RepositoryInterface;
use XMLToDB\XmlParser\Connection\Contracts\StorageInterface;
use XMLToDB\XmlParser\Parser\Contracts\ParserInterface;
use XMLToDB\XmlParser\Result\ParseResult;

readonly class ReedContentToDB
{
    public function __construct(
        private StorageInterface $storage,
        private RepositoryInterface $repository,
        private ParserInterface $parser,
    )
    {
    }

    public function reed(string $filePath, string $pattern, string $tableName): ParseResult
    {
        set_time_limit(0);

        $startTime = microtime(true);
        $recordsAdded = 0;

        try {
            if (!$this->storage->tableExists($tableName)) {
                return ParseResult::error(404, "Table '{$tableName}' does not exist", 0, 0);
            }

            $sourceFile = pathinfo($filePath, PATHINFO_BASENAME);

            $lastRecord = $this->repository->getLatestRecord($tableName, $sourceFile);
            $lastReadPosition = $lastRecord?->endPosition ?? 0;

            $generator = $this->parser->readData([
                'filePath' => $filePath,
                'pattern' => $pattern,
                'lastReadPosition' => $lastReadPosition,
            ]);

            foreach ($generator as $bunch) {
                $this->storage->upsertMany($bunch, $tableName, $sourceFile);
                $recordsAdded += count($bunch);
            }

            $elapsed = microtime(true) - $startTime;
            return ParseResult::success($recordsAdded, $elapsed);
        } catch (\Throwable $e) {
            $elapsed = microtime(true) - $startTime;
            return ParseResult::error(500, $e->getMessage(), $elapsed, $recordsAdded);
        }
    }
}
