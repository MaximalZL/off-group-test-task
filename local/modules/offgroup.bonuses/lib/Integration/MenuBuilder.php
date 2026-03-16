<?php

namespace Offgroup\Bonuses\Integration;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class MenuBuilder
{
    public static function onBuildGlobalMenu(array &$globalMenu, array &$moduleMenu): void
    {
        global $USER;

        if (!$USER || !$USER->IsAuthorized()) {
            return;
        }

        $moduleMenu[] = [
            'parent_menu' => 'global_menu_services',
            'section' => 'offgroup_bonuses',
            'sort' => 250,
            'text' => Loc::getMessage('OFFGROUP_BONUSES_MENU_TEXT'),
            'title' => Loc::getMessage('OFFGROUP_BONUSES_MENU_TITLE'),
            'url' => '/offgroup/bonuses/',
            'items_id' => 'menu_offgroup_bonuses',
            'items' => [],
        ];
    }
}
