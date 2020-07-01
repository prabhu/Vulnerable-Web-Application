<?php

namespace barrelstrength\sproutbasereports\migrations;

use barrelstrength\sproutbasereports\records\Report as ReportRecord;
use craft\db\Migration;
use craft\db\Query;
use yii\base\NotSupportedException;

class m200512_000000_add_reports_delimiter_column extends Migration
{
    /**
     * @inheritdoc
     *
     * @throws NotSupportedException
     */
    public function safeUp(): bool
    {
        if (!$this->db->columnExists(ReportRecord::tableName(), 'delimiter')) {
            $this->addColumn(ReportRecord::tableName(), 'delimiter', $this->string()->after('sortColumn'));
        }

        /** @noinspection ClassConstantCanBeUsedInspection */
        $settingsColumn = (new Query())
            ->select(['settings'])
            ->from(['{{%sprout_settings}}'])
            ->where(['model' => 'barrelstrength\sproutbasereports\models\Settings'])
            ->scalar();

        $settings = json_decode($settingsColumn, true);

        // Add defaultExportDelimiter to shared settings
        $settings['defaultExportDelimiter'] = ',';

        /** @noinspection ClassConstantCanBeUsedInspection */
        $this->update('{{%sprout_settings}}', [
            'settings' => json_encode($settings)
        ], [
            'model' => 'barrelstrength\sproutbasereports\models\Settings'
        ]);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200512_000000_add_reports_delimiter_column cannot be reverted.\n";

        return false;
    }
}
