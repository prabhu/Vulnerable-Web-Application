<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbasereports\web\assets\datatables;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class DataTablesAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = '@sproutbasereportslib/datatables';

        $this->depends = [
            CpAsset::class
        ];

        $this->js = [
            'js/jquery.dataTables.1.10.19.min.js'
        ];

        parent::init();
    }
}