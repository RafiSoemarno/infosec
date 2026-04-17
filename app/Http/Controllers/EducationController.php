<?php

namespace App\Http\Controllers;

use App\Services\DrillDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $authUser = (array) Auth::user()->toAuthUserArray();

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

        // Show all saved materials (link-based and legacy file-based)
        $materials = array_values(array_filter($raw, fn($v) => !empty($v['embedUrl'])));

        $payload = $this->drillData->getEducationPayload($authUser);

        return view('admin-education', [
            'user'      => $authUser,
            'menuData'  => $payload['menuData'] ?? ['items' => $menuItems],
            'materials' => $materials,
        ]);
    }
}
