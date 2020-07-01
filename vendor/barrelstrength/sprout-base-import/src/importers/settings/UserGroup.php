<?php

namespace barrelstrength\sproutbaseimport\importers\settings;

use barrelstrength\sproutbaseimport\base\SettingsImporter;
use Craft;
use craft\errors\WrongEditionException;
use craft\models\UserGroup as UserGroupModel;
use craft\records\UserGroup as UserGroupRecord;

class UserGroup extends SettingsImporter
{
    /**
     * @var bool
     */
    public $isNewSection;

    /**
     * @return string
     */
    public function getName(): string
    {
        return Craft::t('sprout-base-import', 'User Group');
    }

    /**
     * @return string
     */
    public function getModelName(): string
    {
        return UserGroupModel::class;
    }

    /**
     * @inheritdoc
     */
    public function getRecordName()
    {
        return UserGroupRecord::class;
    }

    /**
     * @param $id
     *
     * @return bool|mixed
     * @throws WrongEditionException
     */
    public function deleteById($id)
    {
        return Craft::$app->getUserGroups()->deleteGroupById($id);
    }

    /**
     * @return bool
     * @throws WrongEditionException
     */
    public function save()
    {
        $this->isNewSection = $this->model->id ? false : true;

        return Craft::$app->getUserGroups()->saveGroup($this->model);
    }

    public function returnRelatedValue($params)
    {
        $recordClass = $this->getRecordName();
        /**
         * @var $record UserGroupRecord
         */
        $record = new $recordClass();

        $records = $record::findAll($params);

        $ids = null;
        if ($records) {
            foreach ($records as $record) {
                $ids[] = $record->id;
            }
        }

        return $ids;
    }
}
