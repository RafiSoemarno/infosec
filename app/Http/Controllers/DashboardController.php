<?php

namespace App\Http\Controllers;

use App\Services\DrillDataService;
use App\Services\DrillScheduleStore;

/**
 * DashboardController
 *
 * Admin-only drill statistics dashboard.
 * Provides multi-dimensional data for:
 *   - Statistic Overview (filtered by Company + Directorate)
 *   - Data Table (filtered by Division + Responsible Person)
 */
class DashboardController extends Controller
{
    private DrillDataService   $drillData;
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

        return $this->renderDashboard();
    }

    /**
     * Build and return the admin-dashboard view response.
     * Called both from index() (direct /admin/dashboard URL)
     * and from ResultController when the logged-in user is admin.
     */
    public function renderDashboard()
    {
        $authUser  = (array) session('auth_user');
        $menuItems = $this->getMenuItems();
        $dashData  = $this->buildDashboardData();

        return view('admin-dashboard', [
            'user'     => $authUser,
            'menuData' => ['items' => $menuItems],
            'dashData' => $dashData,
        ]);
    }

    // ── Helpers ─────────────────────────────────────────────────────

    private function isAdmin(): bool
    {
        $user = (array) session('auth_user');
        return ($user['role'] ?? '') === 'admin';
    }

    private function getMenuItems(): array
    {
        $path = public_path('data/drill-dashboard.json');
        if (!file_exists($path)) {
            return [];
        }

        $decoded = json_decode(file_get_contents($path), true);
        $items   = is_array($decoded) ? ($decoded['menu']['items'] ?? []) : [];

        // Remap /drill → /admin/drill for admin context
        $items = array_map(function (array $item) {
            if (strtolower($item['url'] ?? '') === '/drill') {
                $item['url'] = '/admin/drill';
            }
            return $item;
        }, $items);

        // Append Summary Report entry if missing (no Dashboard entry — already present in base menu)
        $existingUrls = array_column($items, 'url');
        if (!in_array('/admin/summary-report', $existingUrls)) {
            $items[] = [
                'title'    => 'Summary Report',
                'subtitle' => 'Drill completion summary',
                'url'      => '/admin/summary-report',
                'symbol'   => 'SRP',
            ];
        }

        return $items;
    }

    private function buildDashboardData(): array
    {
        // ── Load base JSON ───────────────────────────────────────────
        $path = public_path('data/drill-dashboard.json');
        $json = [];
        if (file_exists($path)) {
            $decoded = json_decode(file_get_contents($path), true);
            $json    = is_array($decoded) ? $decoded : [];
        }

        $periodOptions = $json['dashboard']['periodOptions'] ?? ['1st Half', '2nd Half'];
        $periodData    = $json['dashboard']['periodData']    ?? [];

        // ── Company / Directorate hierarchy ──────────────────────────
        // Each company has directorates; directorates own chart stats.
        $companies = [
            'DNIA' => [
                'label'        => 'PT. Denso Indonesia',
                'directorates' => ['DNIA-CORP', 'DNIA-PROD', 'DNIA-QA'],
            ],
            'DMIA' => [
                'label'        => 'PT. Denso Manufacturing Indonesia',
                'directorates' => ['DMIA-CORP', 'DMIA-MANUF'],
            ],
            'DSIA' => [
                'label'        => 'PT. Denso Sales Indonesia',
                'directorates' => ['DSIA-CORP', 'DSIA-SALES'],
            ],
            'HDI'  => [
                'label'        => 'PT. Hamaden Indonesia',
                'directorates' => ['HDI-CORP'],
            ],
        ];

        $directorates = [
            'DNIA-CORP'  => ['label' => 'Corporate',    'company' => 'DNIA'],
            'DNIA-PROD'  => ['label' => 'Production',   'company' => 'DNIA'],
            'DNIA-QA'    => ['label' => 'Quality Assurance', 'company' => 'DNIA'],
            'DMIA-CORP'  => ['label' => 'Corporate',    'company' => 'DMIA'],
            'DMIA-MANUF' => ['label' => 'Manufacturing','company' => 'DMIA'],
            'DSIA-CORP'  => ['label' => 'Corporate',    'company' => 'DSIA'],
            'DSIA-SALES' => ['label' => 'Sales',        'company' => 'DSIA'],
            'HDI-CORP'   => ['label' => 'Corporate',    'company' => 'HDI'],
        ];

        // ── Division / Responsible Person hierarchy ───────────────────
        // Division is NOT tied to company/directorate.
        $divisions = [
            'HC-LEGAL'    => 'HC - Legal',
            'FINANCE'     => 'Finance',
            'FAC-SE'      => 'FAC & SE',
            'INSPECTION-B'=> 'Inspection B',
            'ADMINISTRATION' => 'Administration',
        ];

        // ── Table rows (individual drill records) ─────────────────────
        // Keys: division, responsiblePerson, npk, name, plant, pcName,
        //       drillType, category, date, responseTime, status
        $tableRows = [
            '1st Half' => [
                [
                    'division'          => 'HC-LEGAL',
                    'responsiblePerson' => 'FARIDA MARTHA',
                    'npk'               => 'JK2141189',
                    'name'              => 'Ahmad Dani',
                    'plant'             => 'BEKASI',
                    'pcName'            => 'PC NAME',
                    'drillType'         => 'Self Service',
                    'category'          => 'Ransomware',
                    'date'              => '2026-04-10',
                    'responseTime'      => '2m 10s',
                    'status'            => 'Passed',
                ],
                [
                    'division'          => 'HC-LEGAL',
                    'responsiblePerson' => 'FERZAN WIDIANI',
                    'npk'               => 'JK2141190',
                    'name'              => 'Sari Dewi',
                    'plant'             => 'BEKASI',
                    'pcName'            => 'BKS-PC-0022',
                    'drillType'         => 'Self Service',
                    'category'          => 'Ransomware',
                    'date'              => '2026-04-10',
                    'responseTime'      => '4m 05s',
                    'status'            => 'Failed',
                ],
                [
                    'division'          => 'FINANCE',
                    'responsiblePerson' => 'RYAN FERNANDO',
                    'npk'               => 'JK2141201',
                    'name'              => 'Budi Santoso',
                    'plant'             => 'SUNTER',
                    'pcName'            => 'SNT-PC-0011',
                    'drillType'         => 'Scheduled',
                    'category'          => 'Phishing',
                    'date'              => '2026-04-11',
                    'responseTime'      => '1m 50s',
                    'status'            => 'Passed',
                ],
                [
                    'division'          => 'FINANCE',
                    'responsiblePerson' => 'RYAN FERNANDO',
                    'npk'               => 'JK2141202',
                    'name'              => 'Lina Marlina',
                    'plant'             => 'SUNTER',
                    'pcName'            => 'SNT-PC-0015',
                    'drillType'         => 'Scheduled',
                    'category'          => 'Phishing',
                    'date'              => '2026-04-11',
                    'responseTime'      => '5m 30s',
                    'status'            => 'Scheduled',
                ],
                [
                    'division'          => 'FAC-SE',
                    'responsiblePerson' => 'FARIDA MARTHA',
                    'npk'               => 'JK2141215',
                    'name'              => 'Joko Purnomo',
                    'plant'             => 'BEKASI',
                    'pcName'            => 'BKS-PC-0033',
                    'drillType'         => 'Self Service',
                    'category'          => 'Ransomware',
                    'date'              => '2026-04-12',
                    'responseTime'      => '3m 22s',
                    'status'            => 'Passed',
                ],
                [
                    'division'          => 'INSPECTION-B',
                    'responsiblePerson' => 'FERZAN WIDIANI',
                    'npk'               => 'JK2141230',
                    'name'              => 'Maya Putri',
                    'plant'             => 'BEKASI',
                    'pcName'            => 'BKS-PC-0040',
                    'drillType'         => 'Scheduled',
                    'category'          => 'Ransomware',
                    'date'              => '2026-04-13',
                    'responseTime'      => '6m 10s',
                    'status'            => 'Failed',
                ],
                [
                    'division'          => 'ADMINISTRATION',
                    'responsiblePerson' => 'RYAN FERNANDO',
                    'npk'               => 'JK2141240',
                    'name'              => 'Rina Wati',
                    'plant'             => 'SUNTER',
                    'pcName'            => 'SNT-PC-0025',
                    'drillType'         => 'Self Service',
                    'category'          => 'Phishing',
                    'date'              => '2026-04-14',
                    'responseTime'      => '2m 44s',
                    'status'            => 'Passed',
                ],
                [
                    'division'          => 'ADMINISTRATION',
                    'responsiblePerson' => 'FARIDA MARTHA',
                    'npk'               => 'JK2141241',
                    'name'              => 'Doni Setiawan',
                    'plant'             => 'SUNTER',
                    'pcName'            => 'SNT-PC-0027',
                    'drillType'         => 'Scheduled',
                    'category'          => 'Ransomware',
                    'date'              => '2026-04-14',
                    'responseTime'      => '3m 58s',
                    'status'            => 'Scheduled',
                ],
            ],
            '2nd Half' => [
                [
                    'division'          => 'HC-LEGAL',
                    'responsiblePerson' => 'FARIDA MARTHA',
                    'npk'               => 'JK2141189',
                    'name'              => 'Ahmad Dani',
                    'plant'             => 'BEKASI',
                    'pcName'            => 'BKS-PC-0010',
                    'drillType'         => 'Self Service',
                    'category'          => 'Ransomware',
                    'date'              => '2026-10-05',
                    'responseTime'      => '2m 30s',
                    'status'            => 'Passed',
                ],
                [
                    'division'          => 'FINANCE',
                    'responsiblePerson' => 'RYAN FERNANDO',
                    'npk'               => 'JK2141201',
                    'name'              => 'Budi Santoso',
                    'plant'             => 'SUNTER',
                    'pcName'            => 'SNT-PC-0011',
                    'drillType'         => 'Scheduled',
                    'category'          => 'Phishing',
                    'date'              => '2026-10-06',
                    'responseTime'      => '1m 20s',
                    'status'            => 'Passed',
                ],
            ],
            '3rd Half' => [
                [
                    'division'          => 'HC-LEGAL',
                    'responsiblePerson' => 'FARIDA MARTHA',
                    'npk'               => 'JK2141189',
                    'name'              => 'Ahmad Dani',
                    'plant'             => 'BEKASI',
                    'pcName'            => 'BKS-PC-0010',
                    'drillType'         => 'Self Service',
                    'category'          => 'Ransomware',
                    'date'              => '2027-01-08',
                    'responseTime'      => '1m 55s',
                    'status'            => 'Passed',
                ],
            ],
        ];

        // ── Derive responsible persons per division ───────────────────
        $personsByDivision = [];
        foreach ($tableRows as $periodRows) {
            foreach ($periodRows as $row) {
                $div    = $row['division'];
                $person = $row['responsiblePerson'];
                if (!isset($personsByDivision[$div])) {
                    $personsByDivision[$div] = [];
                }
                if (!in_array($person, $personsByDivision[$div])) {
                    $personsByDivision[$div][] = $person;
                }
            }
        }

        // ── Drill type summary ────────────────────────────────────────
        $drillTypeSummary = [
            '1st Half' => [
                'selfService' => ['count' => 78,  'total' => 78],
                'scheduled'   => ['count' => 1200, 'percentage' => 95],
            ],
            '2nd Half' => [
                'selfService' => ['count' => 95,  'total' => 95],
                'scheduled'   => ['count' => 1380, 'percentage' => 94],
            ],
            '3rd Half' => [
                'selfService' => ['count' => 110, 'total' => 110],
                'scheduled'   => ['count' => 1250, 'percentage' => 96],
            ],
        ];

        return [
            'periodOptions'     => $periodOptions,
            'selectedPeriod'    => $periodOptions[0] ?? '1st Half',
            'fiscalYears'       => [2025, 2026, 2027],
            'selectedFY'        => 2026,
            'companies'         => $companies,
            'directorates'      => $directorates,
            'divisions'         => $divisions,
            'personsByDivision' => $personsByDivision,
            'periodData'        => $periodData,
            'tableRows'         => $tableRows,
            'drillTypeSummary'  => $drillTypeSummary,
        ];
    }
}
