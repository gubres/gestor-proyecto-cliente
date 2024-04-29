<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240429084025 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE clientes CHANGE actualizado_en actualizado_en DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE proyectos CHANGE actualizado_en actualizado_en DATETIME DEFAULT NULL, CHANGE creado_en creado_en DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE tareas ADD descripcion LONGTEXT DEFAULT NULL, CHANGE actualizado_en actualizado_en DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE usuarios CHANGE roles roles JSON NOT NULL, CHANGE confirmation_token confirmation_token VARCHAR(255) DEFAULT NULL, CHANGE reset_token reset_token VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE usuarios_proyectos CHANGE fecha_baja fecha_baja DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE clientes CHANGE actualizado_en actualizado_en DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\' COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE proyectos CHANGE actualizado_en actualizado_en DATETIME DEFAULT \'NULL\', CHANGE creado_en creado_en DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE tareas DROP descripcion, CHANGE actualizado_en actualizado_en DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE usuarios CHANGE roles roles JSON NOT NULL COLLATE `utf8mb4_bin`, CHANGE confirmation_token confirmation_token VARCHAR(255) DEFAULT \'NULL\', CHANGE reset_token reset_token VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE usuarios_proyectos CHANGE fecha_baja fecha_baja DATETIME DEFAULT \'NULL\'');
    }
}
