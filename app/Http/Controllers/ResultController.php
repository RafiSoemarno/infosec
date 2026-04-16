<?php

namespace App\Http\Controllers;

use App\Services\DrillDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ResultController extends Controller
{
    private DrillDataService   $drillData;
    private DashboardController $dashboardController;

    public function __construct(DrillDataService $drillData, DashboardController $dashboardController)
    {
        $this->drillData           = $drillData;
        $this->dashboardController = $dashboardController;
    }

    public function index()
    {
        if (!session()->has('auth_user')) {
            return redirect('/')
                ->withErrors([
                    'auth' => 'Please sign in first.',
                ]);
        }

        // Admin users see the drill statistics dashboard instead of personal results.
        $user = (array) session('auth_user');
        if (($user['role'] ?? '') === 'admin') {
            return $this->dashboardController->renderDashboard();
        }

        $payload = $this->drillData->getMyResultPayload($user);

        return view('my-result', $payload);
    }
}
