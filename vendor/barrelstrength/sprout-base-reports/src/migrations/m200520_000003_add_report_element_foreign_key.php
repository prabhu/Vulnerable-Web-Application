<?php

namespace barrelstrength\sproutbasereports\migrations;

use Craft;
use craft\db\Migration;
use craft\db\Query;
use Exception;

class m200520_000003_add_report_element_foreign_key extends Migration
{
    /**
     * @return bool
     */
    public function safeUp(): bool
    {
        $this->cleanUpOrphanedReports();

        // Updates foreign key if it does not exist. Try catch avoid errors if it exist
        try {
            $this->addForeignKey(null, '{{%sproutreports_reports}}',
                ['id'], '{{%elements}}', ['id'], 'CASCADE');
        } catch (Exception $e) {
            Craft::info('Report Element Foreign Key already exists', __METHOD__);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200520_000003_add_report_element_foreign_key cannot be reverted.\n";

        return false;
    }

    /** @noinspection ClassConstantCanBeUsedInspection */
    protected function cleanUpOrphanedReports()
    {
        $reportTableReportIds = (new Query())
            ->select('reports.id')
            ->from('{{%sproutreports_reports}} AS reports')
            ->column();

        $elementTableReportIds = (new Query())
            ->select('elements.id')
            ->from('{{%elements}} AS elements')
            ->where([
                'type' => 'barrelstrength\sproutbasereports\elements\Report'
            ])
            ->column();

        // All Report IDs that are NOT Element IDs
        // all IDs in array1 that aren't present in array2
        $orphanedReportIds = array_diff($reportTableReportIds, $elementTableReportIds);

        // Remove any orphaned Reports from the Reports table
        foreach ($orphanedReportIds as $orphanedReportId) {
            $this->delete('{{%sproutreports_reports}}', [
                'id' => $orphanedReportId
            ]);
        }
    }
}
