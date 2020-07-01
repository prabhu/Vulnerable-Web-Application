<?php

namespace barrelstrength\sproutbaseimport\importers\fields;

use barrelstrength\sproutbaseimport\base\FieldImporter;
use barrelstrength\sproutbaseimport\SproutBaseImport;
use craft\elements\Asset;
use Craft;
use craft\fields\Assets as AssetsField;
use Exception;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class Assets extends FieldImporter
{
    /**
     * @return string
     */
    public function getModelName(): string
    {
        return AssetsField::class;
    }

    /**
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getSeedSettingsHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('sprout-base-import/settings/seed-defaults/assets/settings', [
            'settings' => $this->seedSettings['fields']['assets'] ?? []
        ]);
    }

    /**
     * @return array|bool|mixed|null
     * @throws Exception
     */
    public function getMockData()
    {
        $settings = $this->model->settings;

        $relatedMin = 1;
        $relatedMax = 3;

        $assetSettings = $this->seedSettings['fields']['assets'] ?? null;

        if ($assetSettings) {
            $relatedMin = $assetSettings['relatedMin'] ?: $relatedMin;
            $relatedMax = $assetSettings['relatedMax'] ?: $relatedMax;
        }

        $relatedMax = SproutBaseImport::$app->fieldImporter->getLimit($settings['limit'], $relatedMax);

        $mockDataSettings = [
            'fieldName' => $this->model->name,
            'required' => $this->model->required,
            'relatedMin' => $relatedMin,
            'relatedMax' => $relatedMax
        ];

        if (empty($settings['sources'])) {
            SproutBaseImport::info(Craft::t('sprout-base-import', 'Unable to generate Mock Data for relations field: {fieldName}. No Sources found.', [
                'fieldName' => $this->model->name
            ]));
            return null;
        }

        $sources = $settings['sources'];

        $sourceIds = SproutBaseImport::$app->fieldImporter->getElementGroupIds($sources);

        $attributes = null;

        if ($sources != '*') {
            $attributes = [
                'volumeId' => $sourceIds
            ];
        }

        $assetElement = new Asset();

        $elementIds = SproutBaseImport::$app->fieldImporter->getMockRelations($assetElement, $attributes, $mockDataSettings);

        return $elementIds;
    }
}
