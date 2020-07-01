<?php

namespace barrelstrength\sproutbaseimport\importers\fields;

use barrelstrength\sproutbaseimport\base\FieldImporter;
use barrelstrength\sproutbaseimport\SproutBaseImport;
use craft\elements\Entry;
use craft\fields\Entries as EntriesField;
use Craft;
use Exception;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class Entries extends FieldImporter
{
    /**
     * @return string
     */
    public function getModelName(): string
    {
        return EntriesField::class;
    }

    /**
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getSeedSettingsHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('sprout-base-import/settings/seed-defaults/entries/settings', [
            'settings' => $this->seedSettings['fields']['entries'] ?? []
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

        $entrySettings = $this->seedSettings['fields']['entries'] ?? null;

        if ($entrySettings) {
            $relatedMin = $entrySettings['relatedMin'] ?: $relatedMin;
            $relatedMax = $entrySettings['relatedMax'] ?: $relatedMax;
        }

        $relatedMax = SproutBaseImport::$app->fieldImporter->getLimit($settings['limit'], $relatedMax);

        $mockDataSettings = [
            'fieldName' => $this->model->name,
            'required' => $this->model->required,
            'relatedMin' => $relatedMin,
            'relatedMax' => $relatedMax
        ];

        if (!isset($settings['sources'])) {
            SproutBaseImport::info(Craft::t('sprout-base-import', 'Unable to generate Mock Data for relations field: {fieldName}. No Sources found.', [
                'fieldName' => $this->model->name
            ]));
            return null;
        }

        $sources = $settings['sources'];

        $sectionIds = SproutBaseImport::$app->fieldImporter->getElementGroupIds($sources);

        $attributes = null;

        if ($sources != '*') {
            $attributes = [
                'sectionId' => $sectionIds
            ];
        }

        $entryElement = new Entry();

        $elementIds = SproutBaseImport::$app->fieldImporter->getMockRelations($entryElement, $attributes, $mockDataSettings);

        return $elementIds;
    }
}
