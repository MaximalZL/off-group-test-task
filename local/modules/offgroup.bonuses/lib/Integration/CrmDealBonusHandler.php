<?php

namespace Offgroup\Bonuses\Integration;

use Offgroup\Bonuses\Service\BonusService;

class CrmDealBonusHandler
{
    public static function onAfterDealUpdate(array &$fields): void
    {
        $dealId = (int)($fields['ID'] ?? 0);

        if ($dealId <= 0) {
            return;
        }

        try {
            (new BonusService())->accrueForDeal($dealId);
        } catch (\Throwable $exception) {
            AddMessage2Log(
                sprintf('[offgroup.bonuses] Deal bonus accrual failed for deal #%d: %s', $dealId, $exception->getMessage())
            );
        }
    }
}
