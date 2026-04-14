<?php

namespace App\Http\Controllers;

use App\Services\DrillDataService;
use App\Services\EducationJsonStore;
use Illuminate\Http\Request;

/**
 * Shows the Education page.
 *
 * - admin role  → admin-education.blade.php  (upload UI + material list from JSON store)
 * - all others  → education.blade.php        (viewer, reads from drill-dashboard.json
 *                                             merged with education-materials.json)
 *
 * No database is used anywhere in this controller.
 */
class EducationController extends Controller
{
    private DrillDataService  $drillData;
    private EducationJsonStore $store;

    public function __construct(DrillDataService $drillData, EducationJsonStore $store)
    {
        $this->drillData = $drillData;
        $this->store     = $store;
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

        // Regular / special user — existing payload builder (reads drill-dashboard.json
        // and merges education-materials.json inside DrillDataService)
        $payload = $this->drillData->getEducationPayload(
            $authUser,
            (int) $request->query('video', 0)
        );

        return view('education', $payload);
    }

    // ── Admin view ────────────────────────────────────────────────

    /**
     * Build the admin education page payload entirely from static files.
     *
     * Materials list  → storage/app/education-materials.json  (via EducationJsonStore)
     * Sidebar menu    → public/data/drill-dashboard.json       (existing file)
     */
    private function adminIndex(array $authUser)
    {
        // 1. Read all uploaded materials from the JSON store
        //    Each record already has: id, title, file_path, file_type,
        //    original_filename, uploaded_by, created_at
        $rawMaterials = $this->store->all();

        // 2. Add the public URL so the view can render/download files
        $materials = array_map(function (array $m) {
            $m['public_url'] = asset($m['file_path']);
            return $m;
        }, $rawMaterials);

        // 3. Load the sidebar menu from drill-dashboard.json (same source the
        //    rest of the app uses — no DB call needed)
        $menuItems = $this->getMenuItemsFromJson();

        return view('admin-education', [
            'user'      => $authUser,
            'menuData'  => ['items' => $menuItems],
            'materials' => $materials,
        ]);
    }

    /**
     * Pull the menu items array out of drill-dashboard.json.
     * Returns an empty array if the file is missing (safe fallback).
     */
    private function getMenuItemsFromJson(): array
    {
        $path = public_path('data/drill-dashboard.json');

        if (!file_exists($path)) {
            return [];
        }

        $decoded = json_decode(file_get_contents($path), true);

        return is_array($decoded) ? ($decoded['menu']['items'] ?? []) : [];
    }
}
