<?php

declare(strict_types=1);

namespace App\Common\Doctrine\Schema;

use Doctrine\DBAL\Schema\PostgreSQLSchemaManager;

final class IgnoredIndexesSchemaManager extends PostgreSQLSchemaManager
{
    private const array IGNORED = [
        'location_name_lower_uniq' => true,
        'position_name_lower_uniq' => true,
        'service_type_name_lower_uniq' => true,
        'department_location_name_active_uniq' => true,
        'employee_assignment_active_uniq' => true,
        'shift_employee_active_uniq' => true,
        'work_object_identifier_active_uniq' => true,
        'work_order_open_by_location' => true,
        'task_active_by_location' => true,
    ];

    /**
     * Защищённый метод DBAL
     */
    protected function _getPortableTableIndexesList(array $rows, string $tableName): array
    {
        return array_diff_key(parent::_getPortableTableIndexesList($rows, $tableName), self::IGNORED);
    }
}
