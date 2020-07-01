<?php

namespace barrelstrength\sproutbaseimport\services;

use barrelstrength\sproutbaseimport\base\Importer;
use barrelstrength\sproutbaseimport\SproutBaseImport;
use barrelstrength\sproutbaseimport\events\ElementImportEvent;
use Craft;
use craft\base\Component;
use craft\base\Element;
use craft\base\Model;
use barrelstrength\sproutbaseimport\base\ElementImporter as BaseElementImporter;
use barrelstrength\sproutbaseimport\base\SettingsImporter as BaseSettingsImporter;
use barrelstrength\sproutbaseimport\base\SettingsImporter;
use craft\errors\DeprecationException;
use Exception;
use ReflectionException;
use Throwable;

/**
 *
 * @property array $elementDataKeys
 */
class ElementImporter extends Component
{
    /**
     * @event ElementImportEvent The event that is triggered before the element is imported
     */
    const EVENT_BEFORE_ELEMENT_IMPORT = 'onBeforeElementImport';

    /**
     * @event ElementImportEvent The event that is triggered after the element is imported
     */
    const EVENT_AFTER_ELEMENT_IMPORT = 'onAfterElementImport';

    /**
     * Elements saved during the length of the import job
     *
     * @var array
     */
    protected $savedElements = [];

    /**
     * Element Ids saved during the length of the import job
     *
     * @var array
     */
    protected $savedElementIds = [];

    /**
     * Current saved element when running saveElement method
     *
     * @var Element
     */
    protected $savedElement;

    /**
     * @var array
     */
    protected $unsavedElements = [];

    /**
     * @var array
     */
    protected $updatedElements = [];

    /**
     * @param                     $rows
     * @param BaseElementImporter $importerClass
     * @param bool                $seed
     *
     * @return bool|mixed
     * @throws ReflectionException
     * @throws Throwable
     */
    public function saveElement($rows, BaseElementImporter $importerClass, $seed = false)
    {
        $additionalDataKeys = $importerClass->getImporterDataKeys();

        $definedDataKeys = array_merge($this->getElementDataKeys(), $additionalDataKeys);

        $dataKeys = array_keys($rows);

        // Catches invalid element keys
        $dataKeysDiff = array_diff($dataKeys, $definedDataKeys);

        if (!empty($dataKeysDiff)) {
            $inputKeysText = implode(', ', $dataKeysDiff);

            $message = Craft::t('sprout-base-import', "Invalid element keys '$inputKeysText'.");

            SproutBaseImport::error($message);

            SproutBaseImport::$app->importUtilities->addError($inputKeysText, $message);

            return false;
        }

        $utilities = SproutBaseImport::$app->importUtilities;

        $fields = $utilities->getValueByKey('content.fields', $rows);

        if (!empty($fields) && method_exists($importerClass, 'getAllFieldHandles')) {
            // Catches invalid field handles stops the importing
            $elementFieldHandles = $importerClass->getAllFieldHandles();

            // Merge default handle
            $elementFieldHandles[] = 'title';

            foreach ($fields as $fieldHandle => $fieldValue) {
                if (!in_array($fieldHandle, $elementFieldHandles, false)) {
                    $key = 'field-null-'.$fieldHandle;

                    $message = Craft::t('sprout-base-import', 'Could not find the {fieldHandle} field.', [
                        'fieldHandle' => $fieldHandle
                    ]);

                    SproutBaseImport::error($message);
                    SproutBaseImport::$app->importUtilities->addError($key, $message);

                    return false;
                }
            }
        }

        unset($rows['content']['related']);

        $model = $importerClass->getModel();
        $modelName = $importerClass->getImporterClass();

        $importerClass->beforeValidateElement();

        $this->trigger(self::EVENT_BEFORE_ELEMENT_IMPORT, new ElementImportEvent([
            'modelName' => $modelName,
            'element' => $model,
            'seed' => $seed
        ]));

        $saved = false;

        if ($model->validate(null, false) && $model->hasErrors() == false) {
            $isNewElement = !$model->id;

            try {
                try {
                    $importerClass->save();

                    // Get updated model after save
                    $model = $importerClass->getModel();

                    $errors = $model->getErrors();

                    // Check for field setting errors
                    if (!empty($errors)) {
                        $this->logErrorByModel($model);

                        return false;
                    }

                    $this->savedElement = $model;

                    $saved = true;
                } catch (Exception $e) {
                    $message = Craft::t('sprout-base-import', "Error when saving Element. \n ");
                    $message .= $e->getMessage();

                    SproutBaseImport::error($message);

                    SproutBaseImport::$app->importUtilities->addError('save-importer', $message);

                    return false;
                }

                if ($saved && $isNewElement) {
                    $this->savedElementIds[] = $model->id;
                    $this->savedElements[] = $model->title;
                } elseif ($saved && !$isNewElement) {
                    $this->updatedElements[] = $model->title;
                } else {
                    $this->unsavedElements[] = $model->title;
                }
            } catch (Exception $e) {
                $this->unsavedElements[] = [
                    'title' => $model->title,
                    'error' => $e->getMessage()
                ];

                $title = $utilities->getValueByKey('content.title', $rows);

                $fieldsMessage = is_array($fields) ? implode(', ', array_keys($fields)) : $fields;

                $message = $title.' '.$fieldsMessage.Craft::t('sprout-base-import', ' Check field values if it exists.');

                SproutBaseImport::error($message);

                SproutBaseImport::$app->importUtilities->addError('save-element-error', $message);

                SproutBaseImport::$app->importUtilities->addError('save-element-error-message', $e->getMessage());
            }
        } else {
            $this->logErrorByModel($model);
        }

        if ($saved) {

            $this->trigger(self::EVENT_AFTER_ELEMENT_IMPORT, new ElementImportEvent([
                'modelName' => $modelName,
                'element' => $model,
                'seed' => $seed
            ]));

            $importerClass->resolveNestedSettings($model, $rows);

            return $model;
        }

        return $saved;
    }

