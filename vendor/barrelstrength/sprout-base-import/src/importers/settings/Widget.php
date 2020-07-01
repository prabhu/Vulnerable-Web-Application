<?php

namespace barrelstrength\sproutbaseimport\importers\settings;

use barrelstrength\sproutbaseimport\base\SettingsImporter;
use barrelstrength\sproutbaseimport\models\importers\Widget as WidgetModel;
use barrelstrength\sproutbaseimport\SproutBaseImport;
use craft\records\Widget as WidgetRecord;
use Craft;
use craft\base\WidgetInterface;
use Throwable;

class Widget extends SettingsImporter
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return Craft::t('sprout-base-import', 'Widget');
    }

    /**
     * @return string
     */
    public function getModelName(): string
    {
        return WidgetModel::class;
    }

    /**
     * @inheritdoc
     */
    public function getRecord()
    {
        return WidgetRecord::class;
    }

    /**
     * @return WidgetInterface
     * @throws Throwable
     */
    public function save()
    {
        unset($this->rows['@model']);

        $dashboardService = Craft::$app->getDashboard();

        /**
         * @var $widget WidgetInterface
         */
        $widget = $dashboardService->saveWidget($dashboardService->createWidget($this->rows));

        if ($widget) {
            $this->model = $widget;
        } else {
            SproutBaseImport::error(Craft::t('sprout-base-import', 'Cannot save Widget: '.$widget::displayName()));
            SproutBaseImport::info($widget);
        }

        return $widget;
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function deleteById($id)
    {
        return Craft::$app->getDashboard()->deleteWidgetById($id);
    }
}
