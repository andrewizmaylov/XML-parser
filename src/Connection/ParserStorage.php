<?php

declare(strict_types=1);

namespace XMLToDB\XmlParser\Connection;

use XMLToDB\XmlParser\Connection\Contracts\StorageInterface;
use PDO;
use PDOException;
use XMLToDB\XmlParser\Database\Migration\ContentTableMigration;

class ParserStorage implements StorageInterface
{
    public function __construct(
        protected ?PDO $connection
    )
    {
    }

    public function checkTableExists(?string $tableName = null): void
    {
        $stmt = $this->connection->prepare(
            "SELECT COUNT(*) FROM information_schema.tables 
             WHERE table_schema = DATABASE() AND table_name = ?"
        );
        $stmt->execute([$tableName]);
        $result = (int) $stmt->fetchColumn() > 0;

        if (!$result) {
            (new ContentTableMigration($this->connection, $tableName?? StorageInterface::TABLE_NAME))->up();
        }
    }

    public function upsertMany(array $data, string $tableName, string $source): void
    {
        if (empty($data)) {
            return;
        }

        try {
            // Data prepare
            $values = [];
            $rowCount = count($data);
            $colCount = 4; // content, startPosition, endPosition, source

            // Create Placeholders: (?, ?, ?, ?), (?, ?, ?, ?), ...
            $rowPlaceholder = '(' . implode(', ', array_fill(0, $colCount, '?')) . ')';
            $placeholders = array_fill(0, $rowCount, $rowPlaceholder);

            // Prepare insertion array
            foreach ($data as $row) {
                $values[] = $row['content'];
                $values[] = $row['startPosition'];
                $values[] = $row['endPosition'];
                $values[] = $source;
            }

            $sql = sprintf(
                "INSERT INTO TABLE_NAME (content, startPosition, endPosition, source) VALUES %s",
                implode(', ', $placeholders)
            );
            $sql = str_replace('TABLE_NAME', $tableName, $sql);

            $stmt = $this->connection->prepare($sql);
            $stmt->execute($values);

        } catch (PDOException $e) {
            error_log("Batch UPSERT Error: " . $e->getMessage());
            throw $e;
        }
    }
}
