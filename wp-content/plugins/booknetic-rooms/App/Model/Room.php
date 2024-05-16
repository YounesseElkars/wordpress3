<?php

namespace BookneticAddon\Rooms\Model;

use BookneticApp\Providers\DB\Model;
use BookneticApp\Providers\DB\MultiTenant;

/**
 * @property-read int $id
 * @property int $tenant_id
 */
class Room extends Model
{
    use MultiTenant;

    public static function getFieldArray( $ids, $field ): array
    {
        $rows = self::select( $field )
            ->whereId( $ids )
            ->fetchAll();

        return array_column( $rows, $field );
    }
}
