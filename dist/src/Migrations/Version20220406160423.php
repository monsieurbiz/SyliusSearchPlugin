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

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220406160423 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change length of messenger_messages.queue_name column and create missing indexes, cf https://github.com/Sylius/Sylius/issues/13838';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('messenger_messages')) {
            $this->addSql('ALTER TABLE messenger_messages CHANGE queue_name queue_name VARCHAR(190) NOT NULL');
            $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
            $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        }
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable('messenger_messages')) {
            $this->addSql('DROP INDEX IDX_75EA56E0FB7336F0 ON messenger_messages');
            $this->addSql('DROP INDEX IDX_75EA56E0E3BD61CE ON messenger_messages');
            $this->addSql('ALTER TABLE messenger_messages CHANGE queue_name queue_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        }
    }
}
