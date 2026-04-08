<?php

namespace App\Http\Controllers;

use App\Services\DrillDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EducationController extends Controller
{
    private DrillDataService $drillData;

    public function __construct(DrillDataService $drillData)
    {
        $this->drillData = $drillData;
    }

    public function index(Request $request)
    {
        if (!session()->has('auth_user')) {
            return redirect('/')
                ->withErrors([
                    'auth' => 'Please sign in first.',
                ]);
        }

        $payload = $this->drillData->getEducationPayload(
            (array) session('auth_user'),
            (int) $request->query('video', 0)
        );

        return view('education', $payload);
    }
}
