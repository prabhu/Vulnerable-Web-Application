<?php

namespace barrelstrength\sproutbaseimport\services;

use barrelstrength\sproutbaseimport\base\SettingsImporter as BaseSettingsImporter;
use barrelstrength\sproutbaseimport\SproutBaseImport;
use craft\base\Component;
use Craft;
use craft\base\Model;
use Exception;

class SettingsImporter extends Component
{
    /**
     * @param                           $rows
     * @param BaseSettingsImporter|null $importerClass
     *
     * @return bool|Model|mixed|null
     * @throws Exception
     */
    public function saveSetting($rows, BaseSettingsImporter $importerClass = null)
    {
        $model = $importerClass->getModel();

        if (!$model->validate(null, false)) {

            SproutBaseImport::error(Craft::t('sprout-base-import', 'Errors found on model while saving Settings'));

            SproutBaseImport::$app->importUtilities->addError('invalid-model', $model->getErrors());

            return false;
        }

        try {

            if ($importerClass->save()) {
                // Get updated model after save
                $model = $importerClass->getModel();

                $importerClass->resolveNestedSettings($model, $rows);

                return $model;
            }

            return false;
        } catch (Exception $e) {

            $message = Craft::t('sprout-base-import', 'Unable to import Settings.');

            SproutBaseImport::error($message);
            SproutBaseImport::error($e->getMessage());

            SproutBaseImport::$app->importUtilities->addError('save-setting-importer', $message);

            return false;
        }
    }
}