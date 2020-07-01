<?php /** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutbasefields\migrations;

use CommerceGuys\Addressing\Subdivision\SubdivisionRepository;
use craft\db\Migration;
use craft\db\Query;

/**
 * m190313_000000_fix_non_abbreviation_administrative_codes migration.
 */
class m190313_000000_fix_non_abbreviation_administrative_codes extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $tableName = '{{%sprout_addresses}}';
        $oldAddressTableName = '{{%sproutfields_addresses}}';

        // Support two scenarios
        // 1. A user installed from scratch with the new sprout_addresses table name
        // 2. A user is upgrading and hasn't yet run the update_address_tables migration that renames to use sprout_addresses
        if ($this->db->tableExists($oldAddressTableName)) {
            $tableName = $oldAddressTableName;
        }

        $addresses = (new Query())
            ->select('*')
            ->from($tableName)
            ->all();

        $subdivisionRepository = new SubdivisionRepository();

        foreach ($addresses as $address) {
            $states = $subdivisionRepository->getAll([$address['countryCode']]);

            foreach ($states as $state) {
                if ($state->getName() == $address['administrativeAreaCode']) {
                    $stateCode = $state->getCode();

                    $this->update($tableName, [
                        'administrativeAreaCode' => $stateCode
                    ], [
                        'id' => $address['id']
                    ], [], false);
                }
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190313_000000_fix_non_abbreviation_administrative_codes cannot be reverted.\n";

        return false;
    }
}
