<?php

namespace barrelstrength\sproutbasereports\migrations;

use barrelstrength\sproutbasereports\elements\Report;
use Craft;
use craft\db\Migration;
use craft\db\Query;
use craft\errors\ElementNotFoundException;
use Throwable;
use yii\base\Exception as BaseException;
use yii\db\Exception;

/**
 * m190305_000002_update_record_to_element_types migration.
 */
class m190305_000002_update_record_to_element_types extends Migration
{
    /**
     * @return bool
     * @throws ElementNotFoundException
     * @throws Exception
     * @throws Throwable
     * @throws BaseException
     */
    public function safeUp(): bool
    {
        $query = new Query();
        $db = Craft::$app->getDb();

        // Get all reports from the report table
        $reports = $query->select('*')
            ->from(['{{%sproutreports_reports}}'])
            ->all();

        if (empty($reports)) {
            return true;
        }

        foreach ($reports as $report) {

            // Only convert report record to element if it doesn't exist in the elements table
            $elementExist = $query->select('id')
                ->from('{{%elements}}')
                ->where(['id' => $report['id']])
                ->one();

            if ($elementExist) {
                continue;
            }

            // Delete report record then convert it to report element
            $db->createCommand()->delete('{{%sproutreports_reports}}',
                ['id' => $report['id']])->execute();

            $reportElement = new Report();
            unset($report['id']);
            $reportElement->setAttributes($report, false);

            Craft::$app->getElements()->saveElement($reportElement, false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190305_000002_update_record_to_element_types cannot be reverted.\n";

        return false;
    }
}
