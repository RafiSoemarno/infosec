<?php

namespace App\Http\Controllers;

use App\Services\DrillDataService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProgressDrillController extends Controller
{
    private DrillDataService $drillData;

    public function __construct(DrillDataService $drillData)
    {
        $this->drillData = $drillData;
    }

    public function index(): View|\Illuminate\Http\RedirectResponse
    {
        $payload = $this->drillData->getProgressDrillPayload((array) Auth::user()->toAuthUserArray());

        return view('progress-drill', $payload);
    }
}
