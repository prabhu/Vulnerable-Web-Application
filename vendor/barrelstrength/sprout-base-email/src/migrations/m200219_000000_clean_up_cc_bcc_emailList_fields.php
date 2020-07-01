<?php

namespace barrelstrength\sproutbaseemail\migrations;

use craft\db\Migration;

class m200219_000000_clean_up_cc_bcc_emailList_fields extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->update('{{%sproutemail_notificationemails}}', [
            'cc' => null,
            'bcc' => null,
        ], ['sendMethod' => 'emailList'], [], false);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200219_000000_clean_up_cc_bcc_emailList_fields cannot be reverted.\n";

        return false;
    }
}
