<?php

namespace barrelstrength\sproutbaseemail\elements;

use barrelstrength\sproutbaseemail\base\EmailElement;
use barrelstrength\sproutbaseemail\base\SenderTrait;
use barrelstrength\sproutbaseemail\elements\actions\DeleteNotification;
use barrelstrength\sproutbaseemail\elements\db\NotificationEmailQuery;
use barrelstrength\sproutbaseemail\records\NotificationEmail as NotificationEmailRecord;
use barrelstrength\sproutbaseemail\SproutBaseEmail;
use barrelstrength\sproutbaseemail\web\assets\email\EmailAsset;
use Craft;
use craft\behaviors\FieldLayoutBehavior;
use craft\elements\db\ElementQueryInterface;
use craft\errors\MissingComponentException;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\MultipleValidationWithAnd;
use Egulias\EmailValidator\Validation\RFCValidation;
use InvalidArgumentException;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;
use yii\base\InvalidConfigException;

/**
 * Class NotificationEmail
 *
 * @mixin FieldLayoutBehavior
 *
 * @property mixed $options
 * @property array $sendRuleOptions
 * @property array $statuses
 */
class NotificationEmail extends EmailElement
{
    use SenderTrait;

    /**
     * The Email Notification Field Layout ID
     *
     * @var int
     */
    public $fieldLayoutId;

    /**
     * The Title Format of your Title.
     *
     * @var string
     */
    public $titleFormat;

    /**
     * The handle of the plugin where a notification event exists
     *
     * @var string
     */
    public $viewContext;

    /**
     * The Email Template integration handle or folder path of the email templates that should be used when rendering this Notification Email.
     *
     * @var string
     */
    public $emailTemplateId;

    /**
     * The qualified namespace of the Email Notification Event
     *
     * @var string
     */
    public $eventId;

    /**
     * Any options that have been set for your Event. Stored as JSON.
     *
     * @var string
     */
    public $settings;

    /**
     * The default email message.
     *
     * This field is only visible when no Email Notification Field Layout exists. Once a Field Layout exists, this field will no longer appear in the interface.
     *
     * @var string
     */
    public $defaultBody;

    /**
     * Statement that gets evaluated to true/false to determine this event will be fired
     *
     * @var boolean
     */
    public $sendRule;

    /**
     * @var string
     */
    public $sendMethod;

