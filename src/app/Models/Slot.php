<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @method static Builder<static>|Slot newModelQuery()
 * @method static Builder<static>|Slot newQuery()
 * @method static Builder<static>|Slot query()
 * @property int $id
 * @property int $capacity
 * @property int $remaining
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder<static>|Slot whereCapacity($value)
 * @method static Builder<static>|Slot whereCreatedAt($value)
 * @method static Builder<static>|Slot whereId($value)
 * @method static Builder<static>|Slot whereRemaining($value)
 * @method static Builder<static>|Slot whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Slot extends Model
{
    use HasFactory;
    protected $fillable = ['capacity', 'remaining'];

    public function holds(): HasMany
    {
        return $this->hasMany(Hold::class);
    }

    public function isFull(): bool
    {
        //не вижу смысла проверять по капасити, логично предположить что кол-во возможных холдов при создание слота <= капасити
        //из чего следует что при проверке полности слота нет смысла дописывать this->remaining <= $this->capacity
        return ($this->remaining <= 0);
    }
}