    /**
     * @param Model $model
     */
    public function logErrorByModel(Model $model)
    {
        SproutBaseImport::error(Craft::t('sprout-base-import', 'Errors found on model while saving Element'));

        SproutBaseImport::$app->importUtilities->addError('sproutImport', $model->getErrors());
    }

    /**
     * @param      $elementTypeName
     * @param      $updateElementSettings
     * @param bool $all
     *
     * @return array|bool|Element|null|static|static[]
     * @throws DeprecationException
     */
    public function getElementFromImportSettings($elementTypeName, $updateElementSettings, $all = false)
    {
        $utilities = SproutBaseImport::$app->importUtilities;

        $params = $utilities->getValueByKey('params', $updateElementSettings);

        /**
         * @deprecated - The matchBy, matchValue, and matchCriteria keys will be removed in Sprout Import v2.0.0
         *
         * If the new 'params' syntax isn't used, use deprecated matchCriteria values if provided
         */
        $matchBy = $utilities->getValueByKey('matchBy', $updateElementSettings);
        $matchValue = $utilities->getValueByKey('matchValue', $updateElementSettings);
        $matchCriteria = $utilities->getValueByKey('matchCriteria', $updateElementSettings);

        if ($params === null && ($matchBy || $matchValue || $matchCriteria)) {
            if ($matchBy !== null) {
                Craft::$app->getDeprecator()->log('ElementImporter matchBy key', 'The “matchBy” key has been deprecated. Use “params” in place of “matchBy”, “matchValue”, and “matchCriteria”.');
            }

            if ($matchValue !== null) {
                Craft::$app->getDeprecator()->log('ElementImporter matchValue key', 'The “matchValue” key has been deprecated. Use “params” in place of “matchBy”, “matchValue”, and “matchCriteria”.');
            }

            if ($matchCriteria !== null) {
                Craft::$app->getDeprecator()->log('ElementImporter matchCriteria key', 'The “matchCriteria” key has been deprecated. Use “params” in place of “matchBy”, “matchValue”, and “matchCriteria”.');
            }

            $params = [
                $matchBy => $matchValue
            ];

            if (is_array($matchCriteria)) {
                $params = array_merge($params, $matchCriteria);
            }
        }

        // Find all element statuses to avoid errors when one of the element is disabled.
        $status = [
            'status' => [
                Element::STATUS_ARCHIVED,
                Element::STATUS_ENABLED,
                Element::STATUS_DISABLED
            ]
        ];

        $params = array_merge($params, $status);

        /**
         * @var $elementType Element
         */
        $elementType = new $elementTypeName();

        try {
            if ($all == true) {
                $element = $elementType::findAll($params);
            } else {
                $element = $elementType::findOne($params);
            }

            return $element;
        } catch (Exception $e) {

            SproutBaseImport::error($e->getMessage());

            SproutBaseImport::$app->importUtilities->addError('invalid-model-match', $e->getMessage());

            return false;
        }
    }

    /**
     * @param array|null $related
     * @param array      $fields
     *
     * @return array|false
     * @throws Throwable
     */
    public function resolveRelationships(array $related = null, array $fields)
    {
        if (!count($related)) {
            return null;
        }

        /**
         * $elementSettings can be attribute or content criteria/params
         */
        foreach ($related as $fieldHandle => $relatedSettings) {

            $ids = null;

            if (empty($relatedSettings)) {
                unset($related[$fieldHandle]);
                continue;
            }

            /**
             * @var $importerClass Importer|SettingsImporter
             */
            $importerClass = SproutBaseImport::$app->importers->getImporter($relatedSettings);

            if (!$importerClass) {
                return null;
            }

            if ($importerClass instanceof BaseElementImporter) {
                $ids = $this->getElementRelationIds($importerClass, $relatedSettings);
            } else {
                $ids = $this->getSettingRelationIds($importerClass, $relatedSettings);
            }

            if (!$ids) {
                continue;
            }

            // $ids could be array for elements and an int for setting
            if (!empty($ids)) {
                $fields[$fieldHandle] = $ids;
            } else {
                $fields[$fieldHandle] = [0];
            }
        }

        return $fields;
    }

