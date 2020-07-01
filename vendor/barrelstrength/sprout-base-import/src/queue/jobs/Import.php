<?php

namespace barrelstrength\sproutbaseimport\queue\jobs;

use barrelstrength\sproutbaseimport\SproutBaseImport;
use barrelstrength\sproutbaseimport\models\Seed;
use barrelstrength\sproutbaseimport\models\Weed;
use craft\helpers\Json;
use craft\queue\BaseJob;
use Craft;
use Exception;
use Throwable;
use yii\helpers\VarDumper;

class Import extends BaseJob
{
    public $importData;

    public $seedAttributes;

    /**
     * @inheritdoc
     *
     * @param $queue
     *
     * @throws Throwable
     */
    public function execute($queue)
    {
        $seedModel = new Seed();
        $seedModel->setAttributes($this->seedAttributes, false);

        try {

            $weedModelAttributes = [
                'seed' => $seedModel->enabled,
                'seedType' => $seedModel->seedType,
                'details' => Craft::t('sprout-base-import', 'Import Type: '.$seedModel->seedType),
                'dateSubmitted' => $seedModel->dateCreated
            ];

            $weedModel = new Weed();
            $weedModel->setAttributes($weedModelAttributes, false);

            $this->importData = Json::decode($this->importData);

            SproutBaseImport::$app->importers->save($this->importData, $weedModel);

            $errors = SproutBaseImport::$app->importUtilities->getErrors();

            if (!empty($errors)) {

                $errors = VarDumper::dumpAsString($errors);

                $message = Craft::t('sprout-base-import', 'Error(s) while running Sprout Import job.');

                SproutBaseImport::error($message);
                SproutBaseImport::error($errors);
            }
        } catch (Exception $e) {
            SproutBaseImport::error('Unable to run Sprout Import job.');
            SproutBaseImport::error($e->getMessage());
        }
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return Craft::t('sprout-base-import', 'Importing Data.');
    }
}
