<?php

namespace barrelstrength\sproutbaseimport\importers\elements;

use Craft;
use barrelstrength\sproutbaseimport\base\ElementImporter;
use craft\elements\Asset as AssetElement;

class Asset extends ElementImporter
{
    public function getName(): string
    {
        return Craft::t('sprout-base-import', 'Assets');
    }

    /**
     * @return mixed
     */
    public function getModelName(): string
    {
        return AssetElement::class;
    }

    public function getFieldLayoutId($model)
    {

    }
}