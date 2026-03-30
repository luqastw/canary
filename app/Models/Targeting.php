<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Targeting extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'flag_targeting';

    protected $fillable = [
        'flag_id',
        'group_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the flag that owns this targeting rule.
     */
    public function flag(): BelongsTo
    {
        return $this->belongsTo(Flag::class);
    }

    /**
     * Get the group that this targeting rule references.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
