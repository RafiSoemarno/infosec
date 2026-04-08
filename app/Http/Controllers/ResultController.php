<?php

namespace App\Http\Controllers;

use App\Services\DrillDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ResultController extends Controller
{
    private DrillDataService $drillData;

    public function __construct(DrillDataService $drillData)
    {
        $this->drillData = $drillData;
    }

    public function index()
    {
        if (!session()->has('auth_user')) {
            return redirect('/')
                ->withErrors([
                    'auth' => 'Please sign in first.',
                ]);
        }

        $payload = $this->drillData->getMyResultPayload((array) session('auth_user'));

        return view('my-result', $payload);
    }
}
