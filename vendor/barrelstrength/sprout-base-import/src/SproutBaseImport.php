<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbaseimport;

use barrelstrength\sproutbaseimport\controllers\ImportController;
use barrelstrength\sproutbaseimport\controllers\SeedController;
use barrelstrength\sproutbaseimport\controllers\SproutSeoController;
use barrelstrength\sproutbaseimport\controllers\WeedController;
use barrelstrength\sproutbase\base\BaseSproutTrait;
use barrelstrength\sproutbaseimport\web\twig\variables\SproutImportVariable;
use craft\web\twig\variables\CraftVariable;
use yii\base\Event;
use \yii\base\Module;
use craft\web\View;
use craft\events\RegisterTemplateRootsEvent;
use craft\helpers\ArrayHelper;
use craft\i18n\PhpMessageSource;
use Craft;

use barrelstrength\sproutbaseimport\services\App;

class SproutBaseImport extends Module
{
    use BaseSproutTrait;

    /**
     * @var string
     */
    public $handle;

    /**
     * @var App
     */
    public static $app;

    /**
     * Identify our plugin for BaseSproutTrait
     *
     * @var string
     */
    public static $pluginHandle = 'sprout-base-import';

    /**
     * @var string|null The translation category that this module translation messages should use. Defaults to the lowercase plugin handle.
     */
    public $t9nCategory;

    /**
     * @var string The language that the module messages were written in
     */
    public $sourceLanguage = 'en-US';

    /**
     * @todo - Copied from craft/base/plugin. Ask P&T if this is the best approach
     *
     * @inheritdoc
     */
    public function __construct($id, $parent = null, array $config = [])
    {
        // Set some things early in case there are any settings, and the settings model's
        // init() method needs to call Craft::t() or Plugin::getInstance().

        $this->handle = 'sprout-base-import';
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

    public function init()
    {
        self::$app = new App();

        Craft::setAlias('@sproutbaseimport', $this->getBasePath());

        Craft::setAlias('@sproutbaseimporticons', $this->getBasePath().'/web/assets/icons');

        // Setup Controllers
        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            $this->controllerNamespace = 'sproutbaseimport\\console\\controllers';
        } else {
            $this->controllerNamespace = 'sproutbaseimport\\controllers';

            $this->controllerMap = [
                'import' => ImportController::class,
                'seed' => SeedController::class,
                'weed' => WeedController::class,
                'redirects-tool' => SproutSeoController::class,
            ];
        }

        // Setup Template Roots
        Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function(RegisterTemplateRootsEvent $e) {
            $e->roots['sprout-base-import'] = $this->getBasePath().DIRECTORY_SEPARATOR.'templates';
        });

        // Setup Variables
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event) {
            $variable = $event->sender;
            $variable->set('sproutImport', SproutImportVariable::class);
        });

        parent::init();
    }
}
