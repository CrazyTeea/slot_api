<?php

namespace App\Http\Controllers;

use App\Services\SlotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class HoldController extends Controller
{
    /**
     * @throws Throwable
     */
    public function createHold(int $id, Request $request): JsonResponse
    {
        $idempotencyKey = request()->header('Idempotency-Key');

        if (empty($idempotencyKey)) {
            return response()->json(['error' => 'Idempotency-Key not set'], 400);
        }


        $hold = $this->slotService->createHold($id, $idempotencyKey);
        return response()->json($hold);

    }

    /**
     * @throws Throwable
     */
    public function confirmHold(int $id): JsonResponse
    {

        $hold = $this->slotService->confirmHold($id);
        return response()->json($hold);

    }

    /**
     * @throws Throwable
     */
    public function destroyHold(int $id): JsonResponse
    {

        $hold = $this->slotService->cancelHold($id);
        return response()->json($hold);


    }
}


