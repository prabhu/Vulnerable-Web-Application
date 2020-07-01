<?php

namespace barrelstrength\sproutbaseemail\migrations;

use craft\db\Migration;
use craft\db\Query;
use yii\db\Exception;

class m190220_000000_clean_up_sent_email_records extends Migration
{
    /**
     * @return bool
     * @throws Exception
     */
    public function safeUp(): bool
    {
        $sentEmails = (new Query())
            ->select(['sentemail.*'])
            ->from(['{{%sproutemail_sentemail}} sentemail'])
            ->limit(null)
            ->leftJoin('{{%elements}} el', '[[sentemail.id]] = [[el.id]]')
            ->where([
                'el.id' => null
            ])->all();

        if (empty($sentEmails)) {
            return true;
        }

        foreach ($sentEmails as $key => $sentEmail) {

            $sentEmailId = $sentEmail['id'];

            (new Query)
                ->createCommand()
                ->delete('{{%sproutemail_sentemail}}', ['[[id]]' => $sentEmailId])
                ->execute();
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190220_000000_clean_up_sent_email_records cannot be reverted.\n";

        return false;
    }
}
