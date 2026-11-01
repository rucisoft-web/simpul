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
    private static mixed $resolvedFor = false;

    public static function org(): ?Organization
    {
        $id = session('org_id');

        return $id ? Organization::find($id) : null;
    }

    /** Id organisasi aktif beserta anak perusahaannya, atau null bila semua. */
    public static function ids(): ?array
    {
        // Cache per organisasi aktif (pemilih bisa berubah dalam satu proses, mis. saat tes).
        if (self::$resolvedFor !== session('org_id')) {
            self::$ids = self::org()?->familyIds();
            self::$resolvedFor = session('org_id');
        }

        return self::$ids;
    }

    /** Id organisasi induk aktif saja (untuk objek yang dimiliki organisasi induk, mis. CI). */
    public static function rootId(): ?int
    {
        return session('org_id');
    }

    /**
     * Akun Tamu (demo) selalu terkunci ke satu perusahaan pilihannya: tidak ada tampilan
     * "Semua Organisasi" dan objek perusahaan lain tidak bisa dibuka lewat URL.
     */
    public static function locked(): bool
    {
        return (bool) auth()->user()?->isGuest();
    }

    /** Hentikan (404) bila objek milik organisasi di luar perusahaan aktif akun Tamu. */
    public static function ensure(?int $organizationId): void
    {
        if (self::locked() && $organizationId !== null) {
            abort_unless(in_array($organizationId, self::ids() ?? [], true), 404);
        }
    }

    /** Organisasi induk yang boleh tampil di daftar/pemilih (semua, atau hanya organisasi aktif). */
    public static function roots(): \Illuminate\Database\Eloquent\Collection
    {
        return Organization::whereNull('parent_id')->when(self::rootId(), fn ($q) => $q->where('id', self::rootId()))->orderBy('name')->get();
    }

    public static function apply(Builder $q, string $column = 'organization_id', bool $family = true): Builder
    {
        $ids = $family ? self::ids() : (self::rootId() ? [self::rootId()] : null);

        return $ids ? $q->whereIn($q->getModel()->getTable().'.'.$column, $ids) : $q;
    }
}
