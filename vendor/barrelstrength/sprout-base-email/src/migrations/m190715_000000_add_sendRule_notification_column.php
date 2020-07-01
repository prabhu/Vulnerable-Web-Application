<?php

namespace barrelstrength\sproutbaseemail\migrations;

use craft\db\Migration;
use craft\db\Query;
use yii\base\NotSupportedException;

/**
 * m190715_000000_add_sendRule_notification_column migration.
 */
class m190715_000000_add_sendRule_notification_column extends Migration
{
    /**
     * @return bool
     * @throws NotSupportedException
     */
    public function safeUp(): bool
    {
        $table = '{{%sproutemail_notificationemails}}';

        if (!$this->db->columnExists($table, 'sendRule')) {
            $this->addColumn($table, 'sendRule', $this->text()->after('viewContext'));
        }

        $notifications = (new Query())
            ->select(['id'])
            ->from(['{{%sproutemail_notificationemails}}'])
            ->all();

        foreach ($notifications as $notification) {
            $this->update('{{%sproutemail_notificationemails}}', ['sendRule' => '*'], ['id' => $notification['id']], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190715_000000_add_sendRule_notification_column cannot be reverted.\n";

        return false;
    }
}
