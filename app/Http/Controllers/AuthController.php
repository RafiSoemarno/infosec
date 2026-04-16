<?php

namespace App\Http\Controllers;

use App\Services\DrillDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        if (Auth::check()) {
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

        $credentials = [
            'MailAddress' => $request->input('username'),
            'password' => $request->input('password'),
        ];

        if (!Auth::attempt($credentials, false)) {
            return redirect('/')
                ->withInput($request->only('username'))
                ->withErrors([
                    'auth' => 'Invalid username or password.',
                ]);
        }

        $request->session()->regenerate();

        return redirect('/menu');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}