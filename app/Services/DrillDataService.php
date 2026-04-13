<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\DashboardPeriodStat;
use App\Models\DrillSimulation;
use App\Models\EducationVideo;
use App\Models\MenuItem;
use App\Models\User;
use App\Models\UserDeviceInfo;
use App\Models\UserDrillCompletion;
use App\Models\UserVideoProgress;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DrillDataService
{
    public function getLandingData(): array
    {
        if ($this->isDatabaseEnabled()) {
            return $this->getLandingDataFromDatabase();
        }

        return $this->getDrillDataFromJson();
    }

    public function authenticate(Request $request): ?array
    {
        if ($this->isDatabaseEnabled()) {
            return $this->authenticateWithDatabase($request);
        }

        return $this->authenticateWithJson($request);
    }

    public function getMenuPayload(array $authUser): array
    {
        if ($this->isDatabaseEnabled()) {
            return $this->getMenuPayloadFromDatabase($authUser);
        }

        $drillData = $this->getDrillDataFromJson();

        return $this->appendSpecialMenuItems([
            'brand' => $drillData['brand'] ?? [],
            'menuData' => $drillData['menu'] ?? [],
            'user' => $authUser,
        ], $authUser);
    }

    public function getEducationPayload(array $authUser, int $videoId = 0): array
    {
        if ($this->isDatabaseEnabled()) {
            return $this->getEducationPayloadFromDatabase($authUser, $videoId);
        }

        return $this->getEducationPayloadFromJson($authUser, $videoId);
    }

    public function getDrillPayload(array $authUser): array
    {
        if ($this->isDatabaseEnabled()) {
            return $this->getDrillPayloadFromDatabase($authUser);
        }

        $drillData = $this->getDrillDataFromJson();

        return $this->appendSpecialMenuItems([
            'brand' => $drillData['brand'] ?? [],
            'menuData' => $drillData['menu'] ?? [],
            'drillSimData' => $drillData['drillSimulation'] ?? [],
            'educationData' => $drillData['education'] ?? [],
            'user' => $authUser,
        ], $authUser);
    }

    public function completeDrill(array $authUser, int $drillId): void
    {
        if ($drillId <= 0) {
            return;
        }

        if ($this->isDatabaseEnabled()) {
            $this->completeDrillInDatabase($authUser, $drillId);
            return;
        }

        $this->completeDrillInJson($drillId);
    }

    public function getProgressDrillPayload(array $authUser): array
    {
        if ($this->isDatabaseEnabled()) {
            return $this->getProgressDrillPayloadFromDatabase($authUser);
        }

        $drillData = $this->getDrillDataFromJson();

        return $this->appendSpecialMenuItems([
            'brand' => $drillData['brand'] ?? [],
            'menuData' => $drillData['menu'] ?? [],
            'progressDrillData' => [
                'title' => 'Progress Drill',
                'subtitle' => 'Statistic Overview',
                'fiscalYears' => [2025, 2026, 2027],
                'selectedFY' => 2026,
                'periods' => array_keys($drillData['dashboard']['periodData'] ?? ['1st Half' => []]),
                'selectedPeriod' => $drillData['dashboard']['selectedPeriod'] ?? '1st Half',
                'periodData' => $drillData['dashboard']['periodData'] ?? [],
                'companies' => ['DNIA', 'DMIA', 'DSIA', 'HDI'],
                'selectedCompany' => 'DNIA',
                'buCodes' => ['3300 - IS', '3301 - IS SUNTER', '3302 - IS BEKASI', '3941 - IS DPIA'],
                'selectedBuCode' => '3300 - IS',
                'drillHistory' => $drillData['myResult']['drillHistory'] ?? [],
            ],
            'educationData' => $drillData['education'] ?? [],
            'user' => $authUser,
        ], $authUser);
    }

    public function getMyResultPayload(array $authUser): array
    {
        if ($this->isDatabaseEnabled()) {
            return $this->getMyResultPayloadFromDatabase($authUser);
        }

        $drillData = $this->getDrillDataFromJson();

        return $this->appendSpecialMenuItems([
            'brand' => $drillData['brand'] ?? [],
            'menuData' => $drillData['menu'] ?? [],
            'myResultData' => $drillData['myResult'] ?? [],
            'educationData' => $drillData['education'] ?? [],
            'user' => $authUser,
        ], $authUser);
    }

    private function appendSpecialMenuItems(array $payload, array $authUser): array
    {
        $username = strtolower((string) ($authUser['username'] ?? ''));

        if (!empty($authUser['isSpecial']) && $username !== 'dnia.admin') {
            $payload['menuData']['items'][] = [
                'title'    => 'Progress Drill',
                'subtitle' => 'Track your training progress',
                'url'      => '/progress-drill',
                'symbol'   => 'PRG',
            ];
        }

        return $payload;
    }

    private function isDatabaseEnabled(): bool
    {
        if (!config('database.use_database', false)) {
            return false;
        }

        return Schema::hasTable('users')
            && Schema::hasTable('education_videos')
            && Schema::hasTable('drill_simulations');
    }

    private function getLandingDataFromDatabase(): array
    {
        $brand = Brand::query()->first();
        $periodStats = DashboardPeriodStat::query()
            ->with('details')
            ->orderBy('period_label')
            ->get();

        $periodData = [];
        $periodOptions = [];
        foreach ($periodStats as $stat) {
            $periodOptions[] = $stat->period_label;
            $periodData[$stat->period_label] = [
                'target' => (int) $stat->target,
                'actual' => (int) $stat->actual,
                'percentage' => (int) $stat->percentage,
                'stats' => $stat->details->map(function ($detail) {
                    return [
                        'label' => $detail->label,
                        'actual' => (int) $detail->actual,
                        'target' => (int) $detail->target,
                    ];
                })->values()->all(),
            ];
        }

        $selectedPeriod = $periodOptions[0] ?? '1st Half';

        $authUsers = User::query()
            ->orderBy('id')
            ->limit(5)
            ->get()
            ->map(function (User $user) {
                return [
                    'username' => (string) ($user->username ?: $user->email),
                    'password' => '********',
                    'name' => (string) $user->name,
                    'employeeId' => (string) ($user->employee_id ?: '-'),
                    'company' => (string) ($user->company ?: '-'),
                    'businessUnit' => (string) ($user->business_unit ?: '-'),
                    'email' => (string) ($user->email ?: '-'),
                ];
            })->values()->all();

        return [
            'brand' => [
                'name' => $brand->name ?? 'DENSO',
                'tagline' => $brand->tagline ?? 'Crafting the Core',
            ],
            'auth' => [
                'users' => $authUsers,
            ],
            'dashboard' => [
                'title' => 'Drill Simulation',
                'subtitle' => 'Self-Service Cyber Attack',
                'periodOptions' => $periodOptions,
                'selectedPeriod' => $selectedPeriod,
                'periodData' => $periodData,
            ],
        ];
    }

    private function authenticateWithDatabase(Request $request): ?array
    {
        $username = strtolower((string) $request->input('username'));
        $password = (string) $request->input('password');

        $user = User::query()
            ->whereRaw('LOWER(username) = ?', [$username])
            ->first();

        if (!$user) {
            return null;
        }

        $validPassword = Hash::check($password, (string) $user->password)
            || hash_equals((string) $user->password, $password);

        if (!$validPassword) {
            return null;
        }

        $resolvedUsername = (string) ($user->username ?: $user->email);

        return [
            'id' => (int) $user->id,
            'username' => $resolvedUsername,
            'name' => (string) $user->name,
            'employeeId' => (string) ($user->employee_id ?: '-'),
            'company' => (string) ($user->company ?: '-'),
            'businessUnit' => (string) ($user->business_unit ?: '-'),
            'email' => (string) ($user->email ?: '-'),
            'isSpecial' => strtolower($resolvedUsername) === 'selamet.nuryanto',
        ];
    }

    private function getMenuPayloadFromDatabase(array $authUser): array
    {
        $brand = Brand::query()->first();
        $items = MenuItem::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (MenuItem $item) {
                return [
                    'title' => $item->title,
                    'subtitle' => $item->subtitle,
                    'url' => $item->url,
                    'symbol' => $item->symbol,
                ];
            })->values()->all();

        return $this->appendSpecialMenuItems([
            'brand' => [
                'name' => $brand->name ?? 'DENSO',
                'tagline' => $brand->tagline ?? 'Crafting the Core',
            ],
            'menuData' => [
                'title' => 'Main Menu',
                'welcomeTitle' => 'Welcome Drill Simulation',
                'welcomeSubtitle' => 'Self-Service Cyber Attack',
                'items' => $items,
            ],
            'user' => $authUser,
        ], $authUser);
    }

    private function getEducationPayloadFromDatabase(array $authUser, int $videoId = 0): array
    {
        $userId = (int) ($authUser['id'] ?? 0);

        $brand = Brand::query()->first();
        $menuItems = MenuItem::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $videos = EducationVideo::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($videos->isEmpty()) {
            return $this->appendSpecialMenuItems([
                'brand' => [
                    'name' => $brand->name ?? 'DENSO',
                    'tagline' => $brand->tagline ?? 'Crafting the Core',
                ],
                'menuData' => [
                    'items' => $menuItems->map(function (MenuItem $item) {
                        return [
                            'title' => $item->title,
                            'subtitle' => $item->subtitle,
                            'url' => $item->url,
                            'symbol' => $item->symbol,
                        ];
                    })->values()->all(),
                ],
                'educationData' => [
                    'title' => 'Education',
                    'subtitle' => 'Upload Video on Devas and embed link to show web',
                    'searchPlaceholder' => 'Search education ...',
                    'progressLabel' => 'Education Material',
                    'progressNote' => 'Track All Ongoing education material',
                    'videos' => [],
                ],
                'user' => $authUser,
            ], $authUser);
        }

        $targetVideo = $videoId > 0
            ? $videos->firstWhere('id', $videoId)
            : $videos->first();

        if ($targetVideo && $userId > 0) {
            UserVideoProgress::query()->updateOrCreate(
                [
                    'user_id' => $userId,
                    'video_id' => $targetVideo->id,
                ],
                [
                    'watched' => true,
                    'watched_at' => now(),
                ]
            );
        }

        $progressByVideo = UserVideoProgress::query()
            ->where('user_id', $userId)
            ->pluck('watched', 'video_id');

        $serializedVideos = $videos->map(function (EducationVideo $video) use ($progressByVideo) {
            return [
                'id' => (int) $video->id,
                'title' => $video->title,
                'embedUrl' => $video->embed_url,
                'watched' => (bool) ($progressByVideo[$video->id] ?? false),
            ];
        })->values()->all();

        return $this->appendSpecialMenuItems([
            'brand' => [
                'name' => $brand->name ?? 'DENSO',
                'tagline' => $brand->tagline ?? 'Crafting the Core',
            ],
            'menuData' => [
                'items' => $menuItems->map(function (MenuItem $item) {
                    return [
                        'title' => $item->title,
                        'subtitle' => $item->subtitle,
                        'url' => $item->url,
                        'symbol' => $item->symbol,
                    ];
                })->values()->all(),
            ],
            'educationData' => [
                'title' => 'Education',
                'subtitle' => 'Upload Video on Devas and embed link to show web',
                'searchPlaceholder' => 'Search education ...',
                'progressLabel' => 'Education Material',
                'progressNote' => 'Track All Ongoing education material',
                'videos' => $serializedVideos,
            ],
            'user' => $authUser,
        ], $authUser);
    }

    private function getDrillPayloadFromDatabase(array $authUser): array
    {
        $userId = (int) ($authUser['id'] ?? 0);
        $brand = Brand::query()->first();

        $menuItems = MenuItem::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $videos = EducationVideo::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $progressByVideo = UserVideoProgress::query()
            ->where('user_id', $userId)
            ->pluck('watched', 'video_id');

        $serializedVideos = $videos->map(function (EducationVideo $video) use ($progressByVideo) {
            return [
                'id' => (int) $video->id,
                'title' => $video->title,
                'embedUrl' => $video->embed_url,
                'watched' => (bool) ($progressByVideo[$video->id] ?? false),
            ];
        })->values()->all();

        $completedByDrill = UserDrillCompletion::query()
            ->where('user_id', $userId)
            ->pluck('id', 'drill_simulation_id');

        $drills = DrillSimulation::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (DrillSimulation $drill) use ($completedByDrill) {
                return [
                    'id' => (int) $drill->id,
                    'title' => $drill->title,
                    'description' => $drill->description,
                    'notifyNote' => $drill->notify_note,
                    'duration' => $drill->duration_label,
                    'periodStart' => $this->formatHumanDate($drill->period_start),
                    'periodEnd' => $this->formatHumanDate($drill->period_end),
                    'completed' => isset($completedByDrill[$drill->id]),
                    'comingSoon' => (bool) $drill->coming_soon,
                ];
            })->values()->all();

        return $this->appendSpecialMenuItems([
            'brand' => [
                'name' => $brand->name ?? 'DENSO',
                'tagline' => $brand->tagline ?? 'Crafting the Core',
            ],
            'menuData' => [
                'items' => $menuItems->map(function (MenuItem $item) {
                    return [
                        'title' => $item->title,
                        'subtitle' => $item->subtitle,
                        'url' => $item->url,
                        'symbol' => $item->symbol,
                    ];
                })->values()->all(),
            ],
            'drillSimData' => [
                'title' => 'Drill Simulation',
                'subtitle' => 'Self-Service Cyber Attack',
                'drills' => $drills,
            ],
            'educationData' => [
                'videos' => $serializedVideos,
            ],
            'user' => $authUser,
        ], $authUser);
    }

    private function completeDrillInDatabase(array $authUser, int $drillId): void
    {
        $userId = (int) ($authUser['id'] ?? 0);
        if ($userId <= 0) {
            return;
        }

        DB::transaction(function () use ($userId, $drillId) {
            UserDrillCompletion::query()->updateOrCreate(
                [
                    'user_id' => $userId,
                    'drill_simulation_id' => $drillId,
                ],
                [
                    'completed_at' => now(),
                    'status' => 'Passed',
                ]
            );
        });
    }

    private function getMyResultPayloadFromDatabase(array $authUser): array
    {
        $userId = (int) ($authUser['id'] ?? 0);
        $brand = Brand::query()->first();

        $menuItems = MenuItem::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $videos = EducationVideo::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $watchedByVideo = UserVideoProgress::query()
            ->where('user_id', $userId)
            ->pluck('watched', 'video_id');

        $watchedCount = collect($watchedByVideo)->filter()->count();
        $totalVideos = $videos->count();

        $drillRows = UserDrillCompletion::query()
            ->with('drill')
            ->where('user_id', $userId)
            ->orderByDesc('completed_at')
            ->get();

        $drillHistory = $drillRows->map(function (UserDrillCompletion $row) {
            $completedAt = $row->completed_at;

            return [
                'drillName' => $row->drill->title ?? 'Drill',
                'category' => 'Ransomware',
                'date' => $completedAt ? $completedAt->toDateString() : '-',
                'time' => $completedAt ? $completedAt->format('H:i') : '-',
                'responseTime' => $row->response_time ?: '-',
                'score' => $row->score ?? '-',
                'status' => $row->status ?: 'Passed',
            ];
        })->values()->all();

        $device = UserDeviceInfo::query()->where('user_id', $userId)->first();
        $drillsCompleted = $drillRows->count();
        $drillTotal = DrillSimulation::query()->where('coming_soon', false)->count();

        $status = 'Not Yet';
        if ($drillsCompleted > 0 && $drillTotal > 0) {
            $status = $drillsCompleted >= $drillTotal ? 'Passed' : 'In Progress';
        }

        return $this->appendSpecialMenuItems([
            'brand' => [
                'name' => $brand->name ?? 'DENSO',
                'tagline' => $brand->tagline ?? 'Crafting the Core',
            ],
            'menuData' => [
                'items' => $menuItems->map(function (MenuItem $item) {
                    return [
                        'title' => $item->title,
                        'subtitle' => $item->subtitle,
                        'url' => $item->url,
                        'symbol' => $item->symbol,
                    ];
                })->values()->all(),
            ],
            'myResultData' => [
                'title' => 'Performance Analytics',
                'subtitle' => 'View your detailed drill history.',
                'fiscalYears' => [2025, 2026, 2027],
                'selectedFY' => 2026,
                'periods' => ['1st Half', '2nd Half'],
                'selectedPeriod' => '1st Half',
                'attendance' => [
                    'completed' => $drillsCompleted,
                    'total' => max($drillTotal, 1),
                ],
                'drillsCompleted' => $drillsCompleted,
                'status' => $status,
                'device' => [
                    'computerName' => $device->computer_name ?? 'N/A',
                    'ipAddress' => $device->ip_address ?? 'N/A',
                    'plant' => $device->plant ?? 'N/A',
                    'location' => $device->location ?? 'N/A',
                ],
                'drillHistory' => $drillHistory,
            ],
            'educationData' => [
                'videos' => $videos->map(function (EducationVideo $video) use ($watchedByVideo) {
                    return [
                        'id' => (int) $video->id,
                        'title' => $video->title,
                        'embedUrl' => $video->embed_url,
                        'watched' => (bool) ($watchedByVideo[$video->id] ?? false),
                    ];
                })->values()->all(),
            ],
            'user' => $authUser,
        ], $authUser);
    }

    private function getDrillDataFromJson(): array
    {
        $jsonPath = public_path('data/drill-dashboard.json');

        if (!file_exists($jsonPath)) {
            return [];
        }

        $decoded = json_decode(file_get_contents($jsonPath), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function authenticateWithJson(Request $request): ?array
    {
        $drillData = $this->getDrillDataFromJson();
        $authUsers = $drillData['auth']['users'] ?? [];
        $inputUsername = (string) $request->input('username');
        $inputPassword = (string) $request->input('password');

        foreach ($authUsers as $user) {
            $username = (string) ($user['username'] ?? '');
            $password = (string) ($user['password'] ?? '');

            if (
                hash_equals(strtolower($username), strtolower($inputUsername))
                && hash_equals($password, $inputPassword)
            ) {
                return [
                    'username' => $username,
                    'name' => (string) ($user['name'] ?? $username),
                    'employeeId' => (string) ($user['employeeId'] ?? '-'),
                    'company' => (string) ($user['company'] ?? '-'),
                    'businessUnit' => (string) ($user['businessUnit'] ?? '-'),
                    'email' => (string) ($user['email'] ?? '-'),
                    'isSpecial' => (bool) ($user['isSpecial'] ?? false),
                ];
            }
        }

        return null;
    }

    private function getEducationPayloadFromJson(array $authUser, int $videoId = 0): array
    {
        $drillData = $this->getDrillDataFromJson();
        $jsonPath = public_path('data/drill-dashboard.json');

        $videos = &$drillData['education']['videos'];
        $targetVideoId = $videoId > 0
            ? $videoId
            : (int) ($videos[0]['id'] ?? 1);

        $updated = false;
        foreach ($videos as &$video) {
            if ((int) ($video['id'] ?? 0) === $targetVideoId && !($video['watched'] ?? false)) {
                $video['watched'] = true;
                $updated = true;
                break;
            }
        }
        unset($video);

        if ($updated) {
            file_put_contents($jsonPath, json_encode($drillData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        return $this->appendSpecialMenuItems([
            'brand' => $drillData['brand'] ?? [],
            'menuData' => $drillData['menu'] ?? [],
            'educationData' => $drillData['education'] ?? [],
            'user' => $authUser,
        ], $authUser);
    }

    private function completeDrillInJson(int $drillId): void
    {
        $jsonPath = public_path('data/drill-dashboard.json');
        $drillData = $this->getDrillDataFromJson();

        $drills = &$drillData['drillSimulation']['drills'];
        foreach ($drills as &$drill) {
            if ((int) ($drill['id'] ?? 0) === $drillId) {
                $drill['completed'] = true;
                break;
            }
        }
        unset($drill);

        file_put_contents($jsonPath, json_encode($drillData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function getProgressDrillPayloadFromDatabase(array $authUser): array
    {
        $brand = Brand::query()->first();

        $menuItems = MenuItem::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $periodStats = DashboardPeriodStat::query()
            ->with('details')
            ->orderBy('period_label')
            ->get();

        $periodData = [];
        $periodOptions = [];
        foreach ($periodStats as $stat) {
            $periodOptions[] = $stat->period_label;
            $periodData[$stat->period_label] = [
                'target' => (int) $stat->target,
                'actual' => (int) $stat->actual,
                'percentage' => (int) $stat->percentage,
                'stats' => $stat->details->map(function ($detail) {
                    return [
                        'label' => $detail->label,
                        'actual' => (int) $detail->actual,
                        'target' => (int) $detail->target,
                    ];
                })->values()->all(),
            ];
        }

        $selectedPeriod = $periodOptions[0] ?? '1st Half';

        $companies = \App\Models\User::query()->distinct()->pluck('company')->filter()->values()->all();
        $buCodes = \App\Models\User::query()->distinct()->pluck('business_unit')->filter()->values()->all();

        return $this->appendSpecialMenuItems([
            'brand' => [
                'name' => $brand->name ?? 'DENSO',
                'tagline' => $brand->tagline ?? 'Crafting the Core',
            ],
            'menuData' => [
                'items' => $menuItems->map(function (MenuItem $item) {
                    return [
                        'title' => $item->title,
                        'subtitle' => $item->subtitle,
                        'url' => $item->url,
                        'symbol' => $item->symbol,
                    ];
                })->values()->all(),
            ],
            'progressDrillData' => [
                'title' => 'Progress Drill',
                'subtitle' => 'Statistic Overview',
                'fiscalYears' => [2025, 2026, 2027],
                'selectedFY' => 2026,
                'periods' => $periodOptions,
                'selectedPeriod' => $selectedPeriod,
                'periodData' => $periodData,
                'companies' => !empty($companies) ? $companies : ['DNIA', 'DMIA', 'DSIA', 'HDI'],
                'selectedCompany' => $companies[0] ?? 'DNIA',
                'buCodes' => !empty($buCodes) ? $buCodes : ['3300 - IS', '3301 - IS SUNTER', '3302 - IS BEKASI', '3941 - IS DPIA'],
                'selectedBuCode' => $buCodes[0] ?? '3300 - IS',
                'drillHistory' => [],
            ],
            'educationData' => ['videos' => []],
            'user' => $authUser,
        ], $authUser);
    }

    public function seedFromJsonIfEmpty(): void
    {
        if (!$this->isDatabaseEnabled()) {
            return;
        }

        if (Brand::query()->exists()) {
            return;
        }

        $source = $this->getDrillDataFromJson();

        DB::transaction(function () use ($source) {
            $brand = $source['brand'] ?? [];
            Brand::query()->create([
                'name' => (string) ($brand['name'] ?? 'DENSO'),
                'tagline' => (string) ($brand['tagline'] ?? 'Crafting the Core'),
            ]);

            $menuItems = $source['menu']['items'] ?? [];
            foreach ($menuItems as $index => $item) {
                MenuItem::query()->create([
                    'title' => (string) ($item['title'] ?? 'Menu Item'),
                    'subtitle' => (string) ($item['subtitle'] ?? null),
                    'url' => (string) ($item['url'] ?? '#'),
                    'symbol' => (string) ($item['symbol'] ?? 'ITM'),
                    'sort_order' => $index,
                ]);
            }

            $videos = $source['education']['videos'] ?? [];
            foreach ($videos as $index => $video) {
                EducationVideo::query()->create([
                    'id' => (int) ($video['id'] ?? $index + 1),
                    'title' => (string) ($video['title'] ?? 'Video'),
                    'embed_url' => (string) ($video['embedUrl'] ?? ''),
                    'sort_order' => $index,
                ]);
            }

            $drills = $source['drillSimulation']['drills'] ?? [];
            foreach ($drills as $index => $drill) {
                DrillSimulation::query()->create([
                    'id' => (int) ($drill['id'] ?? $index + 1),
                    'title' => (string) ($drill['title'] ?? 'Drill'),
                    'description' => (string) ($drill['description'] ?? ''),
                    'notify_note' => (string) ($drill['notifyNote'] ?? ''),
                    'duration_label' => (string) ($drill['duration'] ?? ''),
                    'period_start' => $this->parseHumanDate($drill['periodStart'] ?? null),
                    'period_end' => $this->parseHumanDate($drill['periodEnd'] ?? null),
                    'coming_soon' => (bool) ($drill['comingSoon'] ?? false),
                    'sort_order' => $index,
                ]);
            }

            $dashboard = $source['dashboard'] ?? [];
            $periodData = $dashboard['periodData'] ?? [];
            foreach ($periodData as $periodLabel => $periodStat) {
                $stat = DashboardPeriodStat::query()->create([
                    'fiscal_year' => 2026,
                    'period_label' => (string) $periodLabel,
                    'target' => (int) ($periodStat['target'] ?? 0),
                    'actual' => (int) ($periodStat['actual'] ?? 0),
                    'percentage' => (int) ($periodStat['percentage'] ?? 0),
                ]);

                $details = $periodStat['stats'] ?? [];
                foreach ($details as $detail) {
                    $stat->details()->create([
                        'label' => (string) ($detail['label'] ?? '-'),
                        'actual' => (int) ($detail['actual'] ?? 0),
                        'target' => (int) ($detail['target'] ?? 0),
                    ]);
                }
            }

            $users = $source['auth']['users'] ?? [];
            foreach ($users as $jsonUser) {
                $user = User::query()->updateOrCreate(
                    [
                        'username' => (string) ($jsonUser['username'] ?? ''),
                    ],
                    [
                        'name' => (string) ($jsonUser['name'] ?? 'User'),
                        'email' => (string) ($jsonUser['email'] ?? null),
                        'employee_id' => (string) ($jsonUser['employeeId'] ?? null),
                        'company' => (string) ($jsonUser['company'] ?? null),
                        'business_unit' => (string) ($jsonUser['businessUnit'] ?? null),
                        'password' => Hash::make((string) ($jsonUser['password'] ?? 'password')),
                    ]
                );

                foreach ($videos as $video) {
                    $videoId = (int) ($video['id'] ?? 0);
                    if ($videoId <= 0) {
                        continue;
                    }

                    UserVideoProgress::query()->updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'video_id' => $videoId,
                        ],
                        [
                            'watched' => (bool) ($video['watched'] ?? false),
                            'watched_at' => ($video['watched'] ?? false) ? now() : null,
                        ]
                    );
                }

                $device = $source['myResult']['device'] ?? [];
                UserDeviceInfo::query()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'computer_name' => (string) ($device['computerName'] ?? null),
                        'ip_address' => (string) ($device['ipAddress'] ?? null),
                        'plant' => (string) ($device['plant'] ?? null),
                        'location' => (string) ($device['location'] ?? null),
                    ]
                );

                foreach ($drills as $drill) {
                    if (!($drill['completed'] ?? false)) {
                        continue;
                    }

                    $drillId = (int) ($drill['id'] ?? 0);
                    if ($drillId <= 0) {
                        continue;
                    }

                    UserDrillCompletion::query()->updateOrCreate(
                        [
                            'user_id' => $user->id,
                            'drill_simulation_id' => $drillId,
                        ],
                        [
                            'completed_at' => now(),
                            'status' => 'Passed',
                        ]
                    );
                }
            }
        });
    }

    private function parseHumanDate(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::createFromFormat('j M Y', $value)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function formatHumanDate($value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value)->format('j M Y');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
