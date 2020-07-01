<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbaseemail\migrations;

use craft\db\Migration;

class Install extends Migration
{
    private $notificationEmailTable = '{{%sproutemail_notificationemails}}';

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTables();
    }

    public function createTables()
    {
        $notificationTable = $this->getDb()->tableExists($this->notificationEmailTable);

        if ($notificationTable == false) {
            $this->createTable($this->notificationEmailTable,
                [
                    'id' => $this->primaryKey(),
                    'viewContext' => $this->string(),
                    'titleFormat' => $this->string(),
                    'emailTemplateId' => $this->string(),
                    'eventId' => $this->string(),
                    'settings' => $this->text(),
                    'sendRule' => $this->text(),
                    'subjectLine' => $this->string()->notNull(),
                    'defaultBody' => $this->text(),
                    'recipients' => $this->text(),
                    'cc' => $this->text(),
                    'bcc' => $this->text(),
                    'listSettings' => $this->text(),
                    'fromName' => $this->string(),
                    'fromEmail' => $this->string(),
                    'replyToEmail' => $this->string(),
                    'sendMethod' => $this->string(),
                    'enableFileAttachments' => $this->boolean(),
                    'dateCreated' => $this->dateTime(),
                    'dateUpdated' => $this->dateTime(),
                    'fieldLayoutId' => $this->integer(),
                    'uid' => $this->uid()
                ]
            );

            $this->addForeignKey(null, $this->notificationEmailTable, ['id'], '{{%elements}}', ['id'], 'CASCADE');
        }
    }

    public function dropTables()
    {
        $notificationTable = $this->getDb()->tableExists($this->notificationEmailTable);

        if ($notificationTable) {
            $this->dropTable($this->notificationEmailTable);
        }
    }
}