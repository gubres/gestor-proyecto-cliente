<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240430122617 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tareas ADD descripcion VARCHAR(255) DEFAULT NULL, DROP finalizado_en');
        $this->addSql('ALTER TABLE usuarios ADD creado_en DATETIME DEFAULT NULL, ADD actualizado_en DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tareas ADD finalizado_en DATETIME DEFAULT NULL, DROP descripcion');
        $this->addSql('ALTER TABLE usuarios DROP creado_en, DROP actualizado_en');
    }
}
