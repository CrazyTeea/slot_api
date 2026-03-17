<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OverbookingException extends \Exception
{
    public function render(Request $request): JsonResponse
    {
        return response()->json(['error' => 'slot is full'], 409);
    }
}
