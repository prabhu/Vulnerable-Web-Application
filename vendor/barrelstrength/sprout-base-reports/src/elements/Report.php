<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbasereports\elements;

use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbasereports\base\DataSource;
use barrelstrength\sproutbasereports\elements\actions\DeleteReport;
use barrelstrength\sproutbasereports\elements\db\ReportQuery;
use barrelstrength\sproutbasereports\models\Settings;
use barrelstrength\sproutbasereports\records\Report as ReportRecord;
use barrelstrength\sproutbasereports\SproutBaseReports;
use Craft;
use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use craft\errors\MissingComponentException;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use craft\validators\HandleValidator;
use craft\validators\UniqueValidator;
use DateTime;
use Exception;
use InvalidArgumentException;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\InvalidConfigException;
use yii\web\NotFoundHttpException;

/**
 * SproutReports - Report element type
 *
 * @property string     $resultsError
 * @property $this      $startEndDate
 * @property DataSource $dataSource
 */
class Report extends Element
{
    const DELIMITER_COMMA = ',';
    const DELIMITER_SEMICOLON = ';';
    const DELIMITER_TAB = "\t";

    public $id;

    public $name;

    public $hasNameFormat;

    public $nameFormat;

    public $handle;

    public $description;

    public $allowHtml;

    public $sortOrder;

    public $sortColumn;

    public $delimiter;

    public $emailColumn;

    public $settings;

    public $dataSourceId;

    public $enabled;

    public $groupId;

    public $dateCreated;

    public $dateUpdated;

    public $results;

    /**
     * @var DateTime
     */
    public $startDate;

    /**
     * @var DateTime
     */
    public $endDate;

    /**
     * @var string Plugin Handle as defined in the Data Sources table
     */
    public $viewContext;

    /**
     * Returns the element type name.
     *
     * @return string
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-base-reports', 'Report');
    }

    /**
     * @return string
     */
    public static function pluralDisplayName(): string
    {
        return Craft::t('sprout-base-reports', 'Reports');
    }

