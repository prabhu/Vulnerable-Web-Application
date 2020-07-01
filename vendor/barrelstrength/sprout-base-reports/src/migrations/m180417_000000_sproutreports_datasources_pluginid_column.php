<?php

namespace barrelstrength\sproutbasereports\migrations;

use craft\db\Migration;
use yii\base\NotSupportedException;

/**
 * m180417_000000_sproutreports_datasources_pluginid_column migration.
 */
class m180417_000000_sproutreports_datasources_pluginid_column extends Migration
{
    /**
     * @inheritdoc
     *
     * @throws NotSupportedException
     */
    public function safeUp(): bool
    {
        $table = '{{%sproutreports_datasources}}';

        if (!$this->db->columnExists($table, 'pluginId')) {
            $this->addColumn($table, 'pluginId', $this->string()->after('id'));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180417_000000_sproutreports_datasources_pluginid_column cannot be reverted.\n";

        return false;
    }
}
