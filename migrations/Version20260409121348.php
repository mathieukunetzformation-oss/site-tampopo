<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260409121348 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reservation (id INT AUTO_INCREMENT NOT NULL, firstname VARCHAR(100) NOT NULL, surname VARCHAR(100) NOT NULL, party_size INT NOT NULL, phone_number VARCHAR(25) NOT NULL, email VARCHAR(180) NOT NULL, date DATE NOT NULL, time TIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE reservation_table (reservation_id INT NOT NULL, table_id INT NOT NULL, INDEX IDX_B5565FE1B83297E7 (reservation_id), INDEX IDX_B5565FE1ECFF285C (table_id), PRIMARY KEY (reservation_id, table_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `table` (id INT AUTO_INCREMENT NOT NULL, number VARCHAR(20) NOT NULL, seats INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE reservation_table ADD CONSTRAINT FK_B5565FE1B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reservation_table ADD CONSTRAINT FK_B5565FE1ECFF285C FOREIGN KEY (table_id) REFERENCES `table` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE reservation_table DROP FOREIGN KEY FK_B5565FE1B83297E7');
        $this->addSql('ALTER TABLE reservation_table DROP FOREIGN KEY FK_B5565FE1ECFF285C');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('DROP TABLE reservation_table');
        $this->addSql('DROP TABLE `table`');
    }
}