    /**
     * @inheritDoc IElementType::hasStatuses()
     *
     * @return bool
     */
    public static function hasStatuses(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     *
     * @return ReportQuery The newly created [[RedirectQuery]] instance.
     */
    public static function find(): ElementQueryInterface
    {
        return new ReportQuery(static::class);
    }

    /**
     * Returns the attributes that can be shown/sorted by in table views.
     *
     * @param string|null $source
     *
     * @return array
     */
    public static function defineTableAttributes($source = null): array
    {
        // index or modal
        $context = Craft::$app->request->getParam('context');

        $tableAttributes['name'] = Craft::t('sprout-base-reports', 'Name');

        if ($context !== 'modal') {
            $tableAttributes['results'] = Craft::t('sprout-base-reports', 'View');
            $tableAttributes['download'] = Craft::t('sprout-base-reports', 'Export');
        }

        $tableAttributes['dataSourceId'] = Craft::t('sprout-base-reports', 'Data Source');

        return $tableAttributes;
    }

    public static function defineSearchableAttributes(): array
    {
        return ['name'];
    }

    protected static function defineDefaultTableAttributes(string $source): array
    {
        // index or modal
        $context = Craft::$app->request->getParam('context');

        $tableAttributes[] = 'name';

        if ($context !== 'modal') {
            $tableAttributes[] = 'results';
            $tableAttributes[] = 'download';
        }

        return $tableAttributes;
    }

    /**
     * @inheritdoc
     */
    protected static function defineSortOptions(): array
    {
        $attributes = [
            'name' => Craft::t('sprout-base-reports', 'Name'),
            'dataSourceId' => Craft::t('sprout-base-reports', 'Data Source')
        ];

        return $attributes;
    }

    /**
     * @inheritdoc
     */
    protected static function defineSources(string $context = null): array
    {
        $sources = [];
        $viewContext = null;

        // Just in case this gets run from the console for some reason,
        // make sure we don't try to access the request
        if (!Craft::$app->getRequest()->getIsConsoleRequest()) {
            // Allow the Element Selector Modal requests
            // to override the viewContext param set in the route
            // 'sources' is set as a config setting on the forms.elementSelect macro
            $viewContext = Craft::$app->getRequest()->getParam('sources');

            // Get the context from the URL, skip action requests
            $segment = Craft::$app->getRequest()->getSegment(1);
            if (!$viewContext && strpos($segment, 'sprout') === 0) {
                $viewContext = $segment;
            }

            // Default to sprout-reports context, necessary when filtering sources
            if (!$viewContext) {
                $viewContext = DataSource::DEFAULT_VIEW_CONTEXT;
            }
        }

        if ($viewContext !== 'mailingListModal') {
            $sources = [
                [
                    'key' => '*',
                    'label' => Craft::t('sprout-base-reports', 'All reports'),
                    'criteria' => [
                        'emailColumn' => ':empty:'
                    ]
                ]
            ];
        }

        if ($viewContext === DataSource::DEFAULT_VIEW_CONTEXT || $viewContext === 'mailingListModal') {

            $sources[] = [
                'key' => 'mailingList',
                'label' => Craft::t('sprout-base-reports', 'All mailing lists'),
                'data' => [
                    'readonly' => true
                ],
                'criteria' => [
                    'emailColumn' => ':notempty:'
                ]
            ];
        }

        if ($viewContext === DataSource::DEFAULT_VIEW_CONTEXT) {
            $groups = SproutBaseReports::$app->reportGroups->getReportGroups();

            if ($groups) {

                $sources[] = [
                    'heading' => Craft::t('sprout-base-reports', 'Group')
                ];

                foreach ($groups as $group) {
                    $key = 'group:'.$group->id;

                    $sources[] = [
                        'key' => $key,
                        'label' => Craft::t('sprout-base-reports', $group->name),
                        'data' => ['id' => $group->id],
                        'criteria' => ['groupId' => $group->id]
                    ];
                }
            }
        }

        return $sources;
    }

    /**
     * @inheritdoc
     */
    protected static function defineActions(string $source = null): array
    {
        $actions = [];

        $actions[] = DeleteReport::class;

        return $actions;
    }

    /**
     * @return string
     * @throws Throwable
     */
    public function __toString()
    {
        if ($this->hasNameFormat && $this->nameFormat) {
            try {
                return $this->processNameFormat();
            } catch (Exception $exception) {
                return Craft::t('sprout-base-reports', 'Invalid name format for report: '.$this->name);
            }
        }

        return (string)$this->name;
    }

    /**
     * Returns whether the Report can be .
     *
     * @return bool
     */
    public function getIsEditable(): bool
    {
        return true;
    }

    /**
     * Returns whether a Report can be deleted
     *
     * This is used when integrations like Sprout Forms or Sprout Lists want to
     * dynamically create a Report when something else happens, like when a Form
     * is created or when a list is created. This allows Sprout Forms to be sure
     * to have a report for each specific Form, or Sprout Lists to have a Mailing List
     * for each specific List.
     *
     * @return bool
     */
    public function canBeDeleted(): bool
    {
        return true;
    }

    /**
     * @param null   $currentBaseUrl
     * @param string $pluginHandle
     *
     * @return string|null
     * @throws MissingComponentException
     */
    public function getCpEditUrl($currentBaseUrl = null, $pluginHandle = 'sprout-reports')
    {
        // Data Source is used on the Results page, but we have a case where we need to get the value differently
        if (Craft::$app->getRequest()->getIsActionRequest()) {
            // criteria.pluginHandle is used on the Report Element index page
            $pluginHandle = Craft::$app->getSession()->get('sprout.reports.pluginHandle');
            $currentBaseUrl = Craft::$app->getSession()->get('sprout.reports.currentBaseUrl');
        }

        $permissions = SproutBase::$app->settings->getPluginPermissions(new Settings(), 'sprout-reports', $pluginHandle);

        if (!isset($permissions['sproutReports-viewReports']) || !Craft::$app->getUser()->checkPermission($permissions['sproutReports-viewReports'])) {
            return null;
        }

        return UrlHelper::cpUrl($currentBaseUrl.$this->dataSourceId.'/edit/'.$this->id);
    }

    /**
     * @param string $attribute
     *
     * @return string
     * @throws MissingComponentException
     */
    public function getTableAttributeHtml(string $attribute): string
    {
        $currentBaseUrl = Craft::$app->getSession()->get('sprout.reports.currentBaseUrl');

        if ($attribute === 'results') {
            $resultsUrl = UrlHelper::cpUrl($currentBaseUrl.'view/'.$this->id);

            return '<a href="'.$resultsUrl.'" class="btn small">'.Craft::t('sprout-base-reports', 'Run report').'</a>';
        }

        if ($attribute === 'download') {
            return '<a href="'.UrlHelper::actionUrl('sprout-base-reports/reports/export-report', [
                    'reportId' => $this->id
                ]).'" class="btn small">'.Craft::t('sprout-base-reports', 'Export').'</a>';
        }

        if ($attribute === 'dataSourceId') {

            $dataSource = SproutBaseReports::$app->dataSources->getDataSourceById($this->dataSourceId);

            if (!$dataSource) {
                $message = Craft::t('sprout-base-reports', 'Data Source not found: {dataSourceId}', [
                    'dataSourceId' => $attribute
                ]);

                return '<span class="error">'.$message.'</span>';
            }

            return $dataSource::displayName();
        }

        return parent::getTableAttributeHtml($attribute);
    }

    /**
     * @return DataSource|null
     */
    public function getDataSource()
    {
        $dataSource = SproutBaseReports::$app->dataSources->getDataSourceById($this->dataSourceId);

        if ($dataSource === null) {
            return null;
        }

        $dataSource->setReport($this);

        return $dataSource;
    }

    /**
     * @return string
     * @throws Throwable
     * @throws \yii\base\Exception
     */
    public function processNameFormat(): string
    {
        $dataSource = $this->getDataSource();

        if (!$dataSource) {
            throw new NotFoundHttpException('Data Source not found.');
        }

        $settingsArray = Json::decode($this->settings);

        $settings = $dataSource->prepSettings($settingsArray);

        return Craft::$app->getView()->renderObjectTemplate($this->nameFormat, $settings);
    }

    /**
     * @return mixed
     */
    public function getSettings()
    {
        $settings = $this->settings;

        if (is_string($this->settings)) {
            $settings = Json::decode($this->settings);
        }

        return $settings;
    }

    /**
     * Returns a user supplied setting if it exists or $default otherwise
     *
     * @param string     $name
     * @param null|mixed $default
     *
     * @return null
     */
    public function getSetting($name, $default = null)
    {
        $settings = $this->getSettings();

        return $settings[$name] ?? $default;
    }

    /**
     * @param array $results
     */
    public function setResults(array $results = [])
    {
        $this->results = $results;
    }

    /**
     * @param string $message
     */
    public function setResultsError($message)
    {
        $this->addError('results', $message);
    }

    /**
     * @param bool $isNew
     *
     * @throws InvalidArgumentException
     */
    public function afterSave(bool $isNew)
    {
        if (!$isNew) {
            $reportRecord = ReportRecord::findOne($this->id);

            if (!$reportRecord) {
                throw new InvalidArgumentException('Invalid Report ID: '.$this->id);
            }
        } else {
            $reportRecord = new ReportRecord();
            $reportRecord->id = $this->id;
        }

        $reportRecord->dataSourceId = $this->dataSourceId;
        $reportRecord->groupId = $this->groupId;
        $reportRecord->name = $this->name;
        $reportRecord->hasNameFormat = $this->hasNameFormat;
        $reportRecord->nameFormat = $this->nameFormat;
        $reportRecord->handle = $this->handle;
        $reportRecord->description = $this->description;
        $reportRecord->allowHtml = $this->allowHtml;
        $reportRecord->sortOrder = $this->sortOrder;
        $reportRecord->sortColumn = $this->sortColumn;
        $reportRecord->delimiter = $this->delimiter;
        $reportRecord->emailColumn = $this->emailColumn;
        $reportRecord->settings = $this->settings;
        $reportRecord->enabled = $this->enabled;
        $reportRecord->save(false);

        parent::afterSave($isNew);
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function getStartEndDate(): self
    {
        $dateRange = $this->getSetting('dateRange');

        if ($dateRange !== null && $dateRange == 'customRange') {
            $startDateSetting = $this->getSetting('startDate');
            $endDateSetting = $this->getSetting('endDate');
        } else {
            $startEndDate = SproutBaseReports::$app->reports->getStartEndDateRange($dateRange);

            $startDateSetting = $startEndDate['startDate'];
            $endDateSetting = $startEndDate['endDate'];
        }

        $this->startDate = SproutBaseReports::$app->reports->getUtcDateTime($startDateSetting);
        $this->endDate = SproutBaseReports::$app->reports->getUtcDateTime($endDateSetting);

        return $this;
    }

    public function getStartDate(): DateTime
    {
        return $this->startDate;
    }

    public function getEndDate(): DateTime
    {
        return $this->endDate;
    }

    /**
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws \yii\base\Exception
     */
    public function getEditorHtml(): string
    {
        $html = Craft::$app->getView()->renderTemplateMacro('_includes/forms', 'textField', [
            [
                'label' => Craft::t('sprout-base-reports', 'Name'),
                'siteId' => $this->siteId,
                'id' => 'name',
                'name' => 'name',
                'value' => $this->name,
                'errors' => $this->getErrors('name'),
                'first' => true,
                'autofocus' => true,
                'required' => true
            ]
        ]);

        if ($this->dataSource instanceof DataSource) {
            $settingsHtml = $this->dataSource->getSettingsHtml();
            if ($settingsHtml) {
                $html .= $settingsHtml;
            }
        }

        // Render the custom fields (we don't have any but it's good to call our parents)
        $html .= parent::getEditorHtml();

        return $html;
    }

    /**
     * @return array
     * @throws InvalidConfigException
     */
    protected function defineRules(): array
    {
        $rules = parent::defineRules();

        $rules[] = [['name', 'handle'], 'required'];
        $rules[] = [
            ['handle'],
            HandleValidator::class,
            'reservedWords' => [
                'id', 'dateCreated', 'dateUpdated', 'uid', 'title'
            ]
        ];
        $rules[] = [
            ['name', 'handle'],
            UniqueValidator::class,
            'targetClass' => ReportRecord::class
        ];

        return $rules;
    }
}
