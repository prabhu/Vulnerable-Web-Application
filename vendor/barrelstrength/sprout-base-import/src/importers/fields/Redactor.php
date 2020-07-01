<?php

namespace barrelstrength\sproutbaseimport\importers\fields;

use barrelstrength\sproutbaseimport\base\FieldImporter;
use craft\redactor\Field;
use Exception;

class Redactor extends FieldImporter
{
    /**
     * @return string
     */
    public function getModelName(): string
    {
        return Field::class;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getMockData()
    {
        $lines = random_int(3, 5);
        $paragraphs = $this->fakerService->paragraphs($lines);

        return implode("\n\n", $paragraphs);
    }
}
