<?php

namespace barrelstrength\sproutbasereports\models;

use barrelstrength\sproutbase\base\SharedPermissionsInterface;
use barrelstrength\sproutbase\base\SproutSettingsInterface;
use Craft;
use craft\base\Model;

/**
 *
 * @property array $sharedPermissions
 * @property array $settingsNavItems
 */
class Settings extends Model implements SproutSettingsInterface, SharedPermissionsInterface
{
    /**
     * @var string
     */
    public $pluginNameOverride = '';

    /**
     * @var string
     */
    public $defaultPageLength = 10;

    /**
     * @var string
     */
    public $defaultExportDelimiter = ',';

    /**
     * @inheritdoc
     */
    public function getSettingsNavItems(): array
    {
        return [
            'general' => [
                'label' => Craft::t('sprout-reports', 'General'),
                'url' => 'sprout-reports/settings/general',
                'selected' => 'general',
                'template' => 'sprout-base-reports/settings/general'
            ]
        ];
    }

    /**
     * Shared permissions they may be prefixed by another plugin. Before checking
     * these permissions the plugin name will be determined from the URL and appended.
     *
     * @return array
     * @example
     * /admin/sprout-reports/page => sproutReports-viewReports
     * /admin/sprout-forms/page => sproutForms-viewReports
     *
     */
    public function getSharedPermissions(): array
    {
        return [
            'viewReports',
            'editReports'
        ];
    }
}