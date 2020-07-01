<?php

namespace barrelstrength\sproutbaseemail\migrations;

use craft\db\Migration;
use yii\base\NotSupportedException;

/**
 * m181128_000000_add_list_settings_column migration.
 */
class m181128_000000_add_list_settings_column extends Migration
{
    /**
     * @return bool|void
     * @throws NotSupportedException
     */
    public function safeUp()
    {
        $table = '{{%sproutemail_notificationemails}}';

        if (!$this->db->columnExists($table, 'listSettings')) {
            $this->addColumn($table, 'listSettings', $this->string()->after('recipients'));
        }

        $table = '{{%sproutemail_campaignemails}}';

        if (!$this->db->columnExists($table, 'listSettings')) {
            $this->addColumn($table, 'listSettings', $this->string()->after('recipients'));
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m181128_000000_add_list_settings_column cannot be reverted.\n";

        return false;
    }
}
