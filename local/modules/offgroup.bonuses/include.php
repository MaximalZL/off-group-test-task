<?php

use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses(
    null,
    [
        'Offgroup\\Bonuses\\BonusTable' => '/local/modules/offgroup.bonuses/lib/BonusTable.php',
        'Offgroup\\Bonuses\\Service\\BonusService' => '/local/modules/offgroup.bonuses/lib/Service/BonusService.php',
        'Offgroup\\Bonuses\\Integration\\CrmDealBonusHandler' => '/local/modules/offgroup.bonuses/lib/Integration/CrmDealBonusHandler.php',
        'Offgroup\\Bonuses\\Integration\\MenuBuilder' => '/local/modules/offgroup.bonuses/lib/Integration/MenuBuilder.php',
        'Offgroup\\Bonuses\\Rest\\BonusRestService' => '/local/modules/offgroup.bonuses/lib/Rest/BonusRestService.php',
    ]
);