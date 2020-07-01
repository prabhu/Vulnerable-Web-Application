<?php

namespace barrelstrength\sproutbaseimport\importers\fields;

use barrelstrength\sproutbaseimport\base\FieldImporter;
use barrelstrength\sproutbaseimport\SproutBaseImport;
use craft\fields\Dropdown as DropdownField;

class Dropdown extends FieldImporter
{
    /**
     * @return string
     */
    public function getModelName(): string
    {
        return DropdownField::class;
    }

    /**
     * @return mixed
     */
    public function getMockData()
    {
        $settings = $this->model->settings;

        $optionValue = '';

        if (!empty($settings['options'])) {
            $options = $settings['options'];

            $optionValue = SproutBaseImport::$app->fieldImporter->getRandomOptionValue($options);
        }

        return $optionValue;
    }
}