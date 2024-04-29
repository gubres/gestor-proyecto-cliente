<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240424195902 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE clientes ADD creado_por_id INT NOT NULL, ADD actualizado_por_id INT NOT NULL, ADD eliminado TINYINT(1) NOT NULL, ADD creado_en DATETIME NOT NULL, ADD actualizado_en DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE clientes ADD CONSTRAINT FK_50FE07D7FE35D8C4 FOREIGN KEY (creado_por_id) REFERENCES usuarios (id)');
        $this->addSql('ALTER TABLE clientes ADD CONSTRAINT FK_50FE07D7F6167A1C FOREIGN KEY (actualizado_por_id) REFERENCES usuarios (id)');
        $this->addSql('CREATE INDEX IDX_50FE07D7FE35D8C4 ON clientes (creado_por_id)');
        $this->addSql('CREATE INDEX IDX_50FE07D7F6167A1C ON clientes (actualizado_por_id)');
        $this->addSql('ALTER TABLE proyectos ADD creado_por_id INT NOT NULL, ADD actualizado_por_id INT NOT NULL, ADD eliminado TINYINT(1) NOT NULL, ADD actualizado_en DATETIME DEFAULT NULL, ADD creado_en DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE proyectos ADD CONSTRAINT FK_A9DC1621FE35D8C4 FOREIGN KEY (creado_por_id) REFERENCES usuarios (id)');
        $this->addSql('ALTER TABLE proyectos ADD CONSTRAINT FK_A9DC1621F6167A1C FOREIGN KEY (actualizado_por_id) REFERENCES usuarios (id)');
        $this->addSql('CREATE INDEX IDX_A9DC1621FE35D8C4 ON proyectos (creado_por_id)');
        $this->addSql('CREATE INDEX IDX_A9DC1621F6167A1C ON proyectos (actualizado_por_id)');
        $this->addSql('ALTER TABLE tareas ADD creado_por_id INT NOT NULL, ADD actualizado_por_id INT NOT NULL, ADD eliminado TINYINT(1) NOT NULL, ADD actualizado_en DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE tareas ADD CONSTRAINT FK_BFE3AB35FE35D8C4 FOREIGN KEY (creado_por_id) REFERENCES usuarios (id)');
        $this->addSql('ALTER TABLE tareas ADD CONSTRAINT FK_BFE3AB35F6167A1C FOREIGN KEY (actualizado_por_id) REFERENCES usuarios (id)');
        $this->addSql('CREATE INDEX IDX_BFE3AB35FE35D8C4 ON tareas (creado_por_id)');
        $this->addSql('CREATE INDEX IDX_BFE3AB35F6167A1C ON tareas (actualizado_por_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE clientes DROP FOREIGN KEY FK_50FE07D7FE35D8C4');
        $this->addSql('ALTER TABLE clientes DROP FOREIGN KEY FK_50FE07D7F6167A1C');
        $this->addSql('DROP INDEX IDX_50FE07D7FE35D8C4 ON clientes');
        $this->addSql('DROP INDEX IDX_50FE07D7F6167A1C ON clientes');
        $this->addSql('ALTER TABLE clientes DROP creado_por_id, DROP actualizado_por_id, DROP eliminado, DROP creado_en, DROP actualizado_en');
        $this->addSql('ALTER TABLE proyectos DROP FOREIGN KEY FK_A9DC1621FE35D8C4');
        $this->addSql('ALTER TABLE proyectos DROP FOREIGN KEY FK_A9DC1621F6167A1C');
        $this->addSql('DROP INDEX IDX_A9DC1621FE35D8C4 ON proyectos');
        $this->addSql('DROP INDEX IDX_A9DC1621F6167A1C ON proyectos');
        $this->addSql('ALTER TABLE proyectos DROP creado_por_id, DROP actualizado_por_id, DROP eliminado, DROP actualizado_en, DROP creado_en');
        $this->addSql('ALTER TABLE tareas DROP FOREIGN KEY FK_BFE3AB35FE35D8C4');
        $this->addSql('ALTER TABLE tareas DROP FOREIGN KEY FK_BFE3AB35F6167A1C');
        $this->addSql('DROP INDEX IDX_BFE3AB35FE35D8C4 ON tareas');
        $this->addSql('DROP INDEX IDX_BFE3AB35F6167A1C ON tareas');
        $this->addSql('ALTER TABLE tareas DROP creado_por_id, DROP actualizado_por_id, DROP eliminado, DROP actualizado_en');
    }
}
