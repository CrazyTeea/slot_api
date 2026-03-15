<?php

namespace App\Services;

use App\Models\Hold;
use App\Models\Slot;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class SlotService
{
    private string $cacheKeyPrefix;
    private string $cacheTtl;

    public function __construct()
    {
        $this->cacheKeyPrefix = config('cache.cache_key_prefix');
        $this->cacheTtl = config('cache.cache_ttl');
    }

    public function getAvailableSlots(): Collection
    {
        return Cache::remember($this->cacheKeyPrefix, $this->cacheTtl, function () {
            return Slot::where('remaining', '>', 0)->get()->map(function ($slot) {
                return [
                    'slot_id' => $slot->id,
                    'capacity' => $slot->capacity,
                    'remaining' => $slot->remaining,
                ];
            });
        });
    }

    /**
     * @throws Throwable
     */
    public function createHold(int $slotId, string $idempotenceKey)
    {
        return DB::transaction(function () use ($slotId, $idempotenceKey) {
            $hold = Hold::whereIdempotencyKey($idempotenceKey)->first();
            if ($hold) {
                return $hold;
            }

            $slot = Slot::whereId($slotId)->lockForUpdate()->first();

            if (!$slot or $slot->remaining == 0) {
                throw new HttpResponseException(response()->json([
                    'error' => 'slot not found or is full',
                ], 409));
            }

            return Hold::create([
                'slot_id' => $slotId,
                'idempotency_key' => $idempotenceKey,
                'status' => Hold::STATUS_HELD,
                'expires_at' => now()->addMinutes(5),
            ]);
        });
    }

    /**
     * @throws Throwable
     */
    public function confirmHold(int $holdId)
    {
        return DB::transaction(function () use ($holdId) {
            $hold = Hold::whereId($holdId)->lockForUpdate()->first();

            if (!$hold or $hold->status !== Hold::STATUS_HELD) {
                throw new HttpResponseException(response()->json([
                    'error' => 'hold not found or is not held'
                ], 409));
            }

            $slot = Slot::whereId($hold->slot_id)->lockForUpdate()->first();

            if (!$slot or $slot->remaining == 0) {
                throw new HttpResponseException(response()->json([
                    'error' => 'slot not found or is full',
                ], 409));
            }

            $slot->decrement('remaining');
            $hold->update(['status' => Hold::STATUS_CONFIRMED]);

            $this->invalidateCache();
            return $hold;

        });
    }

    /**
     * @throws Throwable
     */
    public function cancelHold(int $holdId)
    {
        return DB::transaction(function () use ($holdId) {
            $hold = Hold::whereId($holdId)->lockForUpdate()->first();
            if (!$hold or $hold->status === Hold::STATUS_CANCELLED) {
                return $hold;
            }

            $slot = Slot::whereId($hold->slot_id)->lockForUpdate()->first();
            $slot->update(['remaining', $slot->remaining + 1]);
            $hold->update(['status' => Hold::STATUS_CANCELLED]);
            $this->invalidateCache();
            return $hold;
        });
    }

    public function invalidateCache(): void
    {
        Cache::forget($this->cacheKeyPrefix);
    }
}
