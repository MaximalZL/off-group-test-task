<?php

use Bitrix\Main\EventManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class offgroup_bonuses extends CModule
{
    public $MODULE_ID = 'offgroup.bonuses';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $PARTNER_NAME;
    public $PARTNER_URI;

    public function __construct()
    {
        $versionInfo = [];
        include __DIR__ . '/version.php';

        if (is_array($arModuleVersion)) {
            $versionInfo = $arModuleVersion;
        }

        $this->MODULE_VERSION = $versionInfo['VERSION'] ?? '1.0.0';
        $this->MODULE_VERSION_DATE = $versionInfo['VERSION_DATE'] ?? '2026-03-15 00:00:00';
        $this->MODULE_NAME = Loc::getMessage('OFFGROUP_BONUSES_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('OFFGROUP_BONUSES_MODULE_DESCRIPTION');
        $this->PARTNER_NAME = 'Off Group';
        $this->PARTNER_URI = 'https://off-group.example';
    }

    public function DoInstall(): void
    {
        global $APPLICATION;

        if (!IsModuleInstalled('crm')) {
            $APPLICATION->ThrowException(Loc::getMessage('OFFGROUP_BONUSES_INSTALL_ERROR_CRM'));
            return;
        }

        if (!IsModuleInstalled('rest')) {
            $APPLICATION->ThrowException(Loc::getMessage('OFFGROUP_BONUSES_INSTALL_ERROR_REST'));
            return;
        }

        ModuleManager::registerModule($this->MODULE_ID);
        $this->InstallFiles();
        $this->InstallDB();
        $this->InstallEvents();
    }

    public function DoUninstall(): void
    {
        $this->UnInstallEvents();
        $this->UnInstallDB();
        $this->UnInstallFiles();
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    public function InstallDB(): void
    {
        if (!ModuleManager::isModuleInstalled($this->MODULE_ID)) {
            return;
        }

        require_once __DIR__ . '/../include.php';

        $connection = \Bitrix\Main\Application::getConnection();
        $tableName = \Offgroup\Bonuses\BonusTable::getTableName();

        if (!$connection->isTableExists($tableName)) {
            \Offgroup\Bonuses\BonusTable::getEntity()->createDbTable();
        }
    }

    public function UnInstallDB(): void
    {
        require_once __DIR__ . '/../include.php';

        $connection = \Bitrix\Main\Application::getConnection();
        $tableName = \Offgroup\Bonuses\BonusTable::getTableName();

        if ($connection->isTableExists($tableName)) {
            $connection->dropTable($tableName);
        }
    }

    public function InstallEvents(): void
    {
        $eventManager = EventManager::getInstance();

        $eventManager->registerEventHandler(
            'crm',
            'OnAfterCrmDealUpdate',
            $this->MODULE_ID,
            \Offgroup\Bonuses\Integration\CrmDealBonusHandler::class,
            'onAfterDealUpdate'
        );

        $eventManager->registerEventHandler(
            'main',
            'OnBuildGlobalMenu',
            $this->MODULE_ID,
            \Offgroup\Bonuses\Integration\MenuBuilder::class,
            'onBuildGlobalMenu'
        );

        $eventManager->registerEventHandler(
            'rest',
            'OnRestServiceBuildDescription',
            $this->MODULE_ID,
            \Offgroup\Bonuses\Rest\BonusRestService::class,
            'onRestServiceBuildDescription'
        );
    }

    public function UnInstallEvents(): void
    {
        $eventManager = EventManager::getInstance();

        $eventManager->unRegisterEventHandler(
            'crm',
            'OnAfterCrmDealUpdate',
            $this->MODULE_ID,
            \Offgroup\Bonuses\Integration\CrmDealBonusHandler::class,
            'onAfterDealUpdate'
        );

        $eventManager->unRegisterEventHandler(
            'main',
            'OnBuildGlobalMenu',
            $this->MODULE_ID,
            \Offgroup\Bonuses\Integration\MenuBuilder::class,
            'onBuildGlobalMenu'
        );

        $eventManager->unRegisterEventHandler(
            'rest',
            'OnRestServiceBuildDescription',
            $this->MODULE_ID,
            \Offgroup\Bonuses\Rest\BonusRestService::class,
            'onRestServiceBuildDescription'
        );
    }

    public function InstallFiles(): void
    {
        CopyDirFiles(
            __DIR__ . '/components/offgroup',
            $_SERVER['DOCUMENT_ROOT'] . '/local/components/offgroup',
            true,
            true
        );

        CopyDirFiles(
            __DIR__ . '/public',
            $_SERVER['DOCUMENT_ROOT'],
            true,
            true
        );
    }

    public function UnInstallFiles(): void
    {
        DeleteDirFilesEx('/local/components/offgroup/bonuses.list');
        DeleteDirFilesEx('/offgroup/bonuses');
    }
}