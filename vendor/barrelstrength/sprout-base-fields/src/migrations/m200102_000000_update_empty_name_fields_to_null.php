<?php

namespace barrelstrength\sproutbasefields\migrations;

use craft\db\Migration;
use craft\db\Query;
use craft\db\Table;
use yii\base\NotSupportedException;

/**
 * m200102_000000_update_empty_name_fields_to_null migration.
 */
class m200102_000000_update_empty_name_fields_to_null extends Migration
{
    /**
     * @return bool
     * @throws NotSupportedException
     */
    public function safeUp(): bool
    {
        $oldEmptyNameFieldValue = '{"fullName":null,"prefix":null,"firstName":null,"middleName":null,"lastName":null,"suffix":null}';
        /** @noinspection ClassConstantCanBeUsedInspection */
        $sproutFieldsNameFieldClass = 'barrelstrength\sproutfields\fields\Name';
        /** @noinspection ClassConstantCanBeUsedInspection */
        $sproutFormsNameFieldClass = 'barrelstrength\sproutforms\fields\formfields\Name';

        // SPROUT FIELDS

        // Get all Name fields from content table (Craft / Sprout Forms)
        $nameFieldTypes = (new Query())
            ->select(['id', 'handle', 'settings', 'type'])
            ->from([Table::FIELDS])
            ->where(['type' => $sproutFieldsNameFieldClass])
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
                $columnName => $oldEmptyNameFieldValue
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
            ->where(['type' => $sproutFormsNameFieldClass])
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
                    $columnName => $oldEmptyNameFieldValue
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
        echo "m200102_000000_update_empty_name_fields_to_null cannot be reverted.\n";

        return false;
    }
}
