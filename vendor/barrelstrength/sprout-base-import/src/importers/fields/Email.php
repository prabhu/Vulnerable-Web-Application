<?php

namespace barrelstrength\sproutbaseimport\importers\fields;

use barrelstrength\sproutbaseimport\base\FieldImporter;
use craft\fields\Email as EmailField;

class Email extends FieldImporter
{
    /**
     * @return string
     */
    public function getModelName(): string
    {
        return EmailField::class;
    }

    /**
     * @return mixed
     */
    public function getMockData()
    {
        return $this->fakerService->email;
    }
}
