<?php

namespace barrelstrength\sproutbasereports\migrations;

use craft\db\Migration;
use yii\base\NotSupportedException;

/**
 * m180515_000000_rename_datasources_pluginId_column migration.
 */
class m180515_000001_rename_datasources_pluginId_column extends Migration
{
    /**
     * @inheritdoc
     *
     * @throws NotSupportedException
     */
    public function safeUp(): bool
    {
        $table = '{{%sproutreports_datasources}}';

        if ($this->db->columnExists($table, 'pluginId') && !$this->db->columnExists($table, 'pluginHandle')) {
            $this->renameColumn($table, 'pluginId', 'pluginHandle');
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180515_000000_rename_datasources_pluginId_column cannot be reverted.\n";

        return false;
    }
}