    /**
     * Returns the related Element ID(s)
     *
     * @param BaseElementImporter $importerClass
     * @param array               $relatedSettings
     *
     * @return array|bool
     * @throws Throwable
     */
    private function getElementRelationIds(BaseElementImporter $importerClass, array $relatedSettings = [])
    {
        $elementIds = [];
        $newElements = SproutBaseImport::$app->importUtilities->getValueByKey('newElements', $relatedSettings);

        $model = $importerClass->getModel();
        $elementTypeName = get_class($model);
        $elements = $this->getElementFromImportSettings($elementTypeName, $relatedSettings, true);

        if (!empty($elements)) {
            foreach ($elements as $element) {
                $elementIds[] = $element->id;
            }
        }

        if (is_array($newElements) && count($newElements)) {
            try {
                foreach ($newElements as $row) {
                    /**
                     * @var $importerClass BaseElementImporter
                     */
                    $importerClass = SproutBaseImport::$app->importers->getImporter($row);

                    $this->saveElement($row, $importerClass);

                    if ($this->savedElement) {
                        $elementIds[] = $this->savedElement->id;
                    }
                }
            } catch (Exception $e) {
                $message['errorMessage'] = $e->getMessage();
                $message['errorObject'] = $e;

                SproutBaseImport::error($message);

                return false;
            }
        }

        return $elementIds;
    }

    /**
     * Returns the matched settings record ID
     *
     * @param SettingsImporter $importerClass
     * @param array            $relatedSettings
     *
     * @return int|null
     * @throws DeprecationException
     */
    private function getSettingRelationIds(BaseSettingsImporter $importerClass, array $relatedSettings = [])
    {
        $params = SproutBaseImport::$app->importUtilities->getValueByKey('params', $relatedSettings);

        /**
         * @deprecated - The matchBy, matchValue, and matchCriteria keys will be removed in Sprout Import v2.0.0
         *
         * If the new 'params' syntax isn't used, use deprecated matchCriteria values if provided
         */
        $matchBy = SproutBaseImport::$app->importUtilities->getValueByKey('matchBy', $relatedSettings);
        $matchValue = SproutBaseImport::$app->importUtilities->getValueByKey('matchValue', $relatedSettings);

        if ($params === null && ($matchBy || $matchValue)) {
            if ($matchBy !== null) {
                Craft::$app->getDeprecator()->log('ElementImporter matchBy key', 'The “matchBy” key has been deprecated. Use “params” in place of “matchBy”, “matchValue”, and “matchCriteria”.');
            }

            if ($matchValue !== null) {
                Craft::$app->getDeprecator()->log('ElementImporter matchValue key', 'The “matchValue” key has been deprecated. Use “params” in place of “matchBy”, “matchValue”, and “matchCriteria”.');
            }

            $params = [
                $matchBy => $matchValue
            ];
        }

        if ($params) {
            return $importerClass->returnRelatedValue($params);
        }

        return null;
    }

    /**
     * @param bool $returnSavedElementIds
     *
     * @return array
     */
    public function getSavedResults($returnSavedElementIds = false): array
    {
        $result = [
            'saved' => count($this->savedElements),
            'updated' => count($this->updatedElements),
            'unsaved' => count($this->unsavedElements),
            'unsavedDetails' => $this->unsavedElements,
        ];

        return $returnSavedElementIds ? $this->savedElementIds : $result;
    }

    /**
     * Allows us to resolve relationships at the matrix field level
     *
     * @param $fields
     *
     * @return bool
     * @throws Throwable
     */
    public function resolveMatrixRelationships($fields): bool
    {
        foreach ($fields as $field => $blocks) {
            if (is_array($blocks) && count($blocks)) {
                foreach ($blocks as $block => $attributes) {
                    if (strpos($block, 'new') === 0 && isset($attributes['related'])) {
                        $blockFields = $attributes['fields'] ?? [];
                        $relatedFields = $attributes['related'];

                        $blockFields = $this->resolveRelationships($relatedFields, $blockFields);

                        if (!$blockFields) {
                            return false;
                        }

                        unset($fields[$field][$block]['related']);

                        if (empty($blockFields)) {
                            unset($fields[$field][$block]);
                        } else {
                            $fields[$field][$block]['fields'] = $blockFields;
                        }
                    }
                }
            }
        }

        return $fields;
    }

    /**
     * @return array
     */
    private function getElementDataKeys(): array
    {
        return [
            '@model', 'attributes', 'content', 'settings'
        ];
    }
}
