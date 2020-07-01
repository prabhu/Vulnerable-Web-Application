<?php

namespace barrelstrength\sproutbaseimport\importers\fields;

use barrelstrength\sproutbaseimport\base\FieldImporter;
use craft\fields\Color as ColorField;

class Color extends FieldImporter
{
    /**
     * @return string
     */
    public function getModelName(): string
    {
        return ColorField::class;
    }

    /**
     * @return mixed
     */
    public function getMockData()
    {
        return $this->fakerService->hexColor;
    }
}
