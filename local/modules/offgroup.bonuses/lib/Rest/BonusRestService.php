<?php

namespace Offgroup\Bonuses\Rest;

use Bitrix\Rest\RestException;
use Offgroup\Bonuses\Service\BonusService;

class BonusRestService
{
    public static function onRestServiceBuildDescription(): array
    {
        return [
            'offgroup.bonuses' => [
                'offgroup.bonuses.gettotal' => [self::class, 'getTotal'],
            ],
        ];
    }

    public static function getTotal(array $query, $n, \CRestServer $server): array
    {
        $employeeId = (int)($query['employee_id'] ?? 0);

        if ($employeeId <= 0) {
            throw new RestException(
                'The employee_id parameter is required.',
                'INVALID_PARAMETER',
                \CRestServer::STATUS_WRONG_REQUEST
            );
        }

        return [
            'total' => (new BonusService())->getEmployeeTotal($employeeId),
        ];
    }
}