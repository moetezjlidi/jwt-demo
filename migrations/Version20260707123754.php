<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260707123754 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE api_refresh_token (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, token_hash VARCHAR(255) NOT NULL, expires_at DATETIME NOT NULL, revoked BOOLEAN NOT NULL, used_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, api_user_id INTEGER NOT NULL, CONSTRAINT FK_6F2294194A50A7F2 FOREIGN KEY (api_user_id) REFERENCES api_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_6F2294194A50A7F2 ON api_refresh_token (api_user_id)');
        $this->addSql('CREATE TABLE api_user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(255) NOT NULL, password_hash VARCHAR(255) NOT NULL, roles CLOB NOT NULL, status VARCHAR(20) NOT NULL, failed_login_attempts INTEGER NOT NULL, locked_until DATETIME DEFAULT NULL, last_login_at DATETIME DEFAULT NULL, mfa_secret VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, user_id INTEGER NOT NULL, CONSTRAINT FK_AC64A0BAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AC64A0BAA76ED395 ON api_user (user_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AC64A0BAE7927C74 ON api_user (email)');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, organization_id VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, roles_data CLOB DEFAULT NULL, access_rights CLOB DEFAULT NULL)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE api_refresh_token');
        $this->addSql('DROP TABLE api_user');
        $this->addSql('DROP TABLE user');
    }
}
