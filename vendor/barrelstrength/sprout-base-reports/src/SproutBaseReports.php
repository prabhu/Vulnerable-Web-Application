<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbasereports;

use barrelstrength\sproutbasereports\controllers\ReportsController;
use barrelstrength\sproutbasereports\datasources\CustomQuery;
use barrelstrength\sproutbasereports\datasources\CustomTwigTemplate;
use barrelstrength\sproutbasereports\datasources\Users;
use barrelstrength\sproutbasereports\services\App;
use barrelstrength\sproutbasereports\services\DataSources;
use Craft;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\helpers\ArrayHelper;
use craft\i18n\PhpMessageSource;
use craft\web\View;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\base\Module;

class SproutBaseReports extends Module
{
    /**
     * @var App
     */
    public static $app;

    /**
     * @var string
     */
    public $handle;

    /**
     * @var string|null The translation category that this module translation messages should use. Defaults to the lowercase plugin handle.
     */
    public $t9nCategory;

    /**
     * @var string The language that the module messages were written in
     */
    public $sourceLanguage = 'en-US';

    /**
     * @inheritdoc
     */
    public function __construct($id, $parent = null, array $config = [])
    {
        // Set some things early in case there are any settings, and the settings model's
        // init() method needs to call Craft::t() or Plugin::getInstance().

        $this->handle = 'sprout-base-reports';
        $this->t9nCategory = ArrayHelper::remove($config, 't9nCategory', $this->t9nCategory ?? strtolower($this->handle));
        $this->sourceLanguage = ArrayHelper::remove($config, 'sourceLanguage', $this->sourceLanguage);

        if (($basePath = ArrayHelper::remove($config, 'basePath')) !== null) {
            $this->setBasePath($basePath);
        }

        // Translation category
        $i18n = Craft::$app->getI18n();
        /** @noinspection UnSafeIsSetOverArrayInspection */
        if (!isset($i18n->translations[$this->t9nCategory]) && !isset($i18n->translations[$this->t9nCategory.'*'])) {
            $i18n->translations[$this->t9nCategory] = [
                'class' => PhpMessageSource::class,
                'sourceLanguage' => $this->sourceLanguage,
                'basePath' => $this->getBasePath().DIRECTORY_SEPARATOR.'translations',
                'allowOverrides' => true,
            ];
        }

        // Set this as the global instance of this plugin class
        static::setInstance($this);

        parent::__construct($id, $parent, $config);
    }

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        $this->setComponents([
            'app' => App::class
        ]);

        self::$app = $this->get('app');

        Craft::setAlias('@sproutbasereports', $this->getBasePath());
        Craft::setAlias('@sproutbasereportslib', dirname(__DIR__).'/lib');

        // Setup Controllers
        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            $this->controllerNamespace = 'sproutbasereports\\console\\controllers';
        } else {
            $this->controllerNamespace = 'sproutbasereports\\controllers';

            $this->controllerMap = [
                'reports' => ReportsController::class,
            ];
        }

        // Setup Template Roots
        Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function(RegisterTemplateRootsEvent $e) {
            $e->roots['sprout-base-reports'] = $this->getBasePath().DIRECTORY_SEPARATOR.'templates';
        });

        Event::on(DataSources::class, DataSources::EVENT_REGISTER_DATA_SOURCES, static function(RegisterComponentTypesEvent $event) {
            $event->types[] = CustomQuery::class;
            $event->types[] = CustomTwigTemplate::class;
            $event->types[] = Users::class;
        });
    }
}
