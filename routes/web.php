<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

function getDrillData(): array
{
    $jsonPath = public_path('data/drill-dashboard.json');

    if (!file_exists($jsonPath)) {
        return [];
    }

    $decoded = json_decode(file_get_contents($jsonPath), true);

    return is_array($decoded) ? $decoded : [];
}

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    if (session()->has('auth_user')) {
        return redirect('/menu');
    }

    $drillData = getDrillData();

    return view('welcome', compact('drillData'));
});

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'username' => ['required', 'string'],
        'password' => ['required', 'string'],
    ]);

    $drillData = getDrillData();
    $authUsers = $drillData['auth']['users'] ?? [];

    foreach ($authUsers as $user) {
        $username = (string) ($user['username'] ?? '');
        $password = (string) ($user['password'] ?? '');

        if (
            hash_equals(strtolower($username), strtolower($credentials['username']))
            && hash_equals($password, $credentials['password'])
        ) {
            session()->put('auth_user', [
                'username' => $username,
                'name' => (string) ($user['name'] ?? $username),
                'employeeId' => (string) ($user['employeeId'] ?? '-'),
                'company' => (string) ($user['company'] ?? '-'),
                'businessUnit' => (string) ($user['businessUnit'] ?? '-'),
                'email' => (string) ($user['email'] ?? '-'),
            ]);

            return redirect('/menu');
        }
    }

    return redirect('/')
        ->withInput($request->only('username'))
        ->withErrors([
            'auth' => 'Invalid username or password.',
        ]);
});

Route::get('/menu', function () {
    if (!session()->has('auth_user')) {
        return redirect('/')
            ->withErrors([
                'auth' => 'Please sign in first.',
            ]);
    }

    $drillData = getDrillData();
    $menuData = $drillData['menu'] ?? [];

    return view('menu', [
        'brand' => $drillData['brand'] ?? [],
        'menuData' => $menuData,
        'user' => session('auth_user'),
    ]);
});

Route::get('/education', function () {
    if (!session()->has('auth_user')) {
        return redirect('/')
            ->withErrors([
                'auth' => 'Please sign in first.',
            ]);
    }

    $drillData = getDrillData();

    return view('education', [
        'brand' => $drillData['brand'] ?? [],
        'menuData' => $drillData['menu'] ?? [],
        'educationData' => $drillData['education'] ?? [],
        'user' => session('auth_user'),
    ]);
});

Route::post('/logout', function () {
    session()->forget('auth_user');

    return redirect('/');
});
