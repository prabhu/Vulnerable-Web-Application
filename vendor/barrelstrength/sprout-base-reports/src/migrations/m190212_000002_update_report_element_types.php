<?php /** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutbasereports\migrations;

use craft\db\Migration;

/**
 * m190212_000002_update_report_element_types migration.
 */
class m190212_000002_update_report_element_types extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $elements = [
            0 => [
                'oldType' => 'barrelstrength\sproutbase\app\reports\elements\Report',
                'newType' => 'barrelstrength\sproutbasereports\elements\Report'
            ]
        ];

        foreach ($elements as $element) {
            $this->update('{{%elements}}', [
                'type' => $element['newType']
            ], ['type' => $element['oldType']], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190212_000002_update_report_element_types cannot be reverted.\n";

        return false;
    }
}
