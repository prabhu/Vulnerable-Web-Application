<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbasefields\web\assets\address;

use barrelstrength\sproutbase\web\assets\cp\CpAsset;
use craft\web\AssetBundle;

class AddressFieldAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbasefields/web/assets/address/dist';

        $this->depends = [
            CpAsset::class
        ];

        $this->css = [
            'css/addressfield.css'
        ];

        $this->js = [
            'js/addressfield.js'
        ];

        parent::init();
    }
}