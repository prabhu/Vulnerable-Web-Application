<?php

namespace barrelstrength\sproutbasefields\migrations;

use craft\db\Migration;
use craft\db\Query;
use craft\db\Table;
use craft\helpers\Json;

/**
 * m191218_000000_remove_addressHelper_from_settings migration.
 */
class m191218_000000_remove_addressHelper_from_settings extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        /** @noinspection ClassConstantCanBeUsedInspection */
        $addressFieldTypes = [
            'barrelstrength\sproutfields\fields\Address',
            'barrelstrength\sproutforms\fields\formfields\Address'
        ];

        $fields = (new Query())
            ->select(['id', 'settings', 'type'])
            ->from([Table::FIELDS])
            ->where(['in', 'type', $addressFieldTypes])
            ->all();

        foreach ($fields as $field) {

            $settings = Json::decodeIfJson($field['settings']) ?: [];
            unset($settings['addressHelper'], $settings['value']);
            $settings = Json::encode($settings);

            $this->update(Table::FIELDS, ['settings' => $settings], ['id' => $field['id']], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m191218_000000_remove_addressHelper_from_settings cannot be reverted.\n";

        return false;
    }
}
