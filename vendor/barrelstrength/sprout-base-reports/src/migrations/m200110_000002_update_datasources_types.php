<?php /** @noinspection ClassConstantCanBeUsedInspection */

/** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutbasereports\migrations;

use craft\db\Migration;

/**
 * m200110_000002_update_datasources_types migration.
 */
class m200110_000002_update_datasources_types extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $dataSourceClasses = [
            0 => [
                'oldType' => 'barrelstrength\sproutreports\datasources\CustomQuery',
                'newType' => 'barrelstrength\sproutbasereports\datasources\CustomQuery'
            ],
            1 => [
                'oldType' => 'barrelstrength\sproutreports\datasources\CustomTwigTemplate',
                'newType' => 'barrelstrength\sproutbasereports\datasources\CustomTwigTemplate'
            ],
            2 => [
                'oldType' => 'barrelstrength\sproutreportsusers\integrations\sproutreports\datasources\Users',
                'newType' => 'barrelstrength\sproutbasereports\datasources\Users'
            ]
        ];

        foreach ($dataSourceClasses as $dataSourceClass) {
            $this->update('{{%sproutreports_datasources}}', [
                'type' => $dataSourceClass['newType']
            ], ['type' => $dataSourceClass['oldType']], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200110_000002_update_datasources_types cannot be reverted.\n";

        return false;
    }
}
