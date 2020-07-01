<?php

namespace barrelstrength\sproutbasereports\datasources;

use barrelstrength\sproutbasereports\base\DataSource;
use Craft;
use craft\base\MissingComponentInterface;
use craft\base\MissingComponentTrait;

/**
 * Class MissingDataSource
 *
 * @package Craft
 */
class MissingDataSource extends DataSource implements MissingComponentInterface
{
    use MissingComponentTrait;

    /**
     * Dynamically set description
     *
     * @var string
     */
    public $dynamicDescription;

    public static function displayName(): string
    {
        return Craft::t('sprout-base-reports', 'Missing Data Source');
    }

    /**
     * Set the description dynamically
     *
     * @var string
     */
    public function setDescription(string $message)
    {
        $this->dynamicDescription = $message;
    }

    /**
     * @inheritDoc
     */
    public function getDescription(): string
    {
        if ($this->dynamicDescription) {
            $message = $this->dynamicDescription;
        } else {
            $message = Craft::t('sprout-base-reports', 'Unable to find installed Data Source. See logs for details.');
        }

        return ' <em class="error">Unable to find class '.$message.'</em>';
    }
}
