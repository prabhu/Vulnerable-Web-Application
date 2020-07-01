<?php

namespace barrelstrength\sproutbaseimport\queue\jobs;

use barrelstrength\sproutbaseimport\SproutBaseImport;
use craft\queue\BaseJob;
use Craft;
use Throwable;

class Seed extends BaseJob
{
    /**
     * @var $seedJob
     */
    public $seedJob;

    /**
     * @inheritdoc
     *
     * @param $queue
     *
     * @throws Throwable
     */
    public function execute($queue)
    {
        $seedJob = $this->seedJob;

        SproutBaseImport::$app->seed->runSeed($seedJob);
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return Craft::t('sprout-import', 'Seeding Data.');
    }
}
