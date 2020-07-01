<?php

namespace barrelstrength\sproutbaseimport\importers\fields;

use barrelstrength\sproutbaseimport\base\FieldImporter;
use barrelstrength\sproutbaseimport\SproutBaseImport;
use craft\fields\Table as TableField;
use Exception;

class Table extends FieldImporter
{
    /**
     * @return string
     */
    public function getModelName(): string
    {
        return TableField::class;
    }

    /**
     * @return array|mixed|null
     * @throws Exception
     */
    public function getMockData()
    {
        $settings = $this->model->settings;

        if (!isset($settings['columns'])) {
            return null;
        }

        $columns = $settings['columns'];
        $minRows = $settings['minRows'] ?: 1;
        $maxRows = $settings['maxRows'] ?: 5;

        $randomLength = random_int($minRows, $maxRows);

        $values = [];

        for ($inc = 1; $inc <= $randomLength; $inc++) {
            $values[] = SproutBaseImport::$app->fieldImporter->generateTableColumns($columns);
        }

        return $values;
    }

}
