<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230221100522 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE classe (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(64) NOT NULL, description LONGTEXT NOT NULL, picture VARCHAR(255) NOT NULL, hit_die INT NOT NULL, stats LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE classe_equipment (id INT AUTO_INCREMENT NOT NULL, classe_id INT NOT NULL, equipment_id INT NOT NULL, number INT NOT NULL, INDEX IDX_E26C95A48F5EA509 (classe_id), INDEX IDX_E26C95A4517FE9FE (equipment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE equipment (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(64) NOT NULL, description LONGTEXT DEFAULT NULL, damage LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', attack_type VARCHAR(32) DEFAULT NULL, hand INT DEFAULT NULL, distance INT DEFAULT NULL, bonus LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE race (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(64) NOT NULL, description LONGTEXT NOT NULL, partiality LONGTEXT NOT NULL, stats LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', picture_principal VARCHAR(255) NOT NULL, picture_male VARCHAR(255) NOT NULL, picture_female VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE racial_ability (id INT AUTO_INCREMENT NOT NULL, race_id INT NOT NULL, name VARCHAR(64) NOT NULL, description LONGTEXT NOT NULL, bonus LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', traits LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_DD5DD6676E59D40D (race_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE religion (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(64) NOT NULL, description LONGTEXT NOT NULL, alignment TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sheet (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, classe_id INT NOT NULL, racial_ability_id INT NOT NULL, character_name VARCHAR(64) NOT NULL, race_name VARCHAR(64) NOT NULL, religion_name VARCHAR(64) DEFAULT NULL, description LONGTEXT DEFAULT NULL, age INT DEFAULT NULL, level INT NOT NULL, picture VARCHAR(255) NOT NULL, height INT DEFAULT NULL, weight INT DEFAULT NULL, hair VARCHAR(32) DEFAULT NULL, stats LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_873C91E2A76ED395 (user_id), INDEX IDX_873C91E28F5EA509 (classe_id), INDEX IDX_873C91E2E9615798 (racial_ability_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sheet_way_ability (sheet_id INT NOT NULL, way_ability_id INT NOT NULL, INDEX IDX_686F32598B1206A5 (sheet_id), INDEX IDX_686F3259BC4541DA (way_ability_id), PRIMARY KEY(sheet_id, way_ability_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE stat (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(32) NOT NULL, description LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, pseudo VARCHAR(64) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE way (id INT AUTO_INCREMENT NOT NULL, classe_id INT NOT NULL, name VARCHAR(64) NOT NULL, INDEX IDX_FBC034B98F5EA509 (classe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE way_ability (id INT AUTO_INCREMENT NOT NULL, way_id INT NOT NULL, name VARCHAR(64) NOT NULL, description LONGTEXT NOT NULL, limited TINYINT(1) NOT NULL, bonus LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', traits LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', cost INT NOT NULL, level INT NOT NULL, INDEX IDX_FA1E3C748C803113 (way_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE classe_equipment ADD CONSTRAINT FK_E26C95A48F5EA509 FOREIGN KEY (classe_id) REFERENCES classe (id)');
        $this->addSql('ALTER TABLE classe_equipment ADD CONSTRAINT FK_E26C95A4517FE9FE FOREIGN KEY (equipment_id) REFERENCES equipment (id)');
        $this->addSql('ALTER TABLE racial_ability ADD CONSTRAINT FK_DD5DD6676E59D40D FOREIGN KEY (race_id) REFERENCES race (id)');
        $this->addSql('ALTER TABLE sheet ADD CONSTRAINT FK_873C91E2A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE sheet ADD CONSTRAINT FK_873C91E28F5EA509 FOREIGN KEY (classe_id) REFERENCES classe (id)');
        $this->addSql('ALTER TABLE sheet ADD CONSTRAINT FK_873C91E2E9615798 FOREIGN KEY (racial_ability_id) REFERENCES racial_ability (id)');
        $this->addSql('ALTER TABLE sheet_way_ability ADD CONSTRAINT FK_686F32598B1206A5 FOREIGN KEY (sheet_id) REFERENCES sheet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sheet_way_ability ADD CONSTRAINT FK_686F3259BC4541DA FOREIGN KEY (way_ability_id) REFERENCES way_ability (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE way ADD CONSTRAINT FK_FBC034B98F5EA509 FOREIGN KEY (classe_id) REFERENCES classe (id)');
        $this->addSql('ALTER TABLE way_ability ADD CONSTRAINT FK_FA1E3C748C803113 FOREIGN KEY (way_id) REFERENCES way (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE classe_equipment DROP FOREIGN KEY FK_E26C95A48F5EA509');
        $this->addSql('ALTER TABLE classe_equipment DROP FOREIGN KEY FK_E26C95A4517FE9FE');
        $this->addSql('ALTER TABLE racial_ability DROP FOREIGN KEY FK_DD5DD6676E59D40D');
        $this->addSql('ALTER TABLE sheet DROP FOREIGN KEY FK_873C91E2A76ED395');
        $this->addSql('ALTER TABLE sheet DROP FOREIGN KEY FK_873C91E28F5EA509');
        $this->addSql('ALTER TABLE sheet DROP FOREIGN KEY FK_873C91E2E9615798');
        $this->addSql('ALTER TABLE sheet_way_ability DROP FOREIGN KEY FK_686F32598B1206A5');
        $this->addSql('ALTER TABLE sheet_way_ability DROP FOREIGN KEY FK_686F3259BC4541DA');
        $this->addSql('ALTER TABLE way DROP FOREIGN KEY FK_FBC034B98F5EA509');
        $this->addSql('ALTER TABLE way_ability DROP FOREIGN KEY FK_FA1E3C748C803113');
        $this->addSql('DROP TABLE classe');
        $this->addSql('DROP TABLE classe_equipment');
        $this->addSql('DROP TABLE equipment');
        $this->addSql('DROP TABLE race');
        $this->addSql('DROP TABLE racial_ability');
        $this->addSql('DROP TABLE religion');
        $this->addSql('DROP TABLE sheet');
        $this->addSql('DROP TABLE sheet_way_ability');
        $this->addSql('DROP TABLE stat');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE way');
        $this->addSql('DROP TABLE way_ability');
    }
}
