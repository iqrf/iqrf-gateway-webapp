<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * User role update database migration
 */
final class Version20220313055633 extends AbstractMigration {
    /**
     * Returns the migration description
     * @return string Migration description
     */
    public function getDescription(): string {
        return 'User role update database migration';
    }

    public function up(Schema $schema): void {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('UPDATE "users" SET role="admin" WHERE role="normal" OR role="power"');
        $this->addSql('UPDATE "users" SET role="basic" WHERE role="iqaros"');
    }

    public function down(Schema $schema): void {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('UPDATE "users" SET role="normal" WHERE role="admin"');
        $this->addSql('UPDATE "users" SET role="iqaros" WHERE role="basic"');
    }
}
