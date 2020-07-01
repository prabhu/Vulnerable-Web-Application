<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbasefields\web\twig\variables;

use barrelstrength\sproutbasefields\SproutBaseFields;

class SproutFieldsVariable
{
    /**
     * Return countries for Phone Field
     *
     * @return array
     */
    public function getCountries(): array
    {
        return SproutBaseFields::$app->phoneField->getCountries();
    }
}
