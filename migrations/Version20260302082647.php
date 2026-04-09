<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260302082647 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu_category ADD category_is_displayed TINYINT NOT NULL');
        $this->addSql('ALTER TABLE menu_page ADD title_is_displayed TINYINT NOT NULL, ADD description VARCHAR(255) DEFAULT NULL, ADD page_is_displayed TINYINT NOT NULL, CHANGE page_order page_order INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu_category DROP category_is_displayed');
        $this->addSql('ALTER TABLE menu_page DROP title_is_displayed, DROP description, DROP page_is_displayed, CHANGE page_order page_order INT NOT NULL');
    }
}
