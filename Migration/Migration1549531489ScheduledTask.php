<?php declare(strict_types=1);

namespace Shopware\Core\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1549531489ScheduledTask extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1549531489;
    }

    public function update(Connection $connection): void
    {
        $connection->executeQuery('
            CREATE TABLE `scheduled_task` (
              `id` BINARY(16) NOT NULL,
              `name` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `scheduled_task_class` VARCHAR(512) COLLATE utf8mb4_unicode_ci NOT NULL,
              `run_interval` INT(11) NOT NULL,
              `status` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `last_execution_time` DATETIME(3),
              `next_execution_time` DATETIME(3) NOT NULL,
              PRIMARY KEY (`id`),
              CONSTRAINT `check.run_interval` CHECK (run_interval >= 1),
              CONSTRAINT `uniq.scheduled_task.scheduled_task_class` UNIQUE (scheduled_task_class)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
