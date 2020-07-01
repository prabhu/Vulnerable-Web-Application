<?php

namespace barrelstrength\sproutbaseemail\models;

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
     * @var bool
     */
    public $enableNotificationEmails = true;

    /**
     * @var bool
     */
    public $enableCampaignEmails = false;

    /**
     * @var bool
     */
    public $enableSentEmails = false;

    /**
     * @var null
     */
    public $emailTemplateId;

    /**
     * @var int
     */
    public $enablePerEmailEmailTemplateIdOverride = 0;

    /**
     * @var int
     */
    public $sentEmailsLimit;

    /**
     * @var int
     */
    public $cleanupProbability = 1000;

    /**
     * @var bool
     */
//    public $showReportsTab = true;

    /**
     * @inheritdoc
     */
    public function getSettingsNavItems(): array
    {
        return [
            'general' => [
                'label' => Craft::t('sprout-base-email', 'General'),
                'url' => 'sprout-email/settings/general',
                'selected' => 'general',
                'template' => 'sprout-base-email/settings/general'
            ],
            'mailers' => [
                'label' => Craft::t('sprout-base-email', 'Mailers'),
                'url' => 'sprout-email/settings/mailers',
                'selected' => 'mailers',
                'template' => 'sprout-base-email/settings/mailers'
            ],
//            'campaigntypes' => [
//                'label' => Craft::t('sprout-email', 'Campaigns'),
//                'url' => 'sprout-email/settings/campaigntypes',
//                'selected' => 'campaigntypes',
//                'template' => 'sprout-base-email/settings/campaigntypes',
//                'settingsForm' => false
//            ],
            'notifications' => [
                'label' => Craft::t('sprout-base-email', 'Notifications'),
                'url' => 'sprout-email/settings/notifications',
                'selected' => 'notifications',
                'template' => 'sprout-base-email/settings/notifications'
            ],
            'sentemails' => [
                'label' => Craft::t('sprout-base-email', 'Sent Emails'),
                'url' => 'sprout-email/settings/sentemails',
                'selected' => 'sentemails',
                'template' => 'sprout-base-email/settings/sentemails'
            ],
            'integrationsHeading' => [
                'heading' => Craft::t('sprout-base-email', 'Integrations'),
            ],
            'mailing-lists' => [
                'label' => Craft::t('sprout-base-email', 'Mailing Lists'),
                'url' => 'sprout-email/settings/mailing-lists',
                'selected' => 'mailing-lists',
                'template' => 'sprout-base-email/settings/mailing-lists'
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
            'viewNotifications',
            'editNotifications'
        ];
    }
}