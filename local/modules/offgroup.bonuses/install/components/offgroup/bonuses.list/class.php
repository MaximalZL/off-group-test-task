<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UI\PageNavigation;
use Offgroup\Bonuses\BonusTable;

Loc::loadMessages(__FILE__);

class OffgroupBonusesListComponent extends CBitrixComponent
{
    private const GRID_ID = 'OFFGROUP_BONUSES_GRID';

    public function executeComponent(): void
    {
        global $USER;

        if (!$USER || !$USER->IsAuthorized()) {
            ShowError(Loc::getMessage('OFFGROUP_BONUSES_COMPONENT_AUTH_ERROR'));
            return;
        }

        if (!Loader::includeModule('offgroup.bonuses')) {
            ShowError(Loc::getMessage('OFFGROUP_BONUSES_COMPONENT_MODULE_ERROR'));
            return;
        }

        $employeeId = (int)$USER->GetID();
        $gridOptions = new GridOptions(self::GRID_ID);
        $sorting = $gridOptions->GetSorting([
            'sort' => ['ACCRUED_AT' => 'DESC'],
            'vars' => ['by' => 'by', 'order' => 'order'],
        ]);
        $navParams = $gridOptions->GetNavParams(['nPageSize' => 20]);

        $navigation = new PageNavigation(self::GRID_ID);
        $navigation->allowAllRecords(true)
            ->setPageSize((int)$navParams['nPageSize'])
            ->initFromUri();

        $result = BonusTable::getList([
            'select' => [
                'ID',
                'EMPLOYEE_ID',
                'ACCRUED_AT',
                'BONUS_AMOUNT',
                'REASON',
                'EMPLOYEE_NAME' => 'EMPLOYEE.NAME',
                'EMPLOYEE_LAST_NAME' => 'EMPLOYEE.LAST_NAME',
                'EMPLOYEE_SECOND_NAME' => 'EMPLOYEE.SECOND_NAME',
                'EMPLOYEE_LOGIN' => 'EMPLOYEE.LOGIN',
            ],
            'filter' => ['=EMPLOYEE_ID' => $employeeId],
            'order' => $this->normalizeSort($sorting['sort']),
            'offset' => $navigation->getOffset(),
            'limit' => $navigation->getLimit(),
            'count_total' => true,
        ]);

        $totalCount = $result->getCount();
        $navigation->setRecordCount($totalCount);

        $this->arResult['GRID_ID'] = self::GRID_ID;
        $this->arResult['COLUMNS'] = $this->getColumns();
        $this->arResult['ROWS'] = $this->buildRows($result->fetchAll());
        $this->arResult['NAV_OBJECT'] = $navigation;
        $this->arResult['TOTAL_ROWS_COUNT'] = $totalCount;
        $this->arResult['PAGE_SIZES'] = [
            ['NAME' => '10', 'VALUE' => '10'],
            ['NAME' => '20', 'VALUE' => '20'],
            ['NAME' => '50', 'VALUE' => '50'],
        ];

        $this->includeComponentTemplate();
    }

    private function getColumns(): array
    {
        return [
            ['id' => 'EMPLOYEE', 'name' => Loc::getMessage('OFFGROUP_BONUSES_GRID_EMPLOYEE'), 'sort' => 'EMPLOYEE_ID', 'default' => true],
            ['id' => 'ACCRUED_AT', 'name' => Loc::getMessage('OFFGROUP_BONUSES_GRID_ACCRUED_AT'), 'sort' => 'ACCRUED_AT', 'default' => true],
            ['id' => 'BONUS_AMOUNT', 'name' => Loc::getMessage('OFFGROUP_BONUSES_GRID_AMOUNT'), 'sort' => 'BONUS_AMOUNT', 'default' => true],
            ['id' => 'REASON', 'name' => Loc::getMessage('OFFGROUP_BONUSES_GRID_REASON'), 'default' => true],
        ];
    }

    private function buildRows(array $items): array
    {
        $rows = [];

        foreach ($items as $item) {
            $rows[] = [
                'id' => (int)$item['ID'],
                'columns' => [
                    'EMPLOYEE' => htmlspecialcharsbx($this->formatEmployee($item)),
                    'ACCRUED_AT' => htmlspecialcharsbx($this->formatDateTime($item['ACCRUED_AT'] ?? null)),
                    'BONUS_AMOUNT' => htmlspecialcharsbx(number_format((float)$item['BONUS_AMOUNT'], 2, '.', ' ')),
                    'REASON' => htmlspecialcharsbx((string)$item['REASON']),
                ],
            ];
        }

        return $rows;
    }

    private function normalizeSort(array $sort): array
    {
        $allowed = ['EMPLOYEE_ID', 'ACCRUED_AT', 'BONUS_AMOUNT'];
        $normalized = [];

        foreach ($sort as $field => $direction) {
            if (in_array($field, $allowed, true)) {
                $normalized[$field] = mb_strtoupper((string)$direction) === 'ASC' ? 'ASC' : 'DESC';
            }
        }

        return $normalized ?: ['ACCRUED_AT' => 'DESC'];
    }

    private function formatEmployee(array $item): string
    {
        $userFields = [
            'NAME' => (string)($item['EMPLOYEE_NAME'] ?? ''),
            'LAST_NAME' => (string)($item['EMPLOYEE_LAST_NAME'] ?? ''),
            'SECOND_NAME' => (string)($item['EMPLOYEE_SECOND_NAME'] ?? ''),
            'LOGIN' => (string)($item['EMPLOYEE_LOGIN'] ?? ''),
        ];

        $name = trim((string)\CUser::FormatName(\CSite::GetNameFormat(false), $userFields, true, false));

        return $name !== '' ? $name : $userFields['LOGIN'];
    }

    private function formatDateTime($value): string
    {
        if (!$value instanceof DateTime) {
            return '';
        }

        return FormatDate('d.m.Y H:i:s', $value->getTimestamp());
    }
}
