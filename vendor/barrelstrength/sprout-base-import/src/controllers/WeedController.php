<?php

namespace barrelstrength\sproutbaseimport\controllers;

use barrelstrength\sproutbaseimport\SproutBaseImport;
use Craft;
use craft\errors\MissingComponentException;
use craft\web\Controller;
use Throwable;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\db\Exception;
use yii\web\BadRequestHttpException;

class WeedController extends Controller
{
    /**
     * Render the Weed template
     *
     * @return Response
     * @throws ForbiddenHttpException
     * @throws \Exception
     */
    public function actionWeedIndex(): Response
    {
        $this->requirePermission('sproutImport-removeSeeds');

        $seeds = SproutBaseImport::$app->seed->getSeeds();

        return $this->renderTemplate('sprout-base-import/weed/index', [
            'seeds' => $seeds
        ]);
    }

    /**
     * @return null|Response
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws Throwable
     * @throws MissingComponentException
     */
    public function actionProcessWeed()
    {
        $this->requirePostRequest();
        $this->requirePermission('sproutImport-removeSeeds');

        $submit = Craft::$app->getRequest()->getParam('submit');

        $isKeep = true;

        if ($submit == 'Weed' || $submit == 'Weed All') {
            $isKeep = false;
        }

        $seeds = [];

        $dateCreated = Craft::$app->getRequest()->getParam('dateCreated');

        // @todo - move this logic to the service layer
        if ($dateCreated != null && $dateCreated != '*') {
            $seeds = SproutBaseImport::$app->seed->getSeedsByDateCreated($dateCreated);
        }

        if ($dateCreated == '*') {
            $seeds = SproutBaseImport::$app->seed->getAllSeeds();
        }

        // @todo - update weed method to accept a Weed model so we can validate.
        if (!SproutBaseImport::$app->seed->weed($seeds, $isKeep)) {

            Craft::$app->getSession()->setError(Craft::t('sprout-import', 'Unable to weed data.'));

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('sprout-import', 'The garden is weeded.'));

        return $this->redirectToPostedUrl();
    }
}