<?php

namespace barrelstrength\sproutbaseemail\web\assets\email;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * Class EmailAsset
 *
 * @package barrelstrength\sproutemail\web\assets\email
 */
class EmailAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbaseemail/web/assets/email/dist';

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/notification.js',
            'js/sprout-modal.js'
        ];

        $this->css = [
            'css/sproutemail.css',
            'css/modal.css',
            'css/charts-explorer.css',
        ];

        parent::init();
    }
}