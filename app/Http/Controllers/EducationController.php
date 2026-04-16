<?php

namespace App\Http\Controllers;

use App\Services\DrillDataService;
use Illuminate\Http\Request;

/**
 * Shows the Education page.
 *
 * - admin role  → admin-education.blade.php  (upload UI + material list)
 * - all others  → education.blade.php        (viewer)
 *
 * All material metadata lives in public/data/drill-dashboard.json
 * under education.videos. No secondary JSON file is used.
 */
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
            return redirect('/')->withErrors(['auth' => 'Please sign in first.']);
        }

        $authUser = (array) session('auth_user');

        if (($authUser['role'] ?? '') === 'admin') {
            return $this->adminIndex($authUser);
        }

        $payload = $this->drillData->getEducationPayload(
            $authUser,
            (int) $request->query('video', 0)
        );

        return view('education', $payload);
    }

    // ── Admin view ────────────────────────────────────────────────

    private function adminIndex(array $authUser)
    {
        $path    = public_path('data/drill-dashboard.json');
        $decoded = file_exists($path)
            ? json_decode(file_get_contents($path), true)
            : [];

        $raw       = is_array($decoded) ? ($decoded['education']['videos'] ?? []) : [];
        $menuItems = is_array($decoded) ? ($decoded['menu']['items'] ?? []) : [];

        // Only show uploaded materials (those with a fileType) in the admin list
        $materials = array_values(array_filter($raw, fn($v) => !empty($v['fileType'])));

        // Add a public URL for each so the view can render/download
        $materials = array_map(function (array $v) {
            // embedUrl is stored as "/files/<storage_path>" — serve it directly
            $v['public_url'] = $v['embedUrl'] ?? '';
            return $v;
        }, $materials);

        $payload = $this->drillData->getEducationPayload($authUser);

        return view('admin-education', [
            'user'      => $authUser,
            'menuData'  => $payload['menuData'] ?? ['items' => $menuItems],
            'materials' => $materials,
        ]);
    }
}
