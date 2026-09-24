<?php

/*
 * This file is part of Monsieur Biz' Search plugin for Sylius.
 *
 * (c) Monsieur Biz <sylius@monsieurbiz.com>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/** Test application only: do not install this migration into an existing shop. */
final class Version20260914091137 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add search fields and align the disposable test application Messenger indexes with Symfony 7.4.';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'This test migration requires MySQL.');
        $this->addSql('ALTER TABLE sylius_product_attribute ADD searchable TINYINT(1) DEFAULT 0 NOT NULL, ADD filterable TINYINT(1) DEFAULT 0 NOT NULL, ADD search_weight SMALLINT UNSIGNED DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE sylius_product_option ADD searchable TINYINT(1) DEFAULT 0 NOT NULL, ADD filterable TINYINT(1) DEFAULT 0 NOT NULL, ADD search_weight SMALLINT UNSIGNED DEFAULT 1 NOT NULL');
        $this->addSql('DROP INDEX IDX_75EA56E0FB7336F0 ON messenger_messages');
        $this->addSql('DROP INDEX IDX_75EA56E0E3BD61CE ON messenger_messages');
        $this->addSql('DROP INDEX IDX_75EA56E016BA31DB ON messenger_messages');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('ALTER TABLE sylius_product_attribute DROP searchable, DROP filterable, DROP search_weight');
        $this->addSql('ALTER TABLE sylius_product_option DROP searchable, DROP filterable, DROP search_weight');
    }
}
