<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221027130013 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE activation (id INT AUTO_INCREMENT NOT NULL, card_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', amount DOUBLE PRECISION DEFAULT NULL, monthto INT NOT NULL, INDEX IDX_1C6860774ACC9A20 (card_id), INDEX IDX_1C686077B03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE agence (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, address VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bouquet (id INT AUTO_INCREMENT NOT NULL, price DOUBLE PRECISION DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, numero INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE card (id INT AUTO_INCREMENT NOT NULL, numerocard VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL, bouquets LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE card_customer (id INT AUTO_INCREMENT NOT NULL, customer_id INT DEFAULT NULL, card_id INT DEFAULT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_active TINYINT(1) NOT NULL, periodto DATETIME DEFAULT NULL, INDEX IDX_3808408A9395C3F3 (customer_id), INDEX IDX_3808408A4ACC9A20 (card_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE customer (id INT AUTO_INCREMENT NOT NULL, compte_id INT DEFAULT NULL, agence_id INT DEFAULT NULL, datecreation DATETIME DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_81398E09F2C56620 (compte_id), INDEX IDX_81398E09D725330D (agence_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE personnel (id INT AUTO_INCREMENT NOT NULL, agence_id INT DEFAULT NULL, compte_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_A6BCF3DED725330D (agence_id), UNIQUE INDEX UNIQ_A6BCF3DEF2C56620 (compte_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE souscription (id INT AUTO_INCREMENT NOT NULL, bouquet_id INT DEFAULT NULL, customer_id INT DEFAULT NULL, created DATETIME DEFAULT NULL, expired_at DATETIME DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, reference VARCHAR(255) DEFAULT NULL, INDEX IDX_2AED620D6C8DF983 (bouquet_id), INDEX IDX_2AED620D9395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, name VARCHAR(250) NOT NULL, phone VARCHAR(250) DEFAULT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, isactivate TINYINT(1) DEFAULT NULL, facebook_id VARCHAR(250) DEFAULT NULL, google_id VARCHAR(250) DEFAULT NULL, avatar VARCHAR(250) DEFAULT NULL, userid INT DEFAULT NULL, bouquets JSON DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE activation ADD CONSTRAINT FK_1C6860774ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id)');
        $this->addSql('ALTER TABLE activation ADD CONSTRAINT FK_1C686077B03A8386 FOREIGN KEY (created_by_id) REFERENCES personnel (id)');
        $this->addSql('ALTER TABLE card_customer ADD CONSTRAINT FK_3808408A9395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        $this->addSql('ALTER TABLE card_customer ADD CONSTRAINT FK_3808408A4ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id)');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E09F2C56620 FOREIGN KEY (compte_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E09D725330D FOREIGN KEY (agence_id) REFERENCES agence (id)');
        $this->addSql('ALTER TABLE personnel ADD CONSTRAINT FK_A6BCF3DED725330D FOREIGN KEY (agence_id) REFERENCES agence (id)');
        $this->addSql('ALTER TABLE personnel ADD CONSTRAINT FK_A6BCF3DEF2C56620 FOREIGN KEY (compte_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE souscription ADD CONSTRAINT FK_2AED620D6C8DF983 FOREIGN KEY (bouquet_id) REFERENCES bouquet (id)');
        $this->addSql('ALTER TABLE souscription ADD CONSTRAINT FK_2AED620D9395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activation DROP FOREIGN KEY FK_1C6860774ACC9A20');
        $this->addSql('ALTER TABLE activation DROP FOREIGN KEY FK_1C686077B03A8386');
        $this->addSql('ALTER TABLE card_customer DROP FOREIGN KEY FK_3808408A9395C3F3');
        $this->addSql('ALTER TABLE card_customer DROP FOREIGN KEY FK_3808408A4ACC9A20');
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E09F2C56620');
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E09D725330D');
        $this->addSql('ALTER TABLE personnel DROP FOREIGN KEY FK_A6BCF3DED725330D');
        $this->addSql('ALTER TABLE personnel DROP FOREIGN KEY FK_A6BCF3DEF2C56620');
        $this->addSql('ALTER TABLE souscription DROP FOREIGN KEY FK_2AED620D6C8DF983');
        $this->addSql('ALTER TABLE souscription DROP FOREIGN KEY FK_2AED620D9395C3F3');
        $this->addSql('DROP TABLE activation');
        $this->addSql('DROP TABLE agence');
        $this->addSql('DROP TABLE bouquet');
        $this->addSql('DROP TABLE card');
        $this->addSql('DROP TABLE card_customer');
        $this->addSql('DROP TABLE customer');
        $this->addSql('DROP TABLE personnel');
        $this->addSql('DROP TABLE souscription');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
