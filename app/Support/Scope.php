<?php

namespace App\Support;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Builder;

/**
 * Filter organisasi aktif (pemilih organisasi di top bar). Null = semua organisasi.
 */
class Scope
{
    private static ?array $ids = null;
    private static bool $resolved = false;

    public static function org(): ?Organization
    {
        $id = session('org_id');

        return $id ? Organization::find($id) : null;
    }

    /** Id organisasi aktif beserta anak perusahaannya, atau null bila semua. */
    public static function ids(): ?array
    {
        if (! self::$resolved) {
            $org = self::org();
            self::$ids = $org?->familyIds();
            self::$resolved = true;
        }

        return self::$ids;
    }

    /** Id organisasi induk aktif saja (untuk objek yang dimiliki organisasi induk, mis. CI). */
    public static function rootId(): ?int
    {
        return session('org_id');
    }

    public static function apply(Builder $q, string $column = 'organization_id', bool $family = true): Builder
    {
        $ids = $family ? self::ids() : (self::rootId() ? [self::rootId()] : null);

        return $ids ? $q->whereIn($q->getModel()->getTable().'.'.$column, $ids) : $q;
    }
}
