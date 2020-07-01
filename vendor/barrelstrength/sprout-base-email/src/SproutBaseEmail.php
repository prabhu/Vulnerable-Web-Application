<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbaseemail;

use barrelstrength\sproutbase\base\BaseSproutTrait;
use barrelstrength\sproutbaseemail\controllers\MailersController;
use barrelstrength\sproutbaseemail\controllers\NotificationsController;
use barrelstrength\sproutbaseemail\emailtemplates\BasicTemplates;
use barrelstrength\sproutbaseemail\events\RegisterMailersEvent;
use barrelstrength\sproutbaseemail\mailers\DefaultMailer;
use barrelstrength\sproutbaseemail\services\App;
use barrelstrength\sproutbaseemail\services\EmailTemplates;
use barrelstrength\sproutbaseemail\services\Mailers;
use barrelstrength\sproutbaseemail\web\twig\variables\SproutEmailVariable;
use Craft;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\helpers\ArrayHelper;
use craft\i18n\PhpMessageSource;
use craft\web\Application;
use craft\web\twig\variables\CraftVariable;
use craft\web\View;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\base\Module;

class SproutBaseEmail extends Module
{
    use BaseSproutTrait;

    /**
     * @var App
     */
    public static $app;

    /**
     * Identify our plugin for BaseSproutTrait
     *
     * @var string
     */
    public static $pluginHandle = 'sprout-base-email';

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

        $this->handle = 'sprout-base-email';
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

        Craft::setAlias('@sproutbaseemail', $this->getBasePath());
        Craft::setAlias('@sproutbaseemaillib', dirname(__DIR__).'/lib');

        // Setup Controllers
        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            $this->controllerNamespace = 'sproutbaseemail\\console\\controllers';
        } else {
            $this->controllerNamespace = 'sproutbaseemail\\controllers';

            $this->controllerMap = [
                'mailers' => MailersController::class,
                'notifications' => NotificationsController::class,
            ];
        }

        // Setup Template Roots
        Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function(RegisterTemplateRootsEvent $e) {
            $e->roots['sprout-base-email'] = $this->getBasePath().DIRECTORY_SEPARATOR.'templates';
        });

        // Setup Variables
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, static function(Event $event) {
            $event->sender->set('sproutEmail', SproutEmailVariable::class);
        });

        // Register Sprout Email Events
        Event::on(Application::class, Application::EVENT_INIT, static function() {
            SproutBaseEmail::$app->notificationEvents->registerNotificationEmailEventHandlers();
        });

        // Register Sprout Email Mailers
        Event::on(Mailers::class, Mailers::EVENT_REGISTER_MAILER_TYPES, static function(RegisterMailersEvent $event) {
            $event->mailers[] = new DefaultMailer();
        });

        // Register Sprout Email Templates
        Event::on(EmailTemplates::class, EmailTemplates::EVENT_REGISTER_EMAIL_TEMPLATES, static function(RegisterComponentTypesEvent $event) {
            $event->types[] = BasicTemplates::class;
        });
    }
}
