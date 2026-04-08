<?php

namespace App\Http\Controllers;

use App\Services\DrillDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DrillController extends Controller
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

        $payload = $this->drillData->getDrillPayload((array) session('auth_user'));

        return view('drill', $payload);
    }

    public function complete(Request $request)
    {
        if (!session()->has('auth_user')) {
            return redirect('/');
        }

        $request->validate([
            'drill_id' => ['required', 'integer'],
        ]);

        $this->drillData->completeDrill(
            (array) session('auth_user'),
            (int) $request->input('drill_id')
        );

        return redirect('/drill');
    }
}
