<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260901081211 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__trainer AS SELECT id, first_name, last_name, email, organization_id, is_archived, is_allow_send_mail, is_organization, is_public, comments, created_at FROM trainer');
        $this->addSql('DROP TABLE trainer');
        $this->addSql('CREATE TABLE trainer (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, organization_id VARCHAR(255) NOT NULL, is_archived BOOLEAN DEFAULT NULL, is_allow_send_email BOOLEAN DEFAULT NULL, is_organization BOOLEAN DEFAULT NULL, is_public BOOLEAN NOT NULL, comments CLOB DEFAULT NULL, created_at DATETIME NOT NULL)');
        $this->addSql('INSERT INTO trainer (id, first_name, last_name, email, organization_id, is_archived, is_allow_send_email, is_organization, is_public, comments, created_at) SELECT id, first_name, last_name, email, organization_id, is_archived, is_allow_send_mail, is_organization, is_public, comments, created_at FROM __temp__trainer');
        $this->addSql('DROP TABLE __temp__trainer');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__trainer AS SELECT id, first_name, last_name, email, organization_id, is_archived, is_allow_send_email, is_organization, is_public, comments, created_at FROM trainer');
        $this->addSql('DROP TABLE trainer');
        $this->addSql('CREATE TABLE trainer (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, organization_id VARCHAR(255) NOT NULL, is_archived BOOLEAN DEFAULT NULL, is_allow_send_mail BOOLEAN DEFAULT NULL, is_organization BOOLEAN DEFAULT NULL, is_public BOOLEAN NOT NULL, comments CLOB DEFAULT NULL, created_at DATETIME NOT NULL)');
        $this->addSql('INSERT INTO trainer (id, first_name, last_name, email, organization_id, is_archived, is_allow_send_mail, is_organization, is_public, comments, created_at) SELECT id, first_name, last_name, email, organization_id, is_archived, is_allow_send_email, is_organization, is_public, comments, created_at FROM __temp__trainer');
        $this->addSql('DROP TABLE __temp__trainer');
    }
}
