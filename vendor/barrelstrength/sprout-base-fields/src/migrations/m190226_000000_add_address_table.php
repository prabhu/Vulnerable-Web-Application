<?php /** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutbasefields\migrations;

use craft\db\Migration;

/**
 * m190226_000000_add_address_table migration.
 */
class m190226_000000_add_address_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $tableName = '{{%sprout_addresses}}';

        if (!$this->getDb()->tableExists($tableName)) {
            $this->createTable($tableName, [
                'id' => $this->primaryKey(),
                'elementId' => $this->integer(),
                'siteId' => $this->integer(),
                'fieldId' => $this->integer(),
                'countryCode' => $this->string(),
                'administrativeAreaCode' => $this->string(),
                'locality' => $this->string(),
                'dependentLocality' => $this->string(),
                'postalCode' => $this->string(),
                'sortingCode' => $this->string(),
                'address1' => $this->string(),
                'address2' => $this->string(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190226_000000_add_address_table cannot be reverted.\n";

        return false;
    }
}
