<?php

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';

global $APPLICATION, $USER;

if (!$USER || !$USER->IsAuthorized()) {
    LocalRedirect('/auth/');
}

$APPLICATION->SetTitle('Мои бонусы');
?>
<?php
$APPLICATION->IncludeComponent(
    'offgroup:bonuses.list',
    '',
    []
);
?>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>
