<?php

namespace barrelstrength\sproutbaseimport\web\twig\variables;

use barrelstrength\sproutbaseimport\SproutBaseImport;
use Craft;

class SproutImportVariable
{
    /**
     * Confirm if a specific plugin is installed
     *
     * @param string
     *
     * @return bool
     */
    public function isPluginInstalled($plugin)
    {
        if (Craft::$app->getPlugins()->getPlugin($plugin)) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getSproutImportBundles()
    {
        return SproutBaseImport::$app->bundles->getSproutImportBundles();
    }

    /**
     * @return array
     */
    public function getSproutImportImporters()
    {

        return SproutBaseImport::$app->importers->getSproutImportImporters();
    }

    public function getBundleByClass($class)
    {
        return SproutBaseImport::$app->bundles->getBundleByClass($class);
    }

    /**
     * @return mixed
     */
    public function getSproutImportFieldImporters()
    {
        return SproutBaseImport::$app->importers->getSproutImportFieldImporters();
    }

    /**
     * Confirm if any seeds exist
     *
     * @return int
     */
    public function hasSeeds()
    {
        $seeds = SproutBaseImport::$app->seed->getAllSeeds();

        return count($seeds);
    }
}