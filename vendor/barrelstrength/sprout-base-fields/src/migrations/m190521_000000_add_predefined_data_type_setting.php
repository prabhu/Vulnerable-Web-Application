<?php /** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutbasefields\migrations;

use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;

/**
 * m190521_000000_add_predefined_data_type_setting migration.
 */
class m190521_000000_add_predefined_data_type_setting extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $type = 'barrelstrength\sproutfields\fields\Predefined';

        $predefinedFields = (new Query())
            ->select('*')
            ->from('{{%fields}}')
            ->where([
                'type' => $type
            ])
            ->all();

        foreach ($predefinedFields as $predefinedField) {
            $settings = Json::decode($predefinedField['settings']);

            if (!isset($settings['contentColumnType'])) {
                $settings['contentColumnType'] = 'text';
            }

            // Save the new settings
            $this->update('{{%fields}}', [
                'settings' => Json::encode($settings)
            ],
                ['id' => $predefinedField['id']], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190521_000000_add_predefined_data_type_setting cannot be reverted.\n";

        return false;
    }
}
