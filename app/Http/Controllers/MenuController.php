<?php

namespace App\Http\Controllers;

use App\Services\DrillDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MenuController extends Controller
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

        $authUser = (array) session('auth_user');
        $payload = $this->drillData->getMenuPayload($authUser);

        if (!empty($authUser['isSpecial'])) {
            $payload['menuData']['items'][] = [
                'title'    => 'Progress Practice',
                'subtitle' => 'Track your training progress',
                'url'      => '/progress-practice',
                'symbol'   => 'PRG',
            ];
        }

        return view('menu', $payload);
    }
}
