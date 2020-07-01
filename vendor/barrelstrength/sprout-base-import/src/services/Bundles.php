<?php

namespace barrelstrength\sproutbaseimport\services;

use barrelstrength\sproutbaseimport\bundles\SimpleBundle;
use barrelstrength\sproutbaseimport\base\Bundle;
use craft\base\Component;
use craft\events\RegisterComponentTypesEvent;
use Craft;
use craft\helpers\FileHelper;
use yii\base\ErrorException;
use yii\base\Exception;

/**
 *
 * @property array $sproutImportBundles
 */
class Bundles extends Component
{
    const EVENT_REGISTER_BUNDLE_TYPES = 'registerBundlesTypes';

    /**
     * @var array
     */
    protected $bundles = [];

    /**
     * @return array
     */
    public function getSproutImportBundles(): array
    {
        $bundleTypes = [
            SimpleBundle::class
        ];

        $event = new RegisterComponentTypesEvent([
            'types' => $bundleTypes
        ]);

        $this->trigger(self::EVENT_REGISTER_BUNDLE_TYPES, $event);

        $bundles = $event->types;

        if ($bundles !== null) {
            foreach ($bundles as $bundleClass) {

                // Create an instance of our Bundle object
                $bundle = new $bundleClass();

                $this->bundles[$bundleClass] = $bundle;
            }
        }

        uasort($this->bundles, function($a, $b) {
            /**
             * @var $a Bundle
             * @var $b Bundle
             */
            return $a->getName() <=> $b->getName();
        });

        return $this->bundles;
    }

    /**
     * @param $class
     *
     * @return mixed|null
     */
    public function getBundleByClass($class)
    {
        $this->getSproutImportBundles();

        return $this->bundles[$class] ?? null;
    }

    /**
     * Make sure the Sprout Import temp folder is created
     *
     * @return string
     * @throws ErrorException
     * @throws Exception
     */
    public function createTempFolder(): string
    {
        $folderPath = Craft::$app->getPath()->getTempAssetUploadsPath().'/sproutimport/';

        if (file_exists($folderPath)) {
            FileHelper::clearDirectory($folderPath);
        }

        FileHelper::createDirectory($folderPath);

        return $folderPath;
    }
}
