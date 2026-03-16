<?php

namespace Offgroup\Bonuses;

use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\Validator\Length;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\FloatField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;

class BonusTable extends DataManager
{
    public static function getTableName(): string
    {
        return 'offgroup_bonuses';
    }

    public static function getMap(): array
    {
        return [
            (new IntegerField('ID'))
                ->configurePrimary()
                ->configureAutocomplete(),
            (new IntegerField('EMPLOYEE_ID'))
                ->configureRequired(),
            (new DatetimeField('ACCRUED_AT'))
                ->configureRequired()
                ->configureDefaultValue(static fn() => new DateTime()),
            (new FloatField('BONUS_AMOUNT'))
                ->configureRequired(),
            (new TextField('REASON'))
                ->configureRequired(),
            (new StringField('SOURCE_ENTITY_TYPE'))
                ->configureSize(50)
                ->addValidator(new Length(null, 50)),
            (new IntegerField('SOURCE_ENTITY_ID')),
            (new DatetimeField('CREATED_AT'))
                ->configureRequired()
                ->configureDefaultValue(static fn() => new DateTime()),
            new ReferenceField(
                'EMPLOYEE',
                UserTable::class,
                Join::on('this.EMPLOYEE_ID', 'ref.ID')
            ),
        ];
    }
}
