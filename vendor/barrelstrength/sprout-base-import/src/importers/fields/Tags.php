<?php

namespace barrelstrength\sproutbaseimport\importers\fields;

use barrelstrength\sproutbaseimport\base\FieldImporter;
use barrelstrength\sproutbaseimport\SproutBaseImport;
use craft\elements\Tag;
use craft\fields\Tags as TagsField;
use Craft;
use Exception;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class Tags extends FieldImporter
{
    /**
     * @return string
     */
    public function getModelName(): string
    {
        return TagsField::class;
    }

    /**
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getSeedSettingsHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('sprout-base-import/settings/seed-defaults/tags/settings', [
            'settings' => $this->seedSettings['fields']['tags'] ?? []
        ]);
    }

    /**
     * @return array|bool|mixed
     * @throws Exception
     */
    public function getMockData()
    {
        $settings = $this->model->settings;

        $relatedMin = 1;
        $relatedMax = 3;

        $tagSettings = $this->seedSettings['fields']['tags'] ?? null;

        if ($tagSettings) {
            $relatedMin = $tagSettings['relatedMin'] ?: $relatedMin;
            $relatedMax = $tagSettings['relatedMax'] ?: $relatedMax;
        }

        $relatedMax = SproutBaseImport::$app->fieldImporter->getLimit($settings['limit'], $relatedMax);

        $mockDataSettings = [
            'fieldName' => $this->model->name,
            'required' => $this->model->required,
            'relatedMin' => $relatedMin,
            'relatedMax' => $relatedMax
        ];

        if (empty($settings['source'])) {
            SproutBaseImport::info(Craft::t('sprout-base-import', 'Unable to generate Mock Data for relations field: {fieldName}. No Source found.', [
                'fieldName' => $this->model->name
            ]));
            return null;
        }

        $source = $settings['source'];

        $groupId = SproutBaseImport::$app->fieldImporter->getElementGroupId($source);

        $attributes = null;

        if ($source != '*') {
            $attributes = [
                'groupId' => $groupId
            ];
        }

        $tagElement = new Tag();

        $elementIds = SproutBaseImport::$app->fieldImporter->getMockRelations($tagElement, $attributes, $mockDataSettings);

        return $elementIds;
    }
}
