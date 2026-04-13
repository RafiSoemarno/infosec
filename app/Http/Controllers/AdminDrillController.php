<?php

namespace App\Http\Controllers;

use App\Services\DrillDataService;
use App\Services\DrillScheduleStore;
use Illuminate\Http\Request;

/**
 * AdminDrillController
 *
 * Handles the admin-only Drill Simulation scheduling page.
 * All data is stored in storage/app/drill-schedules.json via DrillScheduleStore.
 */
class AdminDrillController extends Controller
{
    private DrillDataService   $drillData;
    private DrillScheduleStore $store;

    public function __construct(DrillDataService $drillData, DrillScheduleStore $store)
    {
        $this->drillData = $drillData;
        $this->store     = $store;
    }

    // ── Show page ──────────────────────────────────────────────────

    public function index()
    {
        if (!$this->isAdmin()) {
            return redirect('/drill');
        }

        return view('admin-drill', $this->buildPayload());
    }

    // ── Save self-service window ───────────────────────────────────

    public function saveSelfService(Request $request)
    {
        if (!$this->isAdmin()) {
            return redirect('/drill');
        }

        $request->validate([
            'first_half_start_date'  => ['required', 'date'],
            'first_half_end_date'    => ['required', 'date'],
            'first_half_start_time'  => ['required'],
            'first_half_end_time'    => ['required'],
            'first_half_duration'    => ['required', 'integer', 'min:1'],
            'second_half_start_date' => ['required', 'date'],
            'second_half_end_date'   => ['required', 'date'],
            'second_half_start_time' => ['required'],
            'second_half_end_time'   => ['required'],
            'second_half_duration'   => ['required', 'integer', 'min:1'],
        ]);

        $this->store->saveSelfService($request->all());

        return redirect(url('/admin/drill'))->with('success_self_service', 'Self-service window saved successfully.');
    }

    // ── Save schedule drill (factory) ──────────────────────────────

    public function saveScheduleDrill(Request $request)
    {
        if (!$this->isAdmin()) {
            return redirect('/drill');
        }

        $request->validate([
            'company'  => ['required', 'string', 'max:100'],
            'plant'    => ['required', 'string', 'max:100'],
            'duration' => ['required', 'integer', 'min:1'],
            'date'     => ['required', 'date'],
            'time'     => ['required'],
        ]);

        $this->store->saveScheduleDrill($request->all());

        return redirect(url('/admin/drill'))->with('success_schedule', 'Schedule drill saved successfully.');
    }

    // ── Add a drill entry ──────────────────────────────────────────

    public function storeDrill(Request $request)
    {
        if (!$this->isAdmin()) {
            return redirect('/drill');
        }

        $request->validate([
            'company'  => ['required', 'string', 'max:150'],
            'plant'    => ['required', 'string', 'max:100'],
            'date'     => ['required', 'date'],
            'time'     => ['required'],
            'duration' => ['required', 'integer', 'min:1'],
        ]);

        $this->store->createDrill($request->all());

        return redirect(url('/admin/drill'))->with('success_schedule', 'Drill entry added successfully.');
    }

    // ── Update a drill entry ───────────────────────────────────────

    public function updateDrill(Request $request, int $id)
    {
        if (!$this->isAdmin()) {
            return redirect('/drill');
        }

        $request->validate([
            'company'  => ['required', 'string', 'max:150'],
            'plant'    => ['required', 'string', 'max:100'],
            'date'     => ['required', 'date'],
            'time'     => ['required'],
            'duration' => ['required', 'integer', 'min:1'],
        ]);

        $this->store->updateDrill($id, $request->all());

        return redirect(url('/admin/drill'))->with('success_schedule', 'Drill entry updated successfully.');
    }

    // ── Delete a drill entry ───────────────────────────────────────

    public function destroyDrill(int $id)
    {
        if (!$this->isAdmin()) {
            return redirect('/drill');
        }

        $this->store->deleteDrill($id);

        return redirect(url('/admin/drill'))->with('success_schedule', 'Drill entry deleted.');
    }

    // ── Helpers ────────────────────────────────────────────────────

    private function isAdmin(): bool
    {
        $user = (array) session('auth_user');
        return ($user['role'] ?? '') === 'admin';
    }

    private function buildPayload(): array
    {
        $authUser = (array) session('auth_user');

        $menuItems = $this->getMenuItemsFromJson();

        return [
            'user'          => $authUser,
            'menuData'      => ['items' => $menuItems],
            'selfService'   => $this->store->getSelfService(),
            'scheduleDrill' => $this->store->getScheduleDrill(),
            'drills'        => $this->store->allDrills(),
        ];
    }

    private function getMenuItemsFromJson(): array
    {
        $path = public_path('data/drill-dashboard.json');

        if (!file_exists($path)) {
            return [];
        }

        $decoded = json_decode(file_get_contents($path), true);
        $items   = \is_array($decoded) ? ($decoded['menu']['items'] ?? []) : [];

        // Remap /drill → /admin/drill so the sidebar highlights the correct item.
        return array_map(function (array $item) {
            if (strtolower($item['url'] ?? '') === '/drill') {
                $item['url'] = '/admin/drill';
            }
            return $item;
        }, $items);
    }
}
