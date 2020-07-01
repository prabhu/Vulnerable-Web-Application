<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbaseimport\services;

use craft\base\Component;

class App extends Component
{
    /**
     * @var Importers
     */
    public $importers;

    /**
     * @var FieldImporter
     */
    public $fieldImporter;

    /**
     * @var Bundles
     */
    public $bundles;


    /**
     * @var ElementImporter
     */
    public $elementImporter;

    /**
     * @var SettingsImporter
     */
    public $settingsImporter;

    /**
     * @var ImportUtilities
     */
    public $importUtilities;

    /**
     * @var Seed
     */
    public $seed;

    /**
     * @inheritdoc
     */
    public function init()
    {
        // Sprout Import
        $this->bundles = new Bundles();
        $this->elementImporter = new ElementImporter();
        $this->fieldImporter = new FieldImporter();
        $this->importers = new Importers();
        $this->importUtilities = new ImportUtilities();
        $this->seed = new Seed();
        $this->settingsImporter = new SettingsImporter();
    }
}
