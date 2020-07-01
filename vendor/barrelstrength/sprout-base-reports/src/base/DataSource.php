<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbasereports\base;

use barrelstrength\sproutbasereports\elements\Report;
use barrelstrength\sproutbasereports\SproutBaseReports;
use Craft;
use craft\base\Plugin;
use craft\base\SavableComponent;
use craft\helpers\UrlHelper;

/**
 * Class DataSource
 *
 * @package Craft
 *
 * @property string $description
 * @property int    $reportCount
 * @property string $viewContextLabel
 * @property string $defaultEmailColumn
 * @property bool   $defaultAllowHtml
 */
abstract class DataSource extends SavableComponent implements DataSourceInterface
{
    use DataSourceTrait;

    const DEFAULT_VIEW_CONTEXT = 'sprout-reports';

    /**
     * This value indicates whether a Report is being generated for Export
     *
     * This is set to true when exporting data, so a report can do something
     * like show HTML in the CP report view and exclude that HTML when exporting.
     *
     * @var bool
     */
    public $isExport = false;

    /**
     * DataSource constructor.
     */
    public function init()
    {
        parent::init();

        // Get plugin class
        $pluginHandle = Craft::$app->getPlugins()->getPluginHandleByClass(get_class($this));

        if ($pluginHandle !== null) {
            $this->plugin = Craft::$app->getPlugins()->getPlugin($pluginHandle);
        } else {
            $this->plugin = Craft::$app->getPlugins()->getPlugin('sprout-reports');
        }
    }


    /**
     * Returns the viewContext for a given Data Source
     *
     * @return string
     */
    public function getViewContext(): string
    {
        return self::DEFAULT_VIEW_CONTEXT;
    }

    /**
     * Returns the viewContext Label used when grouping common report types in the sidebar Sources
     *
     * @return string
     */
    public function getViewContextLabel(): string
    {
        return 'Custom';
    }

    /**
     * @return bool
     */
    public function isEmailColumnEditable(): bool
    {
        return true;
    }

    /**
     * getDefaultEmailColumn is only used when isEmailColumnEditable is set to false.
     *
     * @return string
     */
    public function getDefaultEmailColumn(): string
    {
        return '';
    }

    /**
     * Returns an instance of the plugin that created this Data Source
     *
     * @return Plugin|null
     */
    final public function getPlugin()
    {
        return $this->plugin;
    }

    /**
     * Set a Report on our data source.
     *
     * @param Report|null $report
     */
    public function setReport(Report $report = null)
    {
        if (null === $report) {
            $report = new Report();
        }

        $this->report = $report;
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getDefaultLabels(Report $report, array $settings = []): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getResults(Report $report, array $settings = []): array
    {
        return [];
    }

    /**
     * Give a Data Source a chance to prepare settings before they are processed by the Dynamic Name field
     *
     * @param array $settings
     *
     * @return null
     */
    public function prepSettings(array $settings)
    {
        return $settings;
    }

    /**
     * Validate the data sources settings
     *
     * @param array $settings
     * @param array $errors
     *
     * @return bool
     */
    public function validateSettings(array $settings = [], array &$errors = []): bool
    {
        return true;
    }

    /**
     * Returns the CP Edit URL for the given data source used to create Reports
     *
     * @param null $append
     *
     * @return string
     */
    public function getUrl($append = null): string
    {
        $appendedUrl = ltrim($append, '/');

        return UrlHelper::cpUrl($this->baseUrl.$appendedUrl);
    }

    /**
     * Allow a user to toggle the Allow Html setting.
     *
     * @return bool
     */
    public function isAllowHtmlEditable(): bool
    {
        return false;
    }

    /**
     * Define the default value for the Allow HTML setting. Setting Allow HTML
     * to true enables a report to output HTML on the Results page.
     *
     * @return bool
     */
    public function getDefaultAllowHtml(): bool
    {
        return false;
    }

    /**
     * Returns the total count of reports created based on the given data source
     *
     * @return int
     */
    final public function getReportCount(): int
    {
        return SproutBaseReports::$app->reports->getCountByDataSourceId($this->id);
    }
}
