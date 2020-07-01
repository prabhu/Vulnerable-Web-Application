<?php

namespace barrelstrength\sproutbaseemail\elements\db;

use barrelstrength\sproutbaseemail\services\NotificationEmails;
use Craft;
use craft\base\Element;
use craft\elements\db\ElementQuery;
use craft\errors\MissingComponentException;

class NotificationEmailQuery extends ElementQuery
{
    /**
     * @var string
     */
    public $viewContext;

    public $notificationEmailBaseUrl;

    /**
     * @return bool
     * @throws MissingComponentException
     */
    protected function beforePrepare(): bool
    {
        $this->joinElementTable('sproutemail_notificationemails');

        $this->query->select([
            'sproutemail_notificationemails.viewContext',
            'sproutemail_notificationemails.titleFormat',
            'sproutemail_notificationemails.emailTemplateId',
            'sproutemail_notificationemails.eventId',
            'sproutemail_notificationemails.settings',
            'sproutemail_notificationemails.sendRule',
            'sproutemail_notificationemails.subjectLine',
            'sproutemail_notificationemails.defaultBody',
            'sproutemail_notificationemails.sendMethod',
            'sproutemail_notificationemails.recipients',
            'sproutemail_notificationemails.cc',
            'sproutemail_notificationemails.bcc',
            'sproutemail_notificationemails.listSettings',
            'sproutemail_notificationemails.fromName',
            'sproutemail_notificationemails.fromEmail',
            'sproutemail_notificationemails.replyToEmail',
            'sproutemail_notificationemails.enableFileAttachments',
            'sproutemail_notificationemails.dateCreated',
            'sproutemail_notificationemails.dateUpdated',
            'sproutemail_notificationemails.fieldLayoutId'
        ]);

        if (!Craft::$app->getRequest()->getIsConsoleRequest() && Craft::$app->getSession()->getIsActive()) {

            $this->viewContext = Craft::$app->getSession()->get('sprout.notifications.viewContext');

            if ($this->viewContext !== null && $this->viewContext !== NotificationEmails::DEFAULT_VIEW_CONTEXT) {
                $this->query->where(['sproutemail_notificationemails.viewContext' => $this->viewContext]);
            }
        }

        return parent::beforePrepare();
    }

    /**
     * @inheritdoc
     */
    protected function statusCondition(string $status)
    {
        /**
         * To show disabled notification emails on integrated plugins
         */
        if ($this->viewContext !== NotificationEmails::DEFAULT_VIEW_CONTEXT) {
            return ['elements.enabled' => ['0', '1']];
        }

        switch ($status) {
            case Element::STATUS_ENABLED:
                return ['elements.enabled' => '1'];
            case Element::STATUS_DISABLED:
                return ['elements.enabled' => '0'];
            default:
                return false;
        }
    }
}