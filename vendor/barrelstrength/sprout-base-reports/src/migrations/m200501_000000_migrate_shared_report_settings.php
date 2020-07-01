<?php /**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbasereports\migrations;

use barrelstrength\sproutbase\migrations\Install as SproutBaseInstall;
use barrelstrength\sproutbasereports\migrations\Install as SproutBaseReportsInstall;
use Craft;
use craft\db\Migration;
use craft\services\Plugins;

class m200501_000000_migrate_shared_report_settings extends Migration
{
    /**
     * @return bool
     */
    public function safeUp(): bool
    {
        // Make sure we have the sprout_settings table
        $migration = new SproutBaseInstall();
        ob_start();
        $migration->safeUp();
        ob_end_clean();

        $migration = new SproutBaseReportsInstall();
        ob_start();
        $migration->insertDefaultSettings();
        ob_end_clean();

        // Don't make the same config changes twice
        $projectConfig = Craft::$app->getProjectConfig();
        $pluginHandle = 'sprout-reports';
        $schemaVersion = $projectConfig->get(Plugins::CONFIG_PLUGINS_KEY.'.'.$pluginHandle.'.schemaVersion', true);
        if (version_compare($schemaVersion, '1.5.1', '>=')) {
            return true;
        }

        $sproutReportsSettings = Craft::$app->getProjectConfig()->get(Plugins::CONFIG_PLUGINS_KEY.'.'.$pluginHandle.'.settings');

        $newReportsSharedSettings = [
            'pluginNameOverride' => $sproutReportsSettings['pluginNameOverride'] ?? '',
            'defaultPageLength' => $sproutReportsSettings['defaultPageLength'] ?? 50
        ];

        /** @noinspection ClassConstantCanBeUsedInspection */
        $this->update('{{%sprout_settings}}', [
            'settings' => json_encode($newReportsSharedSettings)
        ], [
            'model' => 'barrelstrength\sproutbasereports\models\Settings'
        ]);

        Craft::$app->getProjectConfig()->remove(Plugins::CONFIG_PLUGINS_KEY.'.'.$pluginHandle.'.settings', 'Migrated Sprout Reports settings so shared sprout settings table.');

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200501_000000_migrate_shared_report_settings cannot be reverted.\n";

        return false;
    }
}
