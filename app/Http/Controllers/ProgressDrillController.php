<?php

namespace App\Http\Controllers;

use App\Services\DrillDataService;
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
        if (!session()->has('auth_user')) {
            return redirect('/')
                ->withErrors([
                    'auth' => 'Please sign in first.',
                ]);
        }

        $payload = $this->drillData->getProgressDrillPayload((array) session('auth_user'));

        return view('progress-drill', $payload);
    }
}
