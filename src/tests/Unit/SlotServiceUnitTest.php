<?php

namespace Tests\Unit;

use App\Models\Hold;
use App\Models\Slot;
use App\Services\SlotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Throwable;
use Tests\TestCase;

class SlotServiceUnitTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_hold_success() {
        $slot = Slot::factory()->create(['capacity' => 10, 'remaining' => 10]);
        $service = new SlotService();
        $hold = $service->createHold($slot->id, 'test-uuid-key');
        $this->assertNotNull($hold);
        $this->assertEquals(Hold::STATUS_HELD, $hold->status);
    }

    public function test_create_hold_with_no_capacity() {
        $slot = Slot::factory()->create(['capacity' => 1, 'remaining' => 0]);
        $service = new SlotService();

        $this->expectException(Throwable::class);
        $hold = $service->createHold($slot->id, 'test-uuid-key2');
    }
}
