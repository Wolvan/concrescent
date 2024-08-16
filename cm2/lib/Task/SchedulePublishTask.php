<?php

namespace {
    require_once __DIR__.'/../../../vendor/autoload.php';
    require_once __DIR__.'/../database/misc.php';
    require_once __DIR__ .'/../../config/config.php';
}

namespace App\Task {

    use App\Hook\CloudflareApi;

    readonly class SchedulePublishTask
    {
        public function __construct(
            private \cm_misc_db $miscDb,
            private CloudflareApi $cloudflareApi,
        ) {
        }

        public function onScheduleManualUpdate(): void
        {
            try {
                $this->cloudflareApi->purgeSchedule();
            } catch (\Throwable $e) {
                \error_log('Failed to execute task '. self::class. ': '. $e->getMessage());
            }
        }
    }
}
