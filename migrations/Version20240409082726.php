<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240409082726 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE clientes (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(50) NOT NULL, telefono VARCHAR(20) NOT NULL, email VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE clientes_proyectos (id INT AUTO_INCREMENT NOT NULL, usuario_id INT DEFAULT NULL, proyecto_id INT DEFAULT NULL, estado TINYINT(1) NOT NULL, fecha_alta DATETIME NOT NULL, fecha_baja DATETIME DEFAULT NULL, INDEX IDX_F4D8A10ADB38439E (usuario_id), INDEX IDX_F4D8A10AF625D1BA (proyecto_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE proyectos (id INT AUTO_INCREMENT NOT NULL, cliente_id INT NOT NULL, nombre VARCHAR(50) NOT NULL, estado VARCHAR(15) NOT NULL, UNIQUE INDEX UNIQ_A9DC1621DE734E51 (cliente_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tareas (id INT AUTO_INCREMENT NOT NULL, proyecto_id INT NOT NULL, nombre VARCHAR(30) NOT NULL, finalizada TINYINT(1) NOT NULL, creado_en DATETIME NOT NULL, prioridad VARCHAR(10) NOT NULL, INDEX IDX_BFE3AB35F625D1BA (proyecto_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tareas_usuarios (tareas_id INT NOT NULL, usuarios_id INT NOT NULL, INDEX IDX_6A63A1FF30113414 (tareas_id), INDEX IDX_6A63A1FFF01D3B25 (usuarios_id), PRIMARY KEY(tareas_id, usuarios_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE usuarios (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, is_verified TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE clientes_proyectos ADD CONSTRAINT FK_F4D8A10ADB38439E FOREIGN KEY (usuario_id) REFERENCES usuarios (id)');
        $this->addSql('ALTER TABLE clientes_proyectos ADD CONSTRAINT FK_F4D8A10AF625D1BA FOREIGN KEY (proyecto_id) REFERENCES proyectos (id)');
        $this->addSql('ALTER TABLE proyectos ADD CONSTRAINT FK_A9DC1621DE734E51 FOREIGN KEY (cliente_id) REFERENCES clientes (id)');
        $this->addSql('ALTER TABLE tareas ADD CONSTRAINT FK_BFE3AB35F625D1BA FOREIGN KEY (proyecto_id) REFERENCES proyectos (id)');
        $this->addSql('ALTER TABLE tareas_usuarios ADD CONSTRAINT FK_6A63A1FF30113414 FOREIGN KEY (tareas_id) REFERENCES tareas (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tareas_usuarios ADD CONSTRAINT FK_6A63A1FFF01D3B25 FOREIGN KEY (usuarios_id) REFERENCES usuarios (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE clientes_proyectos DROP FOREIGN KEY FK_F4D8A10ADB38439E');
        $this->addSql('ALTER TABLE clientes_proyectos DROP FOREIGN KEY FK_F4D8A10AF625D1BA');
        $this->addSql('ALTER TABLE proyectos DROP FOREIGN KEY FK_A9DC1621DE734E51');
        $this->addSql('ALTER TABLE tareas DROP FOREIGN KEY FK_BFE3AB35F625D1BA');
        $this->addSql('ALTER TABLE tareas_usuarios DROP FOREIGN KEY FK_6A63A1FF30113414');
        $this->addSql('ALTER TABLE tareas_usuarios DROP FOREIGN KEY FK_6A63A1FFF01D3B25');
        $this->addSql('DROP TABLE clientes');
        $this->addSql('DROP TABLE clientes_proyectos');
        $this->addSql('DROP TABLE proyectos');
        $this->addSql('DROP TABLE tareas');
        $this->addSql('DROP TABLE tareas_usuarios');
        $this->addSql('DROP TABLE usuarios');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
