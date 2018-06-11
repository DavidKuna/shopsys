<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20180611123750 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->sql('ALTER TABLE transport_domains ADD enabled BOOLEAN NOT NULL');
        $this->sql('ALTER TABLE payment_domains ADD enabled BOOLEAN NOT NULL');
        $this->sql('ALTER TABLE transport_domains DROP CONSTRAINT FK_18AC7F6C9909C13F');
        $this->sql('
            ALTER TABLE
                transport_domains
            ADD
                CONSTRAINT FK_18AC7F6C9909C13F FOREIGN KEY (transport_id) REFERENCES transports (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('ALTER TABLE payment_domains DROP CONSTRAINT FK_9532B1774C3A3BB');
        $this->sql('
            ALTER TABLE
                payment_domains
            ADD
                CONSTRAINT FK_9532B1774C3A3BB FOREIGN KEY (payment_id) REFERENCES payments (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}
