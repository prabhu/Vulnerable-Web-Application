<?php

namespace barrelstrength\sproutbaseimport\importers\fields;

use barrelstrength\sproutbaseimport\base\FieldImporter;
use craft\fields\Lightswitch as LightswitchField;
use Exception;

class Lightswitch extends FieldImporter
{
    /**
     * @return string
     */
    public function getModelName(): string
    {
        return LightswitchField::class;
    }

    /**
     * Returns a boolean value
     *
     * @return mixed
     * @throws Exception
     */
    public function getMockData()
    {
        return random_int(0, 1);
    }
}
