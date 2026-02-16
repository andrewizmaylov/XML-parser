<?php

declare(strict_types=1);

namespace XMLToDB\XmlParser\Connection;

use XMLToDB\XmlParser\Connection\Contracts\StorageInterface;
use PDO;
use PDOException;

class ParserStorage implements StorageInterface
{
    public function __construct(
        protected ?PDO $connection
    )
    {
    }

    public function upsertMany(array $data, string $tableName): void
    {
        if (empty($data)) {
            return;
        }

        try {
            // Data prepare
            $values = [];
            $rowCount = count($data);
            $colCount = 3; // content, startPosition, endPosition

            // Create Placeholders: (?, ?, ?), (?, ?, ?), ...
            $rowPlaceholder = '(' . implode(', ', array_fill(0, $colCount, '?')) . ')';
            $placeholders = array_fill(0, $rowCount, $rowPlaceholder);

            // Prepare insertion array
            foreach ($data as $row) {
                $values[] = $row['content'];
                $values[] = $row['startPosition'];
                $values[] = $row['endPosition'];
            }

            $sql = sprintf(
                <<< 'STATMENT'
                "INSERT INTO {$tableName} (content, startPosition, endPosition) VALUES %s"
                STATMENT,
                implode(', ', $placeholders)
            );

            $stmt = $this->connection->prepare($sql);
            $stmt->execute($values);

        } catch (PDOException $e) {
            error_log("Batch UPSERT Error: " . $e->getMessage());
            throw $e;
        }
    }
}
