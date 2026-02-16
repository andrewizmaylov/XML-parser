<?php

declare(strict_types=1);

namespace XMLToDB\XmlParser\Service;

use PDO;
use XMLToDB\XmlParser\Connection\Contracts\RepositoryInterface;
use XMLToDB\XmlParser\Connection\Contracts\StorageInterface;
use XMLToDB\XmlParser\Connection\PareserRepository;
use XMLToDB\XmlParser\Connection\ParserStorage;
use XMLToDB\XmlParser\Parser\Contracts\ParserInterface;
use XMLToDB\XmlParser\Parser\XmlParser;
use XMLToDB\XmlParser\Result\ParseResult;

readonly class ReedContentToDB
{
    private StorageInterface $storage;
    private RepositoryInterface $repository;
    private ParserInterface $parser;

    public function __construct(
        public PDO $connection,
        ?StorageInterface $storage = null,
        ?RepositoryInterface $repository = null,
        ?ParserInterface $parser = null,
    )
    {
        $this->storage = $storage ?? new ParserStorage($this->connection);
        $this->repository = $repository ?? new PareserRepository($this->connection);
        $this->parser = $parser ?? new XmlParser();
    }

    public function reed(string $filePath, string $pattern, ?string $table = null): ParseResult
    {
        set_time_limit(0);

        $startTime = microtime(true);
        $recordsAdded = 0;

        $tableName = $table ?? StorageInterface::TABLE_NAME;

        try {
            $this->storage->checkTableExists($tableName);

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
