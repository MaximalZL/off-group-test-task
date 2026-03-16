<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

global $APPLICATION;

$APPLICATION->IncludeComponent(
    'bitrix:main.ui.grid',
    '',
    [
        'GRID_ID' => $arResult['GRID_ID'],
        'COLUMNS' => $arResult['COLUMNS'],
        'ROWS' => $arResult['ROWS'],
        'NAV_OBJECT' => $arResult['NAV_OBJECT'],
        'TOTAL_ROWS_COUNT' => $arResult['TOTAL_ROWS_COUNT'],
        'PAGE_SIZES' => $arResult['PAGE_SIZES'],
        'AJAX_MODE' => 'N',
        'ALLOW_COLUMNS_SORT' => true,
        'ALLOW_SORT' => true,
        'ALLOW_PIN_HEADER' => true,
        'SHOW_ROW_CHECKBOXES' => false,
        'SHOW_ACTION_PANEL' => false,
        'SHOW_GRID_SETTINGS_MENU' => true,
        'SHOW_NAVIGATION_PANEL' => true,
        'SHOW_PAGINATION' => true,
        'SHOW_SELECTED_COUNTER' => false,
        'SHOW_TOTAL_COUNTER' => true,
        'SHOW_CHECK_ALL_CHECKBOXES' => false,
        'ENABLE_COLLAPSIBLE_ROWS' => false,
        'ALLOW_CONTEXT_MENU' => false,
    ]
);
