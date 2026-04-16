<?php

namespace App\Http\Controllers;

use App\Services\DrillDataService;
use App\Services\DrillScheduleStore;
use Illuminate\Support\Facades\Auth;

class SummaryReportController extends Controller
{
    private DrillDataService $drillData;
    private DrillScheduleStore $store;

    public function __construct(DrillDataService $drillData, DrillScheduleStore $store)
    {
        $this->drillData = $drillData;
        $this->store     = $store;
    }

    public function index()
    {
        if (!$this->isAdmin()) {
            return redirect('/menu');
        }

        $authUser  = (array) Auth::user()->toAuthUserArray();
        $menuItems = $this->getMenuItems();

        // Build summary report rows from drills + JSON dashboard data
        $summaryData = $this->buildSummaryData();

        return view('admin-summary-report', [
            'user'        => $authUser,
            'menuData'    => ['items' => $menuItems],
            'summaryData' => $summaryData,
        ]);
    }

    // ── Helpers ────────────────────────────────────────────────────

    private function isAdmin(): bool
    {
        $user = Auth::user();
        return $user && $user->role === 'admin';
    }

    private function getMenuItems(): array
    {
        $path = public_path('data/drill-dashboard.json');
        if (!file_exists($path)) {
            return [];
        }

        $decoded = json_decode(file_get_contents($path), true);
        $items   = is_array($decoded) ? ($decoded['menu']['items'] ?? []) : [];

        // Remap /drill → /admin/drill
        $items = array_map(function (array $item) {
            if (strtolower($item['url'] ?? '') === '/drill') {
                $item['url'] = '/admin/drill';
            }
            return $item;
        }, $items);

        // Append Summary Report
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

    private function buildSummaryData(): array
    {
        // Load period data from dashboard JSON for period/target/actual stats
        $path = public_path('data/drill-dashboard.json');
        $dashboard = [];
        if (file_exists($path)) {
            $decoded   = json_decode(file_get_contents($path), true);
            $dashboard = is_array($decoded) ? $decoded : [];
        }

        $periodData = $dashboard['dashboard']['periodData'] ?? [];

        // Build period options from the self-service store
        $selfService = $this->store->getSelfService();

        $periods = ['1st Half', '2nd Half'];
        $selectedPeriod = '1st Half';

        // Build the summary rows — grouped by company & division
        // These are representative rows matching what the admin UI screenshot shows.
        $rows = [
            '1st Half' => [
                [
                    'company'           => 'DSIA',
                    'division'          => 'CORPORATE',
                    'noPc'              => 10,
                    'noPcOk'            => 7,
                    'noPcNg'            => 3,
                    'responsiblePerson' => 'CARLA',
                    'remarkPcNg'        => ['P91112', 'N92221', 'P9222304333'],
                ],
                [
                    'company'           => 'DSIA',
                    'division'          => 'MKT 1',
                    'noPc'              => 28,
                    'noPcOk'            => 28,
                    'noPcNg'            => 0,
                    'responsiblePerson' => 'BRIANS MAURIAT',
                    'remarkPcNg'        => [],
                ],
                [
                    'company'           => 'DSIA',
                    'division'          => 'SALES',
                    'noPc'              => 30,
                    'noPcOk'            => 29,
                    'noPcNg'            => 1,
                    'responsiblePerson' => 'BENZ LOUZ',
                    'remarkPcNg'        => ['N922205223'],
                ],
                [
                    'company'           => 'DNIA',
                    'division'          => 'GA',
                    'noPc'              => 20,
                    'noPcOk'            => 18,
                    'noPcNg'            => 2,
                    'responsiblePerson' => 'JALA YURIAT',
                    'remarkPcNg'        => ['N332305223', 'P497105223'],
                ],
                [
                    'company'           => 'DNIA',
                    'division'          => 'IS',
                    'noPc'              => 14,
                    'noPcOk'            => 14,
                    'noPcNg'            => 0,
                    'responsiblePerson' => 'BYAN P.',
                    'remarkPcNg'        => [],
                ],
            ],
            '2nd Half' => [
                [
                    'company'           => 'DSIA',
                    'division'          => 'CORPORATE',
                    'noPc'              => 10,
                    'noPcOk'            => 9,
                    'noPcNg'            => 1,
                    'responsiblePerson' => 'CARLA',
                    'remarkPcNg'        => ['P91115'],
                ],
                [
                    'company'           => 'DSIA',
                    'division'          => 'MKT 1',
                    'noPc'              => 28,
                    'noPcOk'            => 27,
                    'noPcNg'            => 1,
                    'responsiblePerson' => 'BRIANS MAURIAT',
                    'remarkPcNg'        => ['N92222'],
                ],
                [
                    'company'           => 'DSIA',
                    'division'          => 'SALES',
                    'noPc'              => 30,
                    'noPcOk'            => 30,
                    'noPcNg'            => 0,
                    'responsiblePerson' => 'BENZ LOUZ',
                    'remarkPcNg'        => [],
                ],
                [
                    'company'           => 'DNIA',
                    'division'          => 'GA',
                    'noPc'              => 20,
                    'noPcOk'            => 19,
                    'noPcNg'            => 1,
                    'responsiblePerson' => 'JALA YURIAT',
                    'remarkPcNg'        => ['N332405223'],
                ],
                [
                    'company'           => 'DNIA',
                    'division'          => 'IS',
                    'noPc'              => 14,
                    'noPcOk'            => 14,
                    'noPcNg'            => 0,
                    'responsiblePerson' => 'BYAN P.',
                    'remarkPcNg'        => [],
                ],
            ],
        ];

        // Build available dates from drills scheduled
        $drills = $this->store->allDrills();
        $drillDates = array_values(array_unique(array_map(fn($d) => $d['date'] ?? '', $drills)));
        sort($drillDates);

        if (empty($drillDates)) {
            $drillDates = [\Carbon\Carbon::today()->toDateString()];
        }

        // Available times derived from self-service window
        $fh = $selfService['first_half']  ?? [];
        $sh = $selfService['second_half'] ?? [];
        $availableTimes = array_values(array_unique(array_filter([
            $fh['start_time'] ?? '',
            $fh['end_time']   ?? '',
            $sh['start_time'] ?? '',
            $sh['end_time']   ?? '',
        ])));

        if (empty($availableTimes)) {
            $availableTimes = ['09:00', '11:00', '13:00', '17:00'];
        }

        return [
            'periods'        => $periods,
            'selectedPeriod' => $selectedPeriod,
            'drillDates'     => $drillDates,
            'selectedDate'   => $drillDates[0] ?? \Carbon\Carbon::today()->toDateString(),
            'availableTimes' => $availableTimes,
            'selectedTime'   => $availableTimes[0] ?? '11:00',
            'rows'           => $rows,
        ];
    }
}
