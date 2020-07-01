<?php

namespace barrelstrength\sproutbaseemail\events;

use barrelstrength\sproutbaseemail\elements\NotificationEmail;
use yii\base\Event;

class SendNotificationEmailEvent extends Event
{
    public $event;

    /**
     * @var NotificationEmail
     */
    public $notificationEmail;
}
