<?php

namespace barrelstrength\sproutbaseemail\migrations;

use craft\db\Migration;
use yii\base\NotSupportedException;

/**
 * m200110_000002_update_sendRule_column_to_text_type migration.
 */
class m200110_000002_update_sendRule_column_to_text_type extends Migration
{
    /**
     * @return bool
     * @throws NotSupportedException
     */
    public function safeUp(): bool
    {
        $table = '{{%sproutemail_notificationemails}}';

        // Our install migration created this as a string for a short time
        // so there is a change it needs to be updated
        if ($this->db->columnExists($table, 'sendRule')) {
            $this->alterColumn($table, 'sendRule', $this->text());
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200110_000002_update_sendRule_column_to_text_type cannot be reverted.\n";

        return false;
    }
}
