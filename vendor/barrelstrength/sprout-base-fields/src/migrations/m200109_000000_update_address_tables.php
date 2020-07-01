<?php

namespace barrelstrength\sproutbasefields\migrations;

use craft\db\Migration;

/**
 * m200109_000000_update_address_tables migration.
 */
class m200109_000000_update_address_tables extends Migration
{
    /**
     * @return bool
     */
    public function safeUp(): bool
    {
        $oldAddressTableName = '{{%sproutfields_addresses}}';
        $newAddressTableName = '{{%sprout_addresses}}';

        if ($this->db->tableExists($oldAddressTableName) && !$this->db->tableExists($newAddressTableName)) {
            $this->renameTable($oldAddressTableName, $newAddressTableName);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200109_000000_update_address_tables cannot be reverted.\n";

        return false;
    }
}
