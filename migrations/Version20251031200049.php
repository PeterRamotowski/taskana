<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251031200049 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE time_entries (id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)', task_id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)', user_id BINARY(16) NOT NULL COMMENT '(DC2Type:uuid)', start_time DATETIME NOT NULL, end_time DATETIME DEFAULT NULL, duration INT DEFAULT 0 NOT NULL, description LONGTEXT DEFAULT NULL, created_date DATETIME DEFAULT NULL, updated_date DATETIME DEFAULT NULL, INDEX IDX_797F12A38DB60186 (task_id), INDEX IDX_797F12A3A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE time_entries ADD CONSTRAINT FK_797F12A38DB60186 FOREIGN KEY (task_id) REFERENCES tasks (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE time_entries ADD CONSTRAINT FK_797F12A3A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tasks ADD parent_task_id BINARY(16) DEFAULT NULL COMMENT '(DC2Type:uuid)', ADD estimated_hours DOUBLE PRECISION DEFAULT NULL, ADD due_date DATETIME DEFAULT NULL, ADD is_recurring TINYINT(1) DEFAULT 0 NOT NULL, ADD recurrence_pattern VARCHAR(255) DEFAULT NULL, ADD recurrence_interval INT DEFAULT 1, ADD recurrence_end_date DATETIME DEFAULT NULL, ADD last_recurrence_generation DATETIME DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tasks ADD CONSTRAINT FK_50586597FFFE75C0 FOREIGN KEY (parent_task_id) REFERENCES tasks (id) ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_50586597FFFE75C0 ON tasks (parent_task_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE time_entries DROP FOREIGN KEY FK_797F12A38DB60186
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE time_entries DROP FOREIGN KEY FK_797F12A3A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE time_entries
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tasks DROP FOREIGN KEY FK_50586597FFFE75C0
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_50586597FFFE75C0 ON tasks
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE tasks DROP parent_task_id, DROP estimated_hours, DROP due_date, DROP is_recurring, DROP recurrence_pattern, DROP recurrence_interval, DROP recurrence_end_date, DROP last_recurrence_generation
        SQL);
    }
}
