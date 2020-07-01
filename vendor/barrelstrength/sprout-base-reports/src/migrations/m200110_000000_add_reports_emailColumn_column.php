<?php

namespace barrelstrength\sproutbasereports\migrations;

use craft\db\Migration;
use yii\base\NotSupportedException;

/**
 * m200110_000000_add_reports_emailColumn_column migration.
 */
class m200110_000000_add_reports_emailColumn_column extends Migration
{
    /**
     * @inheritdoc
     *
     * @throws NotSupportedException
     */
    public function safeUp(): bool
    {
        $reportsTable = '{{%sproutreports_reports}}';

        // Add a `viewContext` column
        if (!$this->db->columnExists($reportsTable, 'emailColumn')) {
            $this->addColumn($reportsTable, 'emailColumn', $this->string()->after('allowHtml'));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200110_000000_add_reports_emailColumn_column cannot be reverted.\n";

        return false;
    }
}
