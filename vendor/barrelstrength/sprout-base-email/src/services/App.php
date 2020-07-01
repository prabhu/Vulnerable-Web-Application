<?php

namespace barrelstrength\sproutbaseemail\services;

use craft\base\Component;

class App extends Component
{
    /**
     * @var NotificationEmails
     */
    public $notifications;

    /**
     * @var NotificationEmailEvents
     */
    public $notificationEvents;

    /**
     * @var Mailers
     */
    public $mailers;

    /**
     * @var EmailTemplates
     */
    public $emailTemplates;

    /**
     * @inheritdoc
     */
    public function init()
    {
        // Sprout Email
        $this->emailTemplates = new EmailTemplates();
        $this->mailers = new Mailers();
        $this->notifications = new NotificationEmails();
        $this->notificationEvents = new NotificationEmailEvents();
    }
}
