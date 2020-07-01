<?php

namespace barrelstrength\sproutbaseimport\importers\fields;

use barrelstrength\sproutbaseimport\base\FieldImporter;
use craft\fields\Checkboxes as CheckboxesField;
use barrelstrength\sproutbaseimport\SproutBaseImport;
use Exception;

class Checkboxes extends FieldImporter
{
    /**
     * @return string
     */
    public function getModelName(): string
    {
        return CheckboxesField::class;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getMockData()
    {
        $settings = $this->model->settings;

        $values = [];

        if (!empty($settings['options'])) {
            $options = $settings['options'];

            $length = count($options);
            $number = random_int(1, $length);

            $randomArrayItems = SproutBaseImport::$app->fieldImporter->getRandomArrayItems($options, $number);

            $values = SproutBaseImport::$app->fieldImporter->getOptionValuesByKeys($randomArrayItems, $options);
        }

        return $values;
    }
}
