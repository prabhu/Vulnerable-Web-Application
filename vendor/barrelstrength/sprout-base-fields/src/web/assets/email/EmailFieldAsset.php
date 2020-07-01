<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbasefields\web\assets\email;

use barrelstrength\sproutbase\web\assets\cp\CpAsset;
use craft\web\AssetBundle;

class EmailFieldAsset extends AssetBundle
{
    public function init()
    {
        // define the path that your publishable resources live
        $this->sourcePath = '@sproutbasefields/web/assets/email/dist';

        // define the dependencies
        $this->depends = [
            CpAsset::class
        ];

        $this->css = [
            'css/emailfield.css',
        ];

        $this->js = [
            'js/emailfield.js',
        ];

        parent::init();
    }
}