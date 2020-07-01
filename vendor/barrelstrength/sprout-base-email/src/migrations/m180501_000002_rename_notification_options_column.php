<?php

namespace barrelstrength\sproutbaseemail\migrations;

use craft\db\Migration;
use yii\base\NotSupportedException;

/**
 * m180515_000002_rename_notification_options_column migration.
 */
class m180501_000002_rename_notification_options_column extends Migration
{
    /**
     * @inheritdoc
     *
     * @throws NotSupportedException
     */
    public function safeUp(): bool
    {
        // Craft 2 Updates
        $table = '{{%sproutemail_notificationemails}}';

        if ($this->db->columnExists($table, 'options')) {
            $this->renameColumn($table, 'options', 'settings');
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180515_000002_rename_notification_options_column cannot be reverted.\n";

        return false;
    }
}
