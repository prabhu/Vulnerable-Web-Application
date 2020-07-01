<?php

namespace barrelstrength\sproutbaseemail\events;

use yii\base\Event;

/**
 * Class RegisterSendEmailEvent
 */
class RegisterSendEmailEvent extends Event
{
    public $message;

    public $mailer;

    public $variables = [];
}