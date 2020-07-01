<?php

namespace barrelstrength\sproutbaseemail\migrations;

use craft\db\Migration;
use craft\db\Query;
use yii\base\NotSupportedException;

/**
 * m200110_000001_add_sendMethod_notification_column migration.
 */
class m200110_000001_add_sendMethod_notification_column extends Migration
{
    /**
     * @return bool
     * @throws NotSupportedException
     */
    public function safeUp(): bool
    {
        $table = '{{%sproutemail_notificationemails}}';

        if (!$this->db->columnExists($table, 'sendMethod')) {
            $this->addColumn($table, 'sendMethod', $this->text()->after('singleEmail'));
        }

        $query = (new Query())
            ->select([
                'id',
                'recipients',
                'cc',
                'bcc',
            ])
            ->from(['{{%sproutemail_notificationemails}}']);

        if ($this->db->columnExists($table, 'singleEmail')) {
            $query->addSelect(['singleEmail']);
        }

        $notifications = $query->all();

        foreach ($notifications as $notification) {
            // Determine the value for the sendMethod column
            $sendMethod = isset($notification['singleEmail']) && $notification['singleEmail'] === 1
                ? 'singleEmail'
                : 'emailList';

            $this->update(
                '{{%sproutemail_notificationemails}}', ['sendMethod' => $sendMethod], ['id' => $notification['id']], [], false);

            // Check the user has any values in the CC or BCC
            $hasCcEmails = trim($notification['cc']) !== '';
            $hasBccEmails = trim($notification['bcc']) !== '';

            // Append cc and bcc emails to the recipients field for non-Single Emails
            // These are just sent as individual emails if they are part of an EmailList send
            if ($sendMethod === 'emailList' && ($hasCcEmails || $hasBccEmails)) {
                $recipients = trim($notification['recipients']);

                if ($hasCcEmails) {
                    $recipients .= ','.trim($notification['cc']);
                }

                if ($hasBccEmails) {
                    $recipients .= ','.trim($notification['bcc']);
                }

                $this->update(
                    '{{%sproutemail_notificationemails}}', ['recipients' => $recipients], ['id' => $notification['id']], [], false);
            }
        }

        // Remove
        if ($this->db->columnExists($table, 'singleEmail')) {
            $this->dropColumn($table, 'singleEmail');
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200110_000001_add_sendMethod_notification_column cannot be reverted.\n";

        return false;
    }
}
