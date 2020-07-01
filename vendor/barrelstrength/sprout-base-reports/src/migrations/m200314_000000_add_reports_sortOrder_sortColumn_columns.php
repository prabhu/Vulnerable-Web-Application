<?php

namespace barrelstrength\sproutbasereports\migrations;

use craft\db\Migration;
use yii\base\NotSupportedException;

class m200314_000000_add_reports_sortOrder_sortColumn_columns extends Migration
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
        if (!$this->db->columnExists($reportsTable, 'sortOrder')) {
            $this->addColumn($reportsTable, 'sortOrder', $this->string()->after('allowHtml'));

            $this->addColumn($reportsTable, 'sortColumn', $this->string()->after('sortOrder'));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200314_000000_add_reports_sortOrder_sortColumn_columns cannot be reverted.\n";

        return false;
    }
}
