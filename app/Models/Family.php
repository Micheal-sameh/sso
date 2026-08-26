<?php

namespace App\Models;

use App\Enums\FamilyRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Family extends Model
{
    protected $appends = [
        'display_name',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function father(): HasOne
    {
        return $this->hasOne(User::class)->where('family_role', FamilyRole::Father);
    }

    public function mother(): HasOne
    {
        return $this->hasOne(User::class)->where('family_role', FamilyRole::Mother);
    }

    public function children(): HasMany
    {
        return $this->hasMany(User::class)->where('family_role', FamilyRole::Child);
    }

    /**
     * A family has no name of its own — it's identified by its members:
     * father & mother if both are set, whichever of the two is present,
     * or the first child otherwise (e.g. a lone member with no parents yet).
     */
    public function getDisplayNameAttribute(): string
    {
        $this->loadMissing('father', 'mother', 'children');

        if ($this->father !== null && $this->mother !== null) {
            return "{$this->father->name} & {$this->mother->name}";
        }

        if ($this->father !== null) {
            return $this->father->name;
        }

        if ($this->mother !== null) {
            return $this->mother->name;
        }

        return $this->children->first()?->name ?? __('messages.family_no_members');
    }
}
