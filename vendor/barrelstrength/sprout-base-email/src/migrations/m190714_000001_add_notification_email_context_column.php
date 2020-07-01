<?php

namespace barrelstrength\sproutbaseemail\migrations;

use craft\db\Migration;
use craft\db\Query;
use yii\base\NotSupportedException;

/**
 * m190714_000001_add_notification_email_context_column migration.
 */
class m190714_000001_add_notification_email_context_column extends Migration
{
    /**
     * @inheritdoc
     *
     * @throws NotSupportedException
     */
    public function safeUp(): bool
    {
        $table = '{{%sproutemail_notificationemails}}';

        // Add a `viewContext` column
        if (!$this->db->columnExists($table, 'viewContext')) {
            $this->addColumn($table, 'viewContext', $this->string()->after('id'));
        }

        // Migrate data to the `viewContext` column, Remove the pluginHandle column
        if ($this->db->columnExists($table, 'pluginHandle')) {
            $notificationEmails = (new Query())
                ->select(['*'])
                ->from([$table])
                ->all();

            foreach ($notificationEmails as $notificationEmail) {

                switch ($notificationEmail['pluginHandle']) {
                    case 'sprout-email':
                    case '':
                        $this->update($table, ['viewContext' => 'global'], ['id' => $notificationEmail['id']], [], false);
                        break;
                    case 'sprout-forms':
                        $this->update($table, ['viewContext' => 'sprout-forms'], ['id' => $notificationEmail['id']], [], false);
                        break;
                    default:
                        // Use the same handle
                        $this->update($table, ['viewContext' => $notificationEmail['pluginHandle']], ['id' => $notificationEmail['id']], [], false);
                        break;
                }
            }

            // @deprecated remove this column on Craft 4
            // We have left it in the db for now, because some migrations still require
            // it to be there even though it has been removed from the Notification Email model
            // $this->dropColumn($table, 'pluginHandle');
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190714_000001_add_notification_email_context_column cannot be reverted.\n";

        return false;
    }
}
