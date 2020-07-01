<?php

namespace barrelstrength\sproutbaseimport\importers\fields;

use barrelstrength\sproutbaseimport\base\FieldImporter;
use barrelstrength\sproutbaseimport\SproutBaseImport;
use craft\elements\User;
use craft\fields\Users as UsersField;
use Craft;
use Exception;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class Users extends FieldImporter
{
    /**
     * @return string
     */
    public function getModelName(): string
    {
        return UsersField::class;
    }

    /**
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getSeedSettingsHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('sprout-base-import/settings/seed-defaults/users/settings', [
            'settings' => $this->seedSettings['fields']['users'] ?? []
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

        if (isset($this->seedSettings['fields'])) {
            $relatedMin = $this->seedSettings['fields']['assets']['relatedMin'] ?: $relatedMin;
            $relatedMax = $this->seedSettings['fields']['assets']['relatedMax'] ?: $relatedMax;
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

        $groupIds = SproutBaseImport::$app->fieldImporter->getElementGroupIds($sources);
        $attributes = null;

        if ($sources != '*') {
            $attributes = [
                'groupIds' => $groupIds
            ];
        }

        $userElement = new User();

        $elementIds = SproutBaseImport::$app->fieldImporter->getMockRelations($userElement, $attributes, $mockDataSettings);

        return $elementIds;
    }
}
