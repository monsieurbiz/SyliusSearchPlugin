<?php

/*
 * This file is part of Monsieur Biz' Search plugin for Sylius.
 *
 * (c) Monsieur Biz <sylius@monsieurbiz.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211012092353 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add search columns on product attribute and product option';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_product_attribute ADD searchable TINYINT(1) DEFAULT \'1\' NOT NULL, ADD filterable TINYINT(1) DEFAULT \'0\' NOT NULL, ADD search_weight SMALLINT UNSIGNED DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE sylius_product_option ADD searchable TINYINT(1) DEFAULT \'1\' NOT NULL, ADD filterable TINYINT(1) DEFAULT \'0\' NOT NULL, ADD search_weight SMALLINT UNSIGNED DEFAULT 1 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_product_attribute DROP searchable, DROP filterable, DROP search_weight');
        $this->addSql('ALTER TABLE sylius_product_option DROP searchable, DROP filterable, DROP search_weight');
    }
}
