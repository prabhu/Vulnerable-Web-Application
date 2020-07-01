<?php /** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutbasefields\migrations;

use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;

/**
 * m190524_000000_add_dynamic_rendering_and_standalone_predefined_date_field migration.
 */
class m190524_000000_add_dynamic_rendering_and_standalone_predefined_date_field extends Migration
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

            $contentColumnType = $settings['contentColumnType'] ?? 'text';
            $outputTextarea = $settings['outputTextarea'] ?? null;

            unset(
                $settings['contentColumnType'],
                $settings['outputTextarea']
            );

            $type = $predefinedField['type'];

            if ($contentColumnType === 'datetime') {
                // Migrate Predefined dates to new field type:
                // 'barrelstrength\sproutfields\fields\PredefinedDate';
                $type = 'barrelstrength\sproutfields\fields\PredefinedDate';
            } else {
                // For existing Predefined Text fields:
                // Remove 'contentColumnType'. Now handled by 'outputFormat'.
                // Remove 'outputTextarea'. Now handled by 'outputFormat'.
                $settings['outputFormat'] = $outputTextarea == '1' ? 'textarea' : 'singleline';

                // Add 'renderDynamically' setting
                $settings['renderDynamically'] = false;
            }

            // Resave the field with updated settings
            $this->update('{{%fields}}', [
                'type' => $type,
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
        echo "m190524_000000_add_dynamic_rendering_and_standalone_predefined_date_field cannot be reverted.\n";

        return false;
    }
}
