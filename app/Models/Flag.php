<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy([TenantScope::class])]
class Flag extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'key',
        'name',
        'description',
        'is_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the flag.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the targeting rules for this flag.
     */
    public function targetingRules(): HasMany
    {
        return $this->hasMany(Targeting::class);
    }

    /**
     * Check if the flag has targeting rules.
     */
    public function hasTargeting(): bool
    {
        return $this->targetingRules()->exists();
    }

    /**
     * Get targeting rules with groups eager loaded.
     */
    public function targetingRulesWithGroups(): HasMany
    {
        return $this->targetingRules()->with('group');
    }
}
