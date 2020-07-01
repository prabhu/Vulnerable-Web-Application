<?php

namespace barrelstrength\sproutbasefields\migrations;

use craft\db\Migration;
use craft\db\Query;
use craft\db\Table;
use yii\base\NotSupportedException;

/**
 * m200102_000000_update_empty_phone_fields_to_null migration.
 */
class m200102_000000_update_empty_phone_fields_to_null extends Migration
{
    /**
     * @return bool
     * @throws NotSupportedException
     */
    public function safeUp(): bool
    {
        $oldEmptyPhoneFieldValue = '{"country":"US","phone":""}';
        /** @noinspection ClassConstantCanBeUsedInspection */
        $sproutFieldsPhoneFieldClass = 'barrelstrength\sproutfields\fields\Phone';
        /** @noinspection ClassConstantCanBeUsedInspection */
        $sproutFormsPhoneFieldClass = 'barrelstrength\sproutforms\fields\formfields\Phone';

        // SPROUT FIELDS

        // Get all Name fields from content table (Craft / Sprout Forms)
        $nameFieldTypes = (new Query())
            ->select(['id', 'handle', 'settings', 'type'])
            ->from([Table::FIELDS])
            ->where(['type' => $sproutFieldsPhoneFieldClass])
            ->all();

        // Update every Name Column that matches a blank name JSON string and set it to null
        foreach ($nameFieldTypes as $field) {
            $columnName = 'field_'.$field['handle'];
            if (!$this->db->columnExists(Table::CONTENT, $columnName)) {
                continue;
            }

            $this->update(Table::CONTENT, [
                $columnName => null
            ], [
                $columnName => $oldEmptyPhoneFieldValue
            ], [], false);
        }

        if (!$this->db->tableExists('{{%sproutforms_forms}}')) {
            return true;
        }

        // SPROUT FORMS

        $forms = (new Query())
            ->select(['id', 'handle'])
            ->from(['{{%sproutforms_forms}}'])
            ->all();

        $sproutFormsNameFieldTypes = (new Query())
            ->select(['id', 'handle', 'settings', 'type'])
            ->from([Table::FIELDS])
            ->where(['type' => $sproutFormsPhoneFieldClass])
            ->all();

        foreach ($forms as $form) {
            $contentTable = '{{%sproutformscontent_'.$form['handle'].'}}';
            if (!$this->db->tableExists($contentTable)) {
                continue;
            }

            foreach ($sproutFormsNameFieldTypes as $field) {
                $columnName = 'field_'.$field['handle'];
                if (!$this->db->columnExists($contentTable, $columnName)) {
                    continue;
                }

                $this->update($contentTable, [
                    $columnName => null
                ], [
                    $columnName => $oldEmptyPhoneFieldValue
                ], [], false);
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200102_000000_update_empty_phone_fields_to_null cannot be reverted.\n";

        return false;
    }
}
