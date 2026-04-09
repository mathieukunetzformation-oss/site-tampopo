<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260408171517 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu_category CHANGE title title VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE menu_page CHANGE title title VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE menu_product DROP price, DROP quantity, CHANGE title title VARCHAR(100) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu_category CHANGE title title VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE menu_page CHANGE title title VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE menu_product ADD price NUMERIC(10, 2) DEFAULT NULL, ADD quantity INT DEFAULT NULL, CHANGE title title VARCHAR(255) NOT NULL');
    }
}
