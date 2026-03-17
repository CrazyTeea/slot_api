<?php

namespace App\Services;

use App\Exceptions\OverbookingException;
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
            // Проверяем существующий холд (идемпотентность)
            $hold = Hold::whereIdempotencyKey($idempotenceKey)->first();
            if ($hold) {
                return $hold;
            }
            // Получаем слот с блокировкой - (доп защита от овербукинга/оверсела из-за гонки)
            $slot = Slot::whereId($slotId)->lockForUpdate()->first();

            // Проверка наличия мест (защита от оверсела)
            $this->checkOverbookingOrOversell($slot);

            // Создаем холд и уменьшаем оставшиеся места
            $hold = Hold::create([
                'slot_id' => $slotId,
                'idempotency_key' => $idempotenceKey,
                'status' => Hold::STATUS_HELD,
                'expires_at' => now()->addMinutes(5),
            ]);
            // Атомарное уменьшение оставшихся мест
            $slot->decrement('remaining');

            return $hold;
        });
    }

    /**
     * @throws Throwable
     */
    public function confirmHold(int $holdId)
    {
        return DB::transaction(function () use ($holdId) {
            // Получаем холд с блокировкой
            $hold = Hold::whereId($holdId)->lockForUpdate()->first();

            if (!$hold or $hold->status !== Hold::STATUS_HELD) {
                throw new HttpResponseException(response()->json([
                    //здесь реализуется
                    'error' => 'hold not found or is not held'
                ], 409));
            }
            // Получаем слот с блокировкой - (доп защита от овербукинга/оверсела из-за гонки)
            $slot = Slot::whereId($hold->slot_id)->lockForUpdate()->first();

            $this->checkOverbookingOrOversell($slot);
            // Обновляем слот и холд
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
            $slot->increment('remaining');
            $hold->update(['status' => Hold::STATUS_CANCELLED]);
            $this->invalidateCache();
            return $hold;
        });
    }

    public function invalidateCache(): void
    {
        Cache::forget($this->cacheKeyPrefix);
    }

    /**
     * @throws OverbookingException
     */
    public function checkOverbookingOrOversell(Slot $slot): void
    {
        if ($slot->isFull()){
            throw new OverbookingException();
        }
    }
}
