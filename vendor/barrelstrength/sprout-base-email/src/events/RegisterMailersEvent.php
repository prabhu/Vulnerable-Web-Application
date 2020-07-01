<?php

namespace barrelstrength\sproutbaseemail\events;

use yii\base\Event;

class RegisterMailersEvent extends Event
{
    public $mailers = [];
}