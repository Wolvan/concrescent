<?php

namespace {
    require_once __DIR__.'/../../../vendor/autoload.php';
    require_once __DIR__.'/../database/misc.php';
    require_once __DIR__ .'/../../config/config.php';
}

namespace App\Task {

    use App\Hook\CloudflareApi;
    use App\Log\LogLibrary;

    readonly class SponsorPublishableTask
    {
        public function __construct(
            private \cm_misc_db $miscDb,
            private CloudflareApi $cloudflareApi,
            private LogLibrary   $log,
        ) {
        }

        public function onAttendeeManualUpdate(): void
        {
            global $cm_config;

            try {
                $nameCredit = $cm_config['extra_features']['sponsors']['nameCredit'] ?? null;
                $publishableCredit = $cm_config['extra_features']['sponsors']['publishableCredit'] ?? null;

                if ($nameCredit === null || $publishableCredit === null) {
                    return;
                }

                $sponsorsHash = md5(
                    json_encode(
                        $this->miscDb->getBadgeTypesFromQuestionAnswer(
                            $nameCredit,
                            $publishableCredit,
                        ),
                        JSON_THROW_ON_ERROR
                    )
                );

                $lastHash = \apcu_fetch(self::class . '::last_known_hash');
                $lastHash = $lastHash === false || !is_string($lastHash) ? false : $lastHash;

                if ($sponsorsHash === $lastHash) {
                    return;
                }

                \apcu_store(self::class . '::last_known_hash', $sponsorsHash);

                $this->cloudflareApi->purgeSponsors();
            } catch (\Throwable $e) {
                $this->log->system->error('Failed to execute task '. __METHOD__ .' : '. $e->getMessage());
            }
        }
    }
}
