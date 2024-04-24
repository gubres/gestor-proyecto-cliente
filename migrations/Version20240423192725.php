<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240423192725 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE usuarios_proyectos DROP FOREIGN KEY FK_9BF6E08EDB38439E');
        $this->addSql('ALTER TABLE usuarios_proyectos DROP FOREIGN KEY FK_9BF6E08EF625D1BA');
        $this->addSql('DROP TABLE usuarios_proyectos');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE usuarios_proyectos (id INT AUTO_INCREMENT NOT NULL, usuario_id INT NOT NULL, proyecto_id INT NOT NULL, estado TINYINT(1) NOT NULL, fecha_alta DATETIME NOT NULL, fecha_baja DATETIME DEFAULT NULL, INDEX IDX_9BF6E08EDB38439E (usuario_id), INDEX IDX_9BF6E08EF625D1BA (proyecto_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE usuarios_proyectos ADD CONSTRAINT FK_9BF6E08EDB38439E FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE usuarios_proyectos ADD CONSTRAINT FK_9BF6E08EF625D1BA FOREIGN KEY (proyecto_id) REFERENCES proyectos (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