    /**
     * @deprecated - This property is necessary for the migration
     * m180314_161540_craft2_to_craft3. Remove this property once
     * we update minVersionRequired and delete that migration.
     */
    public $pluginHandle;

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-base-email', 'Notification Email');
    }

    /**
     * @return string
     */
    public static function pluralDisplayName(): string
    {
        return Craft::t('sprout-base-email', 'Notification Emails');
    }

    /**
     * @inheritdoc
     */
    public static function refHandle()
    {
        return 'notificationEmail';
    }

    /**
     * @inheritdoc
     */
    public static function hasContent(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasTitles(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasUris(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasStatuses(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function find(): ElementQueryInterface
    {
        return new NotificationEmailQuery(static::class);
    }

    /**
     * @@inheritdoc
     *
     * @throws InvalidConfigException
     */
    public static function indexHtml(
        ElementQueryInterface $elementQuery, /** @noinspection PhpOptionalBeforeRequiredParametersInspection */
        array $disabledElementIds = null, array $viewState, /** @noinspection PhpOptionalBeforeRequiredParametersInspection */
        string $sourceKey = null, /** @noinspection PhpOptionalBeforeRequiredParametersInspection */
        string $context = null, bool $includeContainer, bool $showCheckboxes
    ): string {
        $html = parent::indexHtml($elementQuery, $disabledElementIds, $viewState, $sourceKey, $context, $includeContainer, true);

        Craft::$app->getView()->registerAssetBundle(EmailAsset::class);
        Craft::$app->getView()->registerJs('new SproutModal();');
        SproutBaseEmail::$app->mailers->includeMailerModalResources();

        return $html;
    }

    /**
     * @inheritdoc
     */
    protected static function defineSources(string $context = null): array
    {
        $sources = [
            [
                'key' => '*',
                'label' => Craft::t('sprout-base-email', 'All notifications')
            ]
        ];

        return $sources;
    }

    /**
     * @inheritdoc
     */
    protected static function defineTableAttributes(): array
    {
        $attributes = [
            'title' => ['label' => Craft::t('sprout-base-email', 'Title')],
            'subjectLine' => ['label' => Craft::t('sprout-base-email', 'Subject Line')],
            'dateCreated' => ['label' => Craft::t('sprout-base-email', 'Date Created')],
            'send' => ['label' => Craft::t('sprout-base-email', 'Send')],
            'preview' => ['label' => Craft::t('sprout-base-email', 'Preview'), 'icon' => 'view']
        ];

        return $attributes;
    }

    /**
     * @inheritdoc
     */
    protected static function defineSortOptions(): array
    {
        return [
            'title' => Craft::t('sprout-base-email', 'Title'),
            'subjectLine' => Craft::t('sprout-base-email', 'Subject Line'),
            'elements.dateCreated' => Craft::t('sprout-base-email', 'Date Created'),
            'elements.dateUpdated' => Craft::t('sprout-base-email', 'Date Updated'),
        ];
    }

    /**
     * @inheritdoc
     */
    protected static function defineActions(string $source = null): array
    {
        $actions = [];

        $actions[] = DeleteNotification::class;

        return $actions;
    }

    /**
     * @inheritdoc
     */
    public function getStatuses(): array
    {
        return [
            self::ENABLED => Craft::t('sprout-base-email', 'enabled'),
            self::PENDING => Craft::t('sprout-base-email', 'pending'),
            self::DISABLED => Craft::t('sprout-base-email', 'disabled')
        ];
    }

    /**
     * @return string|null
     * @throws MissingComponentException
     */
    public function getCpEditUrl()
    {
        $notificationEmailBaseUrl = Craft::$app->getSession()->get('sprout.notifications.notificationEmailBaseUrl');

        return UrlHelper::cpUrl(
            $notificationEmailBaseUrl.'edit/'.$this->id
        );
    }

    /**
     * @param string $attribute
     *
     * @return string
     * @throws Exception
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getTableAttributeHtml(string $attribute): string
    {
        if ($attribute === 'send') {
            return Craft::$app->getView()->renderTemplate('sprout-base-email/_components/elementindex/NotificationEmail/prepare-link', [
                'notification' => $this,
                'mailer' => $this->getMailer()
            ]);
        }

        if ($attribute === 'preview') {
            $shareUrl = null;

            if ($this->id && $this->getUrl()) {
                $shareUrl = UrlHelper::actionUrl('sprout-base-email/notifications/share-notification-email', [
                    'notificationId' => $this->id,
                ]);
            }

            $notificationEmailBaseUrl = Craft::$app->getSession()->get('sprout.notifications.notificationEmailBaseUrl');

            return Craft::$app->getView()->renderTemplate('sprout-base-email/_components/elementindex/NotificationEmail/preview-links', [
                'email' => $this,
                'notificationEmailBaseUrl' => $notificationEmailBaseUrl,
                'shareUrl' => $shareUrl,
                'type' => $attribute
            ]);
        }

        return parent::getTableAttributeHtml($attribute);
    }

    /**
     * @inheritdoc
     *
     * @throws InvalidConfigException
     */
    public function getFieldLayout()
    {
        $behaviors = $this->getBehaviors();

        /**
         * @var FieldLayoutBehavior $fieldLayout
         */
        $fieldLayout = $behaviors['fieldLayout'];

        return $fieldLayout->getFieldLayout();
    }

    /**
     * @param bool $isNew
     */
    public function afterSave(bool $isNew)
    {
        /**
         * @var $notificationEmailRecord NotificationEmail
         */
        $notificationEmailRecord = null;

        // Get the entry record
        if (!$isNew) {
            $notificationEmailRecord = NotificationEmailRecord::findOne($this->id);

            if (!$notificationEmailRecord) {
                throw new InvalidArgumentException('Invalid campaign email ID: '.$this->id);
            }
        } else {
            $notificationEmailRecord = new NotificationEmailRecord();
            $notificationEmailRecord->id = $this->id;
        }

        $notificationEmailRecord->viewContext = $this->viewContext;
        $notificationEmailRecord->titleFormat = $this->titleFormat;
        $notificationEmailRecord->emailTemplateId = $this->emailTemplateId;
        $notificationEmailRecord->eventId = $this->eventId;
        $notificationEmailRecord->sendRule = $this->sendRule;
        $notificationEmailRecord->settings = $this->settings;
        $notificationEmailRecord->subjectLine = $this->subjectLine;
        $notificationEmailRecord->defaultBody = $this->defaultBody;
        $notificationEmailRecord->fieldLayoutId = $this->fieldLayoutId;
        $notificationEmailRecord->fromName = $this->fromName;
        $notificationEmailRecord->fromEmail = $this->fromEmail;
        $notificationEmailRecord->replyToEmail = $this->replyToEmail;
        $notificationEmailRecord->sendMethod = $this->sendMethod;
        $notificationEmailRecord->enableFileAttachments = $this->enableFileAttachments;
        $notificationEmailRecord->recipients = $this->recipients;
        $notificationEmailRecord->cc = $this->cc;
        $notificationEmailRecord->bcc = $this->bcc;
        $notificationEmailRecord->listSettings = $this->listSettings;
        $notificationEmailRecord->dateCreated = $this->dateCreated;
        $notificationEmailRecord->dateUpdated = $this->dateUpdated;

        $notificationEmailRecord->save(false);

        // Update the entry's descendants, who may be using this entry's URI in their own URIs
        Craft::$app->getElements()->updateElementSlugAndUri($this);

        parent::afterSave($isNew);
    }

    /**
     * @@inheritdoc
     *
     * @throws Exception
     */
    public function getUriFormat()
    {
        if ($this->viewContext == null) {
            throw new Exception('Invalid integration. No viewContext specified');
        }

        // @TODO - need to update to base URL
        return $this->viewContext.'/{slug}';
    }

    /**
     * @inheritdoc
     *
     * @throws Exception
     */
    public function getUrl()
    {
        if ($this->uri !== null) {
            return UrlHelper::siteUrl($this->uri);
        }

        return null;
    }

    /**
     * @param $attribute
     *
     * @return bool
     * @throws Throwable
     */
    public function emailList($attribute): bool
    {
        $recipients = $this->{$attribute};
        $validator = new EmailValidator();
        $multipleValidations = new MultipleValidationWithAnd([
            new RFCValidation()
        ]);

        // Add any On The Fly Recipients to our List
        if (!empty($recipients)) {
            $recipientArray = explode(',', trim($recipients));

            foreach ($recipientArray as $recipient) {
                // Let the user use shorthand syntax and don't validate it
                if (strpos($recipient, '{') !== false) {
                    continue;
                }
                // Validate actual emails
                if (!$validator->isValid(trim($recipient), $multipleValidations)) {
                    $this->addError($attribute, Craft::t('sprout-base-email',
                        $recipient.' email is not valid.'));
                }
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'fieldLayout' => [
                'class' => FieldLayoutBehavior::class,
                'elementType' => self::class
            ],
        ]);
    }

    /**
     * Returns a json-decoded array of options
     *
     * @return mixed
     */
    public function getOptions()
    {
        return Json::decode($this->settings);
    }

    /**
     * @inheritdoc
     */
    public function getEmailTemplateId(): string
    {
        return $this->emailTemplateId ?? '';
    }

    public function isReady(): bool
    {
        return ($this->getStatus() == static::ENABLED);
    }

    /**
     * @return array
     */
    final public function getSendRuleOptions(): array
    {
        $options = [
            [
                'label' => Craft::t('sprout-base-email', 'Always'),
                'value' => '*'
            ]
        ];

        $customSendRule = $this->sendRule;

        $options[] = [
            'optgroup' => Craft::t('sprout-base-email', 'Custom Rule')
        ];

        if ($customSendRule != '*' && $customSendRule) {
            $options[] = [
                'label' => $customSendRule,
                'value' => $customSendRule
            ];
        }

        $options[] = [
            'label' => Craft::t('sprout-base-email', 'Add Custom'),
            'value' => 'custom'
        ];

        return $options;
    }

    /**
     * @return bool
     * @throws Throwable
     */
    public function sendRuleIsTrue(): bool
    {
        // Default setting: Always = *
        if ($this->sendRule === '*') {
            return true;
        }

        // Custom Send Rule
        try {
            $resultTemplate = Craft::$app->view->renderObjectTemplate($this->sendRule, $this->getEventObject());
            $value = trim($resultTemplate);
            if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
                return true;
            }
        } catch (\Exception $e) {
            Craft::error($e->getMessage(), __METHOD__);
        }

        return false;
    }

    /**
     * @return array
     * @throws InvalidConfigException
     */
    protected function defineRules(): array
    {
        $rules = parent::defineRules();

        $rules[] = [['subjectLine', 'fromName', 'fromEmail'], 'required'];
        $rules[] = [['fromName', 'fromEmail', 'replyToEmail'], 'default', 'value' => ''];
        $rules[] = ['recipients', 'emailList'];
        $rules[] = ['cc', 'emailList'];
        $rules[] = ['bcc', 'emailList'];

        return $rules;
    }
}
