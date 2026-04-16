<?php

namespace App\Http\Controllers;

use App\Services\DrillDataService;
use App\Services\DrillScheduleStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        // Only validate the fields that were sent (one half at a time)
        $rules = [
            'first_half_start_date'  => ['nullable', 'date'],
            'first_half_end_date'    => ['nullable', 'date'],
            'first_half_start_time'  => ['nullable'],
            'first_half_end_time'    => ['nullable'],
            'first_half_duration'    => ['nullable', 'integer', 'min:1'],
            'first_half_target'      => ['nullable', 'integer', 'min:0'],
            'second_half_start_date' => ['nullable', 'date'],
            'second_half_end_date'   => ['nullable', 'date'],
            'second_half_start_time' => ['nullable'],
            'second_half_end_time'   => ['nullable'],
            'second_half_duration'   => ['nullable', 'integer', 'min:1'],
            'second_half_target'     => ['nullable', 'integer', 'min:0'],
        ];

        // If first_half fields are populated, they must all be valid
        if ($request->filled('first_half_start_date')) {
            $rules['first_half_start_date']  = ['required', 'date'];
            $rules['first_half_end_date']    = ['required', 'date'];
            $rules['first_half_start_time']  = ['required'];
            $rules['first_half_end_time']    = ['required'];
            $rules['first_half_duration']    = ['required', 'integer', 'min:1'];
        }

        // If second_half fields are populated, they must all be valid
        if ($request->filled('second_half_start_date')) {
            $rules['second_half_start_date'] = ['required', 'date'];
            $rules['second_half_end_date']   = ['required', 'date'];
            $rules['second_half_start_time'] = ['required'];
            $rules['second_half_end_time']   = ['required'];
            $rules['second_half_duration']   = ['required', 'integer', 'min:1'];
        }

        $request->validate($rules);

        $this->store->saveSelfService($request->all());

        // Sync first-half window to the user-facing drill-dashboard.json
        $saved = $this->store->getSelfService();
        $this->store->syncSelfServiceToDrillDashboard($saved['first_half'] ?? []);

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

        // Save schedule config, add it as a new drill entry, and sync to drill-dashboard.json
        $this->store->saveScheduleDrillAndSync($request->all());

        return redirect(url('/admin/drill'))->with('success_schedule', 'Schedule drill saved and added to the list.');
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
        $user = Auth::user();
        return $user && $user->role === 'admin';
    }

    private function buildPayload(): array
    {
        $authUser = (array) Auth::user()->toAuthUserArray();

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
        $items = array_map(function (array $item) {
            if (strtolower($item['url'] ?? '') === '/drill') {
                $item['url'] = '/admin/drill';
            }
            return $item;
        }, $items);

        // Append Summary Report if not already present
        $hasSummaryReport = false;
        foreach ($items as $item) {
            if (strtolower($item['url'] ?? '') === '/admin/summary-report') {
                $hasSummaryReport = true;
                break;
            }
        }
        if (!$hasSummaryReport) {
            $items[] = [
                'title'    => 'Summary Report',
                'subtitle' => 'Drill completion summary by division',
                'url'      => '/admin/summary-report',
                'symbol'   => 'SRP',
            ];
        }

        return $items;
    }
}
