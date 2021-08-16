<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210816021706 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('CREATE TABLE "password_recovery" (uuid CHAR(36) NOT NULL --(DC2Type:uuid)
        , user INTEGER DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(uuid))');
        $this->addSql('CREATE INDEX IDX_63D401098D93D649 ON "password_recovery" (user)');
        $this->addSql('DROP INDEX IDX_FE223588D93D649');
        $this->addSql('CREATE TEMPORARY TABLE __temp__email_verification AS SELECT uuid, user, created_at FROM email_verification');
        $this->addSql('DROP TABLE email_verification');
        $this->addSql('CREATE TABLE email_verification (uuid CHAR(36) NOT NULL COLLATE BINARY --(DC2Type:uuid)
        , user INTEGER DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(uuid), CONSTRAINT FK_FE223588D93D649 FOREIGN KEY (user) REFERENCES "users" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO email_verification (uuid, user, created_at) SELECT uuid, user, created_at FROM __temp__email_verification');
        $this->addSql('DROP TABLE __temp__email_verification');
        $this->addSql('CREATE INDEX IDX_FE223588D93D649 ON email_verification (user)');
        $this->addSql('DROP INDEX UNIQ_EA5C8753AB0BE982');
        $this->addSql('CREATE TEMPORARY TABLE __temp__wireguard_interface_ipv4s AS SELECT id, interface_id, address, prefix FROM wireguard_interface_ipv4s');
        $this->addSql('DROP TABLE wireguard_interface_ipv4s');
        $this->addSql('CREATE TABLE wireguard_interface_ipv4s (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, interface_id INTEGER DEFAULT NULL, address BLOB NOT NULL --(DC2Type:ip)
        , prefix INTEGER NOT NULL, CONSTRAINT FK_EA5C8753AB0BE982 FOREIGN KEY (interface_id) REFERENCES "wireguard_interfaces" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO wireguard_interface_ipv4s (id, interface_id, address, prefix) SELECT id, interface_id, address, prefix FROM __temp__wireguard_interface_ipv4s');
        $this->addSql('DROP TABLE __temp__wireguard_interface_ipv4s');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EA5C8753AB0BE982 ON wireguard_interface_ipv4s (interface_id)');
        $this->addSql('DROP INDEX UNIQ_D86AE5D1AB0BE982');
        $this->addSql('CREATE TEMPORARY TABLE __temp__wireguard_interface_ipv6s AS SELECT id, interface_id, address, prefix FROM wireguard_interface_ipv6s');
        $this->addSql('DROP TABLE wireguard_interface_ipv6s');
        $this->addSql('CREATE TABLE wireguard_interface_ipv6s (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, interface_id INTEGER DEFAULT NULL, address BLOB NOT NULL --(DC2Type:ip)
        , prefix INTEGER NOT NULL, CONSTRAINT FK_D86AE5D1AB0BE982 FOREIGN KEY (interface_id) REFERENCES "wireguard_interfaces" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO wireguard_interface_ipv6s (id, interface_id, address, prefix) SELECT id, interface_id, address, prefix FROM __temp__wireguard_interface_ipv6s');
        $this->addSql('DROP TABLE __temp__wireguard_interface_ipv6s');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D86AE5D1AB0BE982 ON wireguard_interface_ipv6s (interface_id)');
        $this->addSql('DROP INDEX IDX_AB85CDC120D91DB4');
        $this->addSql('CREATE TEMPORARY TABLE __temp__wireguard_peer_addresses AS SELECT id, peer_id, address, prefix FROM wireguard_peer_addresses');
        $this->addSql('DROP TABLE wireguard_peer_addresses');
        $this->addSql('CREATE TABLE wireguard_peer_addresses (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, peer_id INTEGER DEFAULT NULL, address BLOB NOT NULL --(DC2Type:ip)
        , prefix INTEGER NOT NULL, CONSTRAINT FK_AB85CDC120D91DB4 FOREIGN KEY (peer_id) REFERENCES "wireguard_peers" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO wireguard_peer_addresses (id, peer_id, address, prefix) SELECT id, peer_id, address, prefix FROM __temp__wireguard_peer_addresses');
        $this->addSql('DROP TABLE __temp__wireguard_peer_addresses');
        $this->addSql('CREATE INDEX IDX_AB85CDC120D91DB4 ON wireguard_peer_addresses (peer_id)');
        $this->addSql('DROP INDEX IDX_23ACBD91AB0BE982');
        $this->addSql('CREATE TEMPORARY TABLE __temp__wireguard_peers AS SELECT id, interface_id, public_key, psk, keepalive, endpoint, port FROM wireguard_peers');
        $this->addSql('DROP TABLE wireguard_peers');
        $this->addSql('CREATE TABLE wireguard_peers (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, interface_id INTEGER DEFAULT NULL, public_key VARCHAR(255) NOT NULL COLLATE BINARY, psk VARCHAR(255) DEFAULT NULL COLLATE BINARY, keepalive INTEGER NOT NULL, endpoint VARCHAR(255) NOT NULL COLLATE BINARY, port INTEGER NOT NULL, CONSTRAINT FK_23ACBD91AB0BE982 FOREIGN KEY (interface_id) REFERENCES "wireguard_interfaces" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO wireguard_peers (id, interface_id, public_key, psk, keepalive, endpoint, port) SELECT id, interface_id, public_key, psk, keepalive, endpoint, port FROM __temp__wireguard_peers');
        $this->addSql('DROP TABLE __temp__wireguard_peers');
        $this->addSql('CREATE INDEX IDX_23ACBD91AB0BE982 ON wireguard_peers (interface_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP TABLE "password_recovery"');
        $this->addSql('DROP INDEX IDX_FE223588D93D649');
        $this->addSql('CREATE TEMPORARY TABLE __temp__email_verification AS SELECT uuid, user, created_at FROM "email_verification"');
        $this->addSql('DROP TABLE "email_verification"');
        $this->addSql('CREATE TABLE "email_verification" (uuid CHAR(36) NOT NULL --(DC2Type:uuid)
        , user INTEGER DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(uuid))');
        $this->addSql('INSERT INTO "email_verification" (uuid, user, created_at) SELECT uuid, user, created_at FROM __temp__email_verification');
        $this->addSql('DROP TABLE __temp__email_verification');
        $this->addSql('CREATE INDEX IDX_FE223588D93D649 ON "email_verification" (user)');
        $this->addSql('DROP INDEX UNIQ_EA5C8753AB0BE982');
        $this->addSql('CREATE TEMPORARY TABLE __temp__wireguard_interface_ipv4s AS SELECT id, interface_id, address, prefix FROM "wireguard_interface_ipv4s"');
        $this->addSql('DROP TABLE "wireguard_interface_ipv4s"');
        $this->addSql('CREATE TABLE "wireguard_interface_ipv4s" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, interface_id INTEGER DEFAULT NULL, address BLOB NOT NULL --(DC2Type:ip)
        , prefix INTEGER NOT NULL)');
        $this->addSql('INSERT INTO "wireguard_interface_ipv4s" (id, interface_id, address, prefix) SELECT id, interface_id, address, prefix FROM __temp__wireguard_interface_ipv4s');
        $this->addSql('DROP TABLE __temp__wireguard_interface_ipv4s');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EA5C8753AB0BE982 ON "wireguard_interface_ipv4s" (interface_id)');
        $this->addSql('DROP INDEX UNIQ_D86AE5D1AB0BE982');
        $this->addSql('CREATE TEMPORARY TABLE __temp__wireguard_interface_ipv6s AS SELECT id, interface_id, address, prefix FROM "wireguard_interface_ipv6s"');
        $this->addSql('DROP TABLE "wireguard_interface_ipv6s"');
        $this->addSql('CREATE TABLE "wireguard_interface_ipv6s" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, interface_id INTEGER DEFAULT NULL, address BLOB NOT NULL --(DC2Type:ip)
        , prefix INTEGER NOT NULL)');
        $this->addSql('INSERT INTO "wireguard_interface_ipv6s" (id, interface_id, address, prefix) SELECT id, interface_id, address, prefix FROM __temp__wireguard_interface_ipv6s');
        $this->addSql('DROP TABLE __temp__wireguard_interface_ipv6s');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D86AE5D1AB0BE982 ON "wireguard_interface_ipv6s" (interface_id)');
        $this->addSql('DROP INDEX IDX_AB85CDC120D91DB4');
        $this->addSql('CREATE TEMPORARY TABLE __temp__wireguard_peer_addresses AS SELECT id, peer_id, address, prefix FROM "wireguard_peer_addresses"');
        $this->addSql('DROP TABLE "wireguard_peer_addresses"');
        $this->addSql('CREATE TABLE "wireguard_peer_addresses" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, peer_id INTEGER DEFAULT NULL, address BLOB NOT NULL --(DC2Type:ip)
        , prefix INTEGER NOT NULL)');
        $this->addSql('INSERT INTO "wireguard_peer_addresses" (id, peer_id, address, prefix) SELECT id, peer_id, address, prefix FROM __temp__wireguard_peer_addresses');
        $this->addSql('DROP TABLE __temp__wireguard_peer_addresses');
        $this->addSql('CREATE INDEX IDX_AB85CDC120D91DB4 ON "wireguard_peer_addresses" (peer_id)');
        $this->addSql('DROP INDEX IDX_23ACBD91AB0BE982');
        $this->addSql('CREATE TEMPORARY TABLE __temp__wireguard_peers AS SELECT id, interface_id, public_key, psk, keepalive, endpoint, port FROM "wireguard_peers"');
        $this->addSql('DROP TABLE "wireguard_peers"');
        $this->addSql('CREATE TABLE "wireguard_peers" (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, interface_id INTEGER DEFAULT NULL, public_key VARCHAR(255) NOT NULL, psk VARCHAR(255) DEFAULT NULL, keepalive INTEGER NOT NULL, endpoint VARCHAR(255) NOT NULL, port INTEGER NOT NULL)');
        $this->addSql('INSERT INTO "wireguard_peers" (id, interface_id, public_key, psk, keepalive, endpoint, port) SELECT id, interface_id, public_key, psk, keepalive, endpoint, port FROM __temp__wireguard_peers');
        $this->addSql('DROP TABLE __temp__wireguard_peers');
        $this->addSql('CREATE INDEX IDX_23ACBD91AB0BE982 ON "wireguard_peers" (interface_id)');
    }
}
