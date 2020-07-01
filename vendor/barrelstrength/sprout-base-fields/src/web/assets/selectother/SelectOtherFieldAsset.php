<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbasefields\web\assets\selectother;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class SelectOtherFieldAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbasefields/web/assets/selectother/dist';

        $this->depends = [
            CpAsset::class
        ];

        $this->js = [
            'js/selectotherfield.js'
        ];

        $this->css = [
            'css/select-other.css'
        ];

        parent::init();
    }
}