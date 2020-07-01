<?php

namespace barrelstrength\sproutbaseimport\importers\settings;

use barrelstrength\sproutbaseimport\base\SettingsImporter;
use barrelstrength\sproutbaseimport\models\importers\Field as FieldModel;
use barrelstrength\sproutbaseimport\SproutBaseImport;
use craft\base\FieldInterface;
use craft\records\Field as FieldRecord;
use Craft;
use Throwable;

class Field extends SettingsImporter
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return Craft::t('sprout-base-import', 'Field');
    }

    /**
     * @return string
     */
    public function getModelName(): string
    {
        return FieldModel::class;
    }

    /**
     * @param $settings
     *
     * @return FieldInterface
     */
    private function getFieldType($settings)
    {
        $fieldsService = Craft::$app->getFields();

        unset($settings['@model']);

        return $fieldsService->createField($settings);
    }


    /**
     * @param null $handle
     *
     * @return FieldInterface|null
     */
    public function getModelByHandle($handle = null)
    {
        return Craft::$app->getFields()->getFieldByHandle($handle);
    }

    /**
     * @inheritdoc
     */
    public function getRecord()
    {
        return FieldRecord::class;
    }

    /**
     * @return bool|FieldInterface|mixed
     * @throws Throwable
     */
    public function save()
    {
        $fieldsService = Craft::$app->getFields();

        if (!isset($this->model->id)) {
            $fieldType = $this->getFieldType($this->rows);

            if (!$fieldsService->saveField($fieldType)) {

                SproutBaseImport::error(Craft::t('sprout-base-import', 'Cannot save Field: '.$fieldType::displayName()));
                SproutBaseImport::info($fieldType);

                return false;
            }

            $this->model = $fieldType;
        } else {
            $fieldType = $this->model;
        }


        return $fieldType;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function deleteById($id)
    {
        return Craft::$app->getFields()->deleteFieldById($id);
    }
}
