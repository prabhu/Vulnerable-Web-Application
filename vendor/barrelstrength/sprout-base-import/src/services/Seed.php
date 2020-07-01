<?php

namespace barrelstrength\sproutbaseimport\services;

use barrelstrength\sproutbaseimport\base\ElementImporter as BaseElementImporter;
use barrelstrength\sproutbaseimport\base\SettingsImporter;
use barrelstrength\sproutbaseimport\SproutBaseImport;
use barrelstrength\sproutbaseimport\models\jobs\SeedJob as SeedJobModel;
use barrelstrength\sproutbaseimport\queue\jobs\Seed as SeedJob;
use barrelstrength\sproutbaseimport\models\Weed;
use craft\base\Component;
use Craft;
use craft\db\Query;
use barrelstrength\sproutbaseimport\models\Seed as SeedModel;
use barrelstrength\sproutbaseimport\records\Seed as SeedRecord;
use craft\helpers\DateTimeHelper;
use Exception;
use Throwable;

/**
 *
 * @property mixed $seeds
 * @property array $allSeeds
 */
class Seed extends Component
{
    /**
     * Return all imported content and settings marked as seed data
     *
     * @return array
     */
    public function getAllSeeds()
    {
        $query = new Query();

        $seeds = $query->select('*')
            ->from('{{%sproutimport_seeds}}')
            ->all();

        return $seeds;
    }

    /**
     * @param $seedJobModel SeedJobModel
     *
     * @return bool
     */
    public function generateSeeds(SeedJobModel $seedJobModel)
    {
        if (!$seedJobModel->validate()) {
            return false;
        }

        try {
            //SproutBaseImport::$app->seed->runSeed($seedJobModel->getAttributes());
            // @todo temporary for debugging
            Craft::$app->queue->push(new SeedJob([
                'seedJob' => $seedJobModel->getAttributes()
            ]));

            return true;
        } catch (Exception $e) {
            SproutBaseImport::error($e->getMessage());
        }

        return false;
    }

    /**
     * Mark an item being imported as seed data
     *
     * @param SeedModel $model
     *
     * @return bool
     */
    public function trackSeed(SeedModel $model)
    {
        $itemId = $model->itemId;

        $record = SeedRecord::find()->where(['itemId' => $itemId])->one();

        $result = false;

        // Avoids duplicate tracking
        if ($record == null) {
            $record = new SeedRecord();

            $recordAttributes = $model->getAttributes();

            if (!empty($recordAttributes)) {
                foreach ($recordAttributes as $handle => $value) {
                    if (!empty($value)) {
                        $record->setAttribute($handle, $value);
                    }
                }
            }

            $result = $record->save();
        }

        return $result;
    }

    /**
     * Remove a group of items from the database that are marked as seed data as identified by their class handle
     *
     * @param array $seeds
     * @param bool  $isKeep
     *
     * @return bool
     * @throws Throwable
     * @throws \yii\db\Exception
     */
    public function weed(array $seeds = [], $isKeep = false)
    {
        $transaction = Craft::$app->getDb()->beginTransaction();

        if (!empty($seeds)) {
            foreach ($seeds as $seed) {
                try {
                    if (!$isKeep) {
                        $row = [];

                        $row['@model'] = $seed['type'];

                        /**
                         * @var $importerClass BaseElementImporter|SettingsImporter
                         */
                        $importerClass = SproutBaseImport::$app->importers->getImporter($row);

                        $importerClass->deleteById($seed['itemId']);
                    }

                    SproutBaseImport::$app->seed->deleteSeedById($seed['id']);
                } catch (Exception $e) {
                    SproutBaseImport::error($e->getMessage());

                    return false;
                }
            }

            if ($transaction) {
                $transaction->commit();
            }

            return true;
        }

        return false;
    }

    /**
     * Delete seed data from the database by id
     *
     * @param $id
     *
     * @return int
     * @throws \yii\db\Exception
     */
    public function deleteSeedById($id)
    {
        return Craft::$app->getDb()->createCommand()->delete(
            'sproutimport_seeds',
            '[[id]]=:id',
            [':id' => $id]
        )->execute();
    }

    /**
     * Get the number of seed items in the database for element class type
     *
     * @param $type
     *
     * @return string
     */
    public function getSeedCountByElementType($type)
    {
        $count = SeedRecord::find()->where([
            'type' => $type
        ])->count();

        if ($count) {
            return $count;
        }

        return '0';
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getSeeds(): array
    {
        $query = new Query();
        $seeds = $query
            ->select('seedType, details, COUNT(1) as total, dateCreated')
            ->from('{{%sproutimport_seeds}}')
            ->groupBy(['dateCreated', 'details', 'seedType'])
            ->orderBy('dateCreated DESC')
            ->all();

        if ($seeds) {
            foreach ($seeds as $key => $seed) {
                $dateTime = DateTimeHelper::toDateTime($seed['dateCreated']);

                // Display the user set control panel timezone
                $seeds[$key]['dateCreated'] = $dateTime->getTimestamp();
            }
        }

        return $seeds;
    }

    /**
     * Returns seeds by dateCreated
     *
     * @param $date
     *
     * @return array
     */
    public function getSeedsByDateCreated($date): array
    {
        $query = new Query();

        $seeds = $query
            ->select('*')
            ->from('{{%sproutimport_seeds}}')
            ->where('dateCreated=:dateCreated', [':dateCreated' => $date])
            ->all();

        return $seeds;
    }

    /**
     * @param array $seedJob
     *
     * @return bool
     * @throws Throwable
     */
    public function runSeed(array $seedJob)
    {
        $qty = $seedJob['quantity'];
        $details = $seedJob['details'];

        $weedModelAttributes = [
            'seed' => true,
            'seedType' => $seedJob['seedType'],
            'details' => $details,
            'dateCreated' => $seedJob['dateCreated']
        ];

        try {
            $weedModel = new Weed();

            $weedModel->setAttributes($weedModelAttributes, false);

            $elementType = $seedJob['elementType'];
            $settings = $seedJob['settings'];

            /**
             * @var $importerClass BaseElementImporter
             */
            $importerClass = new $elementType;

            for ($count = 1; $count <= $qty; $count++) {

                $seed = $importerClass->getMockData(1, $settings);

                SproutBaseImport::$app->importers->save($seed, $weedModel);

                $errors = SproutBaseImport::$app->importUtilities->getErrors();

                if (!empty($errors)) {

                    Craft::error('Unable to save Seed data.', 'sprout-import');
                    Craft::error($errors, 'sprout-import');

                    return false;
                }
            }

            return true;
        } catch (Exception $e) {

            Craft::error('Unable to save Seed data. Rolling back.', 'sprout-import');
            Craft::error($e->getMessage());

            throw $e;
        }
    }
}
