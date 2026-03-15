<?php

namespace Tests\Feature;

use App\Models\Hold;
use App\Models\Slot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlotApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_available_slots()
    {
        $response = $this->get('/slots/availability');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => ['slot_id', 'capacity', 'remaining'],
        ]);
    }

    public function test_crete_hold_with_idempotency_key()
    {
        $slot = Slot::factory()->create(['capacity' => 10, 'remaining' => 10]);
        $response = $this->post("/slots/$slot->id/hold", [], [
            'Idempotency-Key' => 'test-uuid-123'
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['id', 'slot_id', 'idempotency_key', 'status']);
    }

    public function test_crete_duplicate_hold_with_idempotency_key()
    {
        $slot = Slot::factory()->create(['capacity' => 10, 'remaining' => 10]);
        $response = $this->post("/slots/$slot->id/hold", [], [
            'Idempotency-Key' => 'duble-uuid-123'
        ]);

        $response = $this->post("/slots/$slot->id/hold", [], [
            'Idempotency-Key' => 'duble-uuid-123'
        ]);

        $response->assertStatus(200);
    }

    public function test_confirm_hold()
    {
        $slot = Slot::factory()->create(['capacity' => 10, 'remaining' => 10]);
        $hold = Hold::factory()->create(['slot_id' => $slot->id, 'status' => 'held', 'expires_at' => now()->addMinutes(5)]);
        $response = $this->post("/holds/$hold->id/confirm");
        $response->assertStatus(200);
        $response->assertJsonPath('status', Hold::STATUS_CONFIRMED);
    }

    public function test_cancel_hold()
    {
        $slot = Slot::factory()->create(['capacity' => 10, 'remaining' => 10]);
        $hold = Hold::factory()->create(['slot_id' => $slot->id, 'expires_at' => now()->addMinutes(5)]);
        $response = $this->delete("/holds/$hold->id");
        $response->assertStatus(200);
    }

    public function test_create_hold_with_no_capacity()
    {
        $slot = Slot::factory()->create(['capacity' => 1, 'remaining' => 0]);
        $response = $this->post("/slots/$slot->id/hold", [], [
            'Idempotency-Key' => 'duble-uuid-123'
        ]);
        $response->assertStatus(409);
    }

}
