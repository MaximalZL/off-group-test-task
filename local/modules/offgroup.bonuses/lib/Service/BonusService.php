<?php

namespace Offgroup\Bonuses\Service;

use Bitrix\Crm\Category\DealCategory;
use Bitrix\Crm\PhaseSemantics;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Offgroup\Bonuses\BonusTable;

class BonusService
{
    private const BONUS_PERCENT = 0.01;
    private const SOURCE_TYPE_DEAL = 'DEAL';

    public function addBonus(
        int $employeeId,
        float $bonusAmount,
        string $reason,
        ?string $sourceEntityType = null,
        ?int $sourceEntityId = null
    ): int {
        $bonusAmount = round($bonusAmount, 2);

        if ($employeeId <= 0 || $bonusAmount <= 0) {
            throw new \InvalidArgumentException('Employee ID and bonus amount must be positive.');
        }

        if ($sourceEntityType !== null && $sourceEntityId !== null) {
            $existingBonus = BonusTable::getRow([
                'select' => ['ID'],
                'filter' => [
                    '=EMPLOYEE_ID' => $employeeId,
                    '=SOURCE_ENTITY_TYPE' => $sourceEntityType,
                    '=SOURCE_ENTITY_ID' => $sourceEntityId,
                ],
            ]);

            if ($existingBonus) {
                return (int)$existingBonus['ID'];
            }
        }

        $result = BonusTable::add([
            'EMPLOYEE_ID' => $employeeId,
            'BONUS_AMOUNT' => $bonusAmount,
            'REASON' => $reason,
            'SOURCE_ENTITY_TYPE' => $sourceEntityType,
            'SOURCE_ENTITY_ID' => $sourceEntityId,
        ]);

        if (!$result->isSuccess()) {
            throw new \RuntimeException(implode('; ', $result->getErrorMessages()));
        }

        return (int)$result->getId();
    }

    public function accrueForDeal(int $dealId): ?int
    {
        if ($dealId <= 0) {
            return null;
        }

        if (!Loader::includeModule('crm')) {
            throw new \RuntimeException('CRM module is not available.');
        }

        $deal = \CCrmDeal::GetByID($dealId, false);

        if (!$deal) {
            return null;
        }

        $categoryId = (int)($deal['CATEGORY_ID'] ?? 0);
        $stageSemantics = DealCategory::getStageSemantics((string)$deal['STAGE_ID'], $categoryId);

        if ($stageSemantics !== PhaseSemantics::SUCCESS) {
            return null;
        }

        $employeeId = (int)($deal['ASSIGNED_BY_ID'] ?? 0);
        $opportunity = (float)($deal['OPPORTUNITY'] ?? 0);
        $bonusAmount = round($opportunity * self::BONUS_PERCENT, 2);

        if ($employeeId <= 0 || $bonusAmount <= 0) {
            return null;
        }

        $reason = sprintf('Бонус за закрытую сделку №%d', $dealId);

        return $this->addBonus($employeeId, $bonusAmount, $reason, self::SOURCE_TYPE_DEAL, $dealId);
    }

    public function getEmployeeTotal(int $employeeId): float
    {
        if ($employeeId <= 0) {
            return 0.0;
        }

        $row = BonusTable::getRow([
            'runtime' => [
                new ExpressionField('TOTAL', 'SUM(%s)', ['BONUS_AMOUNT']),
            ],
            'select' => ['TOTAL'],
            'filter' => ['=EMPLOYEE_ID' => $employeeId],
        ]);

        return round((float)($row['TOTAL'] ?? 0), 2);
    }
}