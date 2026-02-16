<?php

declare(strict_types=1);

namespace AndreyIzmaylov\XmlParser\Database\Migration;

use PDO;

class ContentTableMigration
{
    public function __construct(
        public readonly PDO $pdo,
        public readonly string $tableName,
    )
    {
    }

    public function up(): void
    {
        $sql = <<<'STATMENT'
        "CREATE TABLE IF NOT EXISTS {$this->tableName} (
            `id` INT NOT NULL AUTO_INCREMENT,
            `content` LONGTEXT NOT NULL COLLATE 'utf8mb4_unicode_ci',
            `startPosition` INT UNSIGNED NOT NULL,
            `endPosition` INT UNSIGNED NOT NULL,
            `status` ENUM('PROCESSED','PENDING','FAIL') NULL DEFAULT 'PENDING' COLLATE 'utf8mb4_unicode_ci',
            `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP(),
            `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
        STATMENT;

        $this->execute($sql);
    }

    public function down(): void
    {
        $sql = <<<'STATMENT'
            "DROP TABLE IF EXISTS {$this->tableName};"
        STATMENT;

        $this->execute($sql);
    }

    protected function execute(string $sql, array $params = []): void
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }
}
