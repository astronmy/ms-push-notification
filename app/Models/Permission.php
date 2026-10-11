<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

#[Fillable(['module', 'action', 'slug', 'label'])]
class Permission extends Model
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    /**
     * @return Collection<string, Collection<int, self>>
     */
    public static function groupedByModule(): Collection
    {
        return static::query()
            ->orderBy('module')
            ->orderBy('action')
            ->get()
            ->groupBy('module');
    }
}
