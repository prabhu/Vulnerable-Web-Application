<?php

namespace barrelstrength\sproutbasereports\migrations;

use craft\db\Migration;
use craft\db\Query;
use yii\base\NotSupportedException;

/**
 * m190714_000000_add_context_column migration.
 */
class m190714_000000_add_context_column extends Migration
{
    /**
     * @inheritdoc
     *
     * @throws NotSupportedException
     */
    public function safeUp(): bool
    {
        $table = '{{%sproutreports_datasources}}';

        // Add a `viewContext` column
        if (!$this->db->columnExists($table, 'viewContext')) {
            $this->addColumn($table, 'viewContext', $this->string()->after('id'));
        }

        // Migrate data to the `viewContext` column, Remove the pluginHandle column
        if ($this->db->columnExists($table, 'pluginHandle')) {
            $dataSources = (new Query())
                ->select(['*'])
                ->from(['{{%sproutreports_datasources}}'])
                ->all();

            foreach ($dataSources as $dataSource) {

                switch ($dataSource['pluginHandle']) {
                    case 'sprout-reports':
                    case '':
                        $this->update($table, ['viewContext' => 'global'], ['id' => $dataSource['id']], [], false);
                        break;
                    case 'sprout-forms':
                        $this->update($table, ['viewContext' => 'sprout-forms'], ['id' => $dataSource['id']], [], false);
                        break;
                    default:
                        // Use the same handle
                        $this->update($table, ['viewContext' => $dataSource['pluginHandle']], ['id' => $dataSource['id']], [], false);
                        break;
                }
            }
            // @todo - remove the comment after we add minimum version required.
            #$this->dropColumn($table, 'pluginHandle');
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190714_000000_add_context_column cannot be reverted.\n";

        return false;
    }
}
