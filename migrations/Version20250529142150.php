<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250529142150 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE comments (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', author BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', task BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', description LONGTEXT NOT NULL, created_date DATETIME DEFAULT NULL, updated_date DATETIME DEFAULT NULL, INDEX IDX_5F9E962ABDAFD8C8 (author), INDEX IDX_5F9E962A527EDB25 (task), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ext_log_entries (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(8) NOT NULL, logged_at DATETIME NOT NULL, object_id VARCHAR(64) DEFAULT NULL, object_class VARCHAR(191) NOT NULL, version INT NOT NULL, data LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', username VARCHAR(191) DEFAULT NULL, INDEX log_class_lookup_idx (object_class), INDEX log_date_lookup_idx (logged_at), INDEX log_user_lookup_idx (username), INDEX log_version_lookup_idx (object_id, object_class, version), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB ROW_FORMAT = DYNAMIC');
        $this->addSql('CREATE TABLE projects (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, archived TINYINT(1) DEFAULT 0 NOT NULL, created_date DATETIME DEFAULT NULL, updated_date DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tasks (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', project BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', worker BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', creator BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, priority VARCHAR(255) DEFAULT \'medium\' NOT NULL, status VARCHAR(255) DEFAULT \'pending\' NOT NULL, completion_date DATETIME DEFAULT NULL, created_date DATETIME DEFAULT NULL, updated_date DATETIME DEFAULT NULL, INDEX IDX_505865972FB3D0EE (project), INDEX IDX_505865979FB2BF62 (worker), INDEX IDX_50586597BC06EA63 (creator), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL, roles JSON NOT NULL, active TINYINT(1) NOT NULL, last_login_date DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962ABDAFD8C8 FOREIGN KEY (author) REFERENCES users (id)');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962A527EDB25 FOREIGN KEY (task) REFERENCES tasks (id)');
        $this->addSql('ALTER TABLE tasks ADD CONSTRAINT FK_505865972FB3D0EE FOREIGN KEY (project) REFERENCES projects (id)');
        $this->addSql('ALTER TABLE tasks ADD CONSTRAINT FK_505865979FB2BF62 FOREIGN KEY (worker) REFERENCES users (id)');
        $this->addSql('ALTER TABLE tasks ADD CONSTRAINT FK_50586597BC06EA63 FOREIGN KEY (creator) REFERENCES users (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comments DROP FOREIGN KEY FK_5F9E962ABDAFD8C8');
        $this->addSql('ALTER TABLE comments DROP FOREIGN KEY FK_5F9E962A527EDB25');
        $this->addSql('ALTER TABLE tasks DROP FOREIGN KEY FK_505865972FB3D0EE');
        $this->addSql('ALTER TABLE tasks DROP FOREIGN KEY FK_505865979FB2BF62');
        $this->addSql('ALTER TABLE tasks DROP FOREIGN KEY FK_50586597BC06EA63');
        $this->addSql('DROP TABLE comments');
        $this->addSql('DROP TABLE ext_log_entries');
        $this->addSql('DROP TABLE projects');
        $this->addSql('DROP TABLE tasks');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
