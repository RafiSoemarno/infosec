<?php

namespace App\Http\Controllers;

use App\Services\DrillDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    private DrillDataService $drillData;

    public function __construct(DrillDataService $drillData)
    {
        $this->drillData = $drillData;
    }

    public function showLogin()
    {
        if (session()->has('auth_user')) {
            return redirect('/menu');
        }

        $this->drillData->seedFromJsonIfEmpty();
        $drillData = $this->drillData->getLandingData();

        return view('welcome', compact('drillData'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = $this->drillData->authenticate($request);

        if (!$user) {
            return redirect('/')
                ->withInput($request->only('username'))
                ->withErrors([
                    'auth' => 'Invalid username or password.',
                ]);
        }

        session()->put('auth_user', $user);

        return redirect('/menu');
    }

    public function logout()
    {
        session()->forget('auth_user');

        return redirect('/');
    }
}