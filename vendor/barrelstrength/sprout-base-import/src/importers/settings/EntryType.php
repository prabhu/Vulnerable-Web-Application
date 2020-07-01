<?php

namespace barrelstrength\sproutbaseimport\importers\settings;

use barrelstrength\sproutbaseimport\base\SettingsImporter;
use barrelstrength\sproutbaseimport\SproutBaseImport;
use craft\errors\EntryTypeNotFoundException;
use craft\models\EntryType as EntryTypeModel;
use craft\records\EntryType as EntryTypeRecord;
use craft\elements\Entry;
use Craft;
use Exception;
use Throwable;

class EntryType extends SettingsImporter
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return Craft::t('sprout-base-import', 'Entry Type');
    }

    /**
     * @return string
     */
    public function getModelName(): string
    {
        return EntryTypeModel::class;
    }

    /**
     * @param EntryTypeModel $entryType
     * @param array          $rows
     *
     * @return mixed|void
     * @throws Exception
     */
    public function setModel($entryType, array $rows = [])
    {
        // Set the simple stuff
        $entryType->sectionId = $rows['sectionId'] ?? null;
        $entryType->name = $rows['name'] ?? null;
        $entryType->handle = $rows['handle'] ?? null;
        $entryType->hasTitleField = $rows['hasTitleField'] ?? true;
        $entryType->titleLabel = $rows['titleLabel'] ?? Craft::t('sprout-base-import', 'Title');
        $entryType->titleFormat = $rows['titleFormat'] ?? '';

        if (isset($rows['fieldLayout'])) {
            $fieldLayoutTabs = $rows['fieldLayout'];
            $fieldLayout = [];
            $requiredFields = [];

            foreach ($fieldLayoutTabs as $tab) {
                $tabName = $tab['name'];
                $fields = $tab['fields'];

                foreach ($fields as $fieldSettings) {
                    $model = SproutBaseImport::$app->importers->getImporter($fieldSettings);

                    $field = SproutBaseImport::$app->settingsImporter->saveSetting($fieldSettings, $model);

                    $fieldLayout[$tabName][] = $field->id;

                    if ($field->required) {
                        $requiredFields[] = $field->id;
                    }
                }
            }

            if ($entryType->getFieldLayout() != null) {
                // Remove previous field layout and update layout
                Craft::$app->getFields()->deleteLayoutById($entryType->fieldLayoutId);
            }

            $fieldLayout = Craft::$app->getFields()->assembleLayout($fieldLayout, $requiredFields);

            // Make Entry element as default
            $fieldLayout->type = empty($rows['elementType']) ? Entry::class : $rows['elementType'];

            $entryType->setFieldLayout($fieldLayout);
        }

        $this->model = $entryType;
    }

    /**
     * @inheritdoc
     */
    public function getRecordName()
    {
        return EntryTypeRecord::class;
    }

    /**
     * @return bool
     * @throws Throwable
     * @throws EntryTypeNotFoundException
     */
    public function save()
    {
        return Craft::$app->getSections()->saveEntryType($this->model);
    }

    /**
     * @param $id
     *
     * @return mixed|void
     */
    public function deleteById($id)
    {
        //return craft()->sections->deleteEntryTypeById($id);
    }

    /**
     * @param null $handle
     *
     * @return mixed
     */
    public function getModelByHandle($handle = null)
    {
        $types = Craft::$app->getSections()->getEntryTypesByHandle($handle);

        if (!empty($types)) {
            return $types[0];
        }

        return null;
    }
}
