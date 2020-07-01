<?php /** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutbasefields\migrations;

use craft\db\Migration;
use yii\base\NotSupportedException;

/**
 * m190313_000000_add_administrativeareacode_column migration.
 */
class m190313_000000_add_administrativeareacode_column extends Migration
{
    /**
     * @inheritdoc
     *
     * @throws NotSupportedException
     */
    public function safeUp(): bool
    {
        $tableName = '{{%sprout_addresses}}';
        $oldAddressTableName = '{{%sproutfields_addresses}}';

        // Support two scenarios
        // 1. A user installed from scratch with the new sprout_addresses table name
        // 2. A user is upgrading and hasn't yet run the update_address_tables migration that renames to use sprout_addresses
        if ($this->db->tableExists($oldAddressTableName)) {
            $tableName = $oldAddressTableName;
        }

        if ($this->db->columnExists($tableName, 'administrativeArea')) {
            $this->renameColumn($tableName, 'administrativeArea', 'administrativeAreaCode');
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190313_000000_add_administrativeareacode_column cannot be reverted.\n";

        return false;
    }
}
