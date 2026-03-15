<?php

namespace App\Http\Controllers;

use App\Services\SlotService;

abstract class Controller
{
    public function __construct(public SlotService $slotService)
    {
    }
}
