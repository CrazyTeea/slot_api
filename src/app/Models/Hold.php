<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @method static Builder<static>|Hold newModelQuery()
 * @method static Builder<static>|Hold newQuery()
 * @method static Builder<static>|Hold query()
 * @property int $id
 * @property int $slot_id
 * @property string $status
 * @property string $idempotency_key
 * @property string $expires_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder<static>|Hold whereCreatedAt($value)
 * @method static Builder<static>|Hold whereExpiresAt($value)
 * @method static Builder<static>|Hold whereId($value)
 * @method static Builder<static>|Hold whereIdempotencyKey($value)
 * @method static Builder<static>|Hold whereSlotId($value)
 * @method static Builder<static>|Hold whereStatus($value)
 * @method static Builder<static>|Hold whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Hold extends Model
{
    use HasFactory;
    public const string STATUS_HELD = 'held';
    public const string STATUS_CONFIRMED = 'confirmed';
    public const string STATUS_CANCELLED = 'cancelled';
    protected $fillable = ['slot_id', 'status', 'idempotency_key', 'expires_at'];

    public function slot(): BelongsTo
    {
        return $this->belongsTo(Slot::class);
    }

}
