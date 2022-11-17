<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221117042933 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE card_customer DROP FOREIGN KEY FK_3808408A4ACC9A20');
        $this->addSql('ALTER TABLE card_customer DROP FOREIGN KEY FK_3808408A9395C3F3');
        $this->addSql('ALTER TABLE card_customer ADD CONSTRAINT FK_3808408A4ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE card_customer ADD CONSTRAINT FK_3808408A9395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE card_customer DROP FOREIGN KEY FK_3808408A9395C3F3');
        $this->addSql('ALTER TABLE card_customer DROP FOREIGN KEY FK_3808408A4ACC9A20');
        $this->addSql('ALTER TABLE card_customer ADD CONSTRAINT FK_3808408A9395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE card_customer ADD CONSTRAINT FK_3808408A4ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
