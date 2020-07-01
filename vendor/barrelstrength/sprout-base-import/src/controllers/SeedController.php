<?php

namespace barrelstrength\sproutbaseimport\controllers;

use barrelstrength\sproutbaseimport\models\jobs\SeedJob;
use barrelstrength\sproutbaseimport\SproutBaseImport;
use barrelstrength\sproutbaseimport\importers\elements\Category;
use barrelstrength\sproutbaseimport\importers\elements\Entry;
use barrelstrength\sproutbaseimport\importers\elements\Tag;
use barrelstrength\sproutbaseimport\importers\elements\User;
use craft\errors\MissingComponentException;
use craft\helpers\DateTimeHelper;
use craft\web\Controller;
use Craft;
use barrelstrength\sproutbaseimport\enums\ImportType;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class SeedController extends Controller
{
    /**
     * @param SeedJob|null $seedJob
     *
     * @return Response
     * @throws ForbiddenHttpException
     */
    public function actionSeedIndex(SeedJob $seedJob = null): Response
    {
        $this->requirePermission('sproutImport-generateSeeds');

        if ($seedJob === null) {
            $seedJob = new SeedJob();
        }

        $elementSelect = [];

        $allSeedImporters = SproutBaseImport::$app->importers->getSproutImportSeedImporters();

        $defaultKeys = [
            Entry::class,
            Category::class,
            Tag::class,
            User::class
        ];

        $defaultSeedImporters = [];
        $customSeedImporters = [];

        if ($allSeedImporters) {
            foreach ($allSeedImporters as $key => $allSeedImporter) {
                if (in_array($key, $defaultKeys, false)) {
                    $defaultSeedImporters[$key] = $allSeedImporter;
                } else {
                    $customSeedImporters[$key] = $allSeedImporter;
                }
            }
        }

        if (!empty($defaultSeedImporters)) {
            $elementSelect['standard-elements'] = [
                'optgroup' => Craft::t('sprout-import', 'Standard Elements')
            ];

            foreach ($defaultSeedImporters as $importer) {
                $classNameSpace = get_class($importer);
                $title = $importer->getName();

                $elementSelect[$classNameSpace] = [
                    'label' => $title,
                    'value' => $classNameSpace
                ];
            }
        }

        if (!empty($customSeedImporters)) {
            $elementSelect['custom-elements'] = [
                'optgroup' => Craft::t('sprout-import', 'Custom Elements')
            ];

            foreach ($customSeedImporters as $importer) {

                $classNameSpace = get_class($importer);

                $title = $importer->getName();

                $elementSelect[$classNameSpace] = [
                    'label' => $title,
                    'value' => $classNameSpace
                ];
            }
        }

        return $this->renderTemplate('sprout-base-import/seed/index', [
            'elements' => $elementSelect,
            'importers' => $allSeedImporters,
            'seedJob' => $seedJob
        ]);
    }

    /**
     * Generates Elements with mock data and mark them as Seeds
     *
     * @return bool|Response
     * @throws BadRequestHttpException
     * @throws MissingComponentException
     * @throws ForbiddenHttpException
     */
    public function actionGenerateElementSeeds()
    {
        $this->requirePostRequest();
        $this->requirePermission('sproutImport-generateSeeds');

        $elementType = Craft::$app->getRequest()->getRequiredBodyParam('elementType');
        $quantity = Craft::$app->getRequest()->getBodyParam('quantity');
        $settings = Craft::$app->getRequest()->getBodyParam('settings.'.$elementType);

        $weedMessage = Craft::t('sprout-import', '{elementType} Element(s)');

        $details = Craft::t('sprout-import', $weedMessage, [
            'elementType' => $elementType
        ]);

        $seedJob = new SeedJob();
        $seedJob->elementType = $elementType;
        $seedJob->quantity = !empty($quantity) ? $quantity : 11;
        $seedJob->settings = $settings;
        $seedJob->seedType = ImportType::Seed;
        $seedJob->details = $details;
        $seedJob->dateCreated = DateTimeHelper::currentUTCDateTime();

        $seedJobErrors = null;

        if (!SproutBaseImport::$app->seed->generateSeeds($seedJob)) {

            $seedJobErrors = $seedJob->getErrors();

            SproutBaseImport::error($seedJobErrors);
        }

        $errors = SproutBaseImport::$app->importUtilities->getErrors();

        if (!empty($errors) || $seedJobErrors != null) {
            $message = Craft::t('sprout-import', 'Unable to plant seeds.');

            Craft::$app->getSession()->setError($message);

            SproutBaseImport::error($errors);

            return Craft::$app->getUrlManager()->setRouteParams([
                'seedJob' => $seedJob
            ]);
        }

        Craft::$app->getSession()->setNotice(Craft::t('sprout-import', '{quantity} Element(s) queued for seeding.', [
            'quantity' => $quantity
        ]));

        return $this->redirectToPostedUrl($seedJob);
    }
}
