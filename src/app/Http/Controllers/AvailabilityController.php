<?php

namespace App\Http\Controllers;

use App\Services\SlotService;
use Illuminate\Http\JsonResponse;

class AvailabilityController extends Controller
{
    public function getAvailability(): JsonResponse
    {
        return response()->json($this->slotService->getAvailableSlots());
    }

}
