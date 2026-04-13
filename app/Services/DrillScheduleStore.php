<?php

namespace App\Services;

/**
 * DrillScheduleStore
 *
 * Manages the drill schedule JSON file stored at storage/app/drill-schedules.json
 *
 * JSON structure:
 * {
 *   "next_id": 3,
 *   "self_service": {
 *     "first_half":  { "start_date": "2026-04-01", "start_time": "09:00", "end_date": "2026-06-30", "end_time": "17:00", "duration": 10 },
 *     "second_half": { "start_date": "2026-10-01", "start_time": "09:00", "end_date": "2026-12-31", "end_time": "17:00", "duration": 10 }
 *   },
 *   "schedule_drill": {
 *     "company": "DNIA",
 *     "plant": "Bekasi",
 *     "duration": 30,
 *     "date": "2026-04-20",
 *     "time": "11:00"
 *   },
 *   "drills": [
 *     {
 *       "id": 1,
 *       "company": "PT. Denso Indonesia",
 *       "plant": "DMIA2",
 *       "date": "2026-08-19",
 *       "time": "09:30",
 *       "duration": 30,
 *       "created_at": "2026-04-13 10:00:00"
 *     }
 *   ]
 * }
 */
class DrillScheduleStore
{
    private string $path;

    private array $defaultSelfService = [
        'first_half' => [
            'start_date' => '2026-04-01',
            'start_time' => '09:00',
            'end_date'   => '2026-06-30',
            'end_time'   => '17:00',
            'duration'   => 10,
        ],
        'second_half' => [
            'start_date' => '2026-10-01',
            'start_time' => '09:00',
            'end_date'   => '2026-12-31',
            'end_time'   => '17:00',
            'duration'   => 10,
        ],
    ];

    private array $defaultScheduleDrill = [
        'company'  => 'DNIA',
        'plant'    => 'Bekasi',
        'duration' => 30,
        'date'     => '',
        'time'     => '11:00',
    ];

    private array $defaultDrills = [
        [
            'id'         => 1,
            'company'    => 'PT. Denso Manufacturing Indonesia',
            'plant'      => 'DMIA2',
            'date'       => '2026-08-19',
            'time'       => '09:30',
            'duration'   => 30,
            'created_at' => '2026-04-13 10:00:00',
        ],
        [
            'id'         => 2,
            'company'    => 'PT. Denso Indonesia',
            'plant'      => 'Fajar',
            'date'       => '2026-04-20',
            'time'       => '15:30',
            'duration'   => 30,
            'created_at' => '2026-04-13 10:00:00',
        ],
        [
            'id'         => 3,
            'company'    => 'PT. Denso Sales Indonesia',
            'plant'      => 'SUNTER',
            'date'       => '2025-02-28',
            'time'       => '09:30',
            'duration'   => 30,
            'created_at' => '2025-02-01 10:00:00',
        ],
        [
            'id'         => 4,
            'company'    => 'PT. Hamaden Indonesia',
            'plant'      => 'BEKASI',
            'date'       => '2025-01-20',
            'time'       => '09:00',
            'duration'   => 30,
            'created_at' => '2025-01-01 10:00:00',
        ],
    ];

    public function __construct()
    {
        $this->path = storage_path('app/drill-schedules.json');
    }

    // ── Self-service window ────────────────────────────────────────

    public function getSelfService(): array
    {
        return $this->read()['self_service'] ?? $this->defaultSelfService;
    }

    public function saveSelfService(array $data): void
    {
        $store = $this->read();
        $store['self_service'] = [
            'first_half' => [
                'start_date' => $data['first_half_start_date'] ?? '',
                'start_time' => $data['first_half_start_time'] ?? '',
                'end_date'   => $data['first_half_end_date'] ?? '',
                'end_time'   => $data['first_half_end_time'] ?? '',
                'duration'   => (int) ($data['first_half_duration'] ?? 10),
            ],
            'second_half' => [
                'start_date' => $data['second_half_start_date'] ?? '',
                'start_time' => $data['second_half_start_time'] ?? '',
                'end_date'   => $data['second_half_end_date'] ?? '',
                'end_time'   => $data['second_half_end_time'] ?? '',
                'duration'   => (int) ($data['second_half_duration'] ?? 10),
            ],
        ];
        $this->write($store);
    }

    // ── Schedule Drill (factory-wide) ──────────────────────────────

    public function getScheduleDrill(): array
    {
        return $this->read()['schedule_drill'] ?? $this->defaultScheduleDrill;
    }

    public function saveScheduleDrill(array $data): void
    {
        $store = $this->read();
        $store['schedule_drill'] = [
            'company'  => (string) ($data['company'] ?? ''),
            'plant'    => (string) ($data['plant'] ?? ''),
            'duration' => (int)   ($data['duration'] ?? 30),
            'date'     => (string) ($data['date'] ?? ''),
            'time'     => (string) ($data['time'] ?? ''),
        ];
        $this->write($store);
    }

    // ── Drill list ─────────────────────────────────────────────────

    public function allDrills(): array
    {
        return $this->read()['drills'] ?? [];
    }

    public function createDrill(array $fields): array
    {
        $store  = $this->read();
        $nextId = (int) ($store['next_id'] ?? (count($store['drills'] ?? []) + 1));

        $record = [
            'id'         => $nextId,
            'company'    => (string) ($fields['company'] ?? ''),
            'plant'      => (string) ($fields['plant'] ?? ''),
            'date'       => (string) ($fields['date'] ?? ''),
            'time'       => (string) ($fields['time'] ?? ''),
            'duration'   => (int)   ($fields['duration'] ?? 30),
            'created_at' => now()->format('Y-m-d H:i:s'),
        ];

        $store['drills'][]  = $record;
        $store['next_id']   = $nextId + 1;
        $this->write($store);

        return $record;
    }

    public function updateDrill(int $id, array $fields): bool
    {
        $store   = $this->read();
        $updated = false;

        foreach ($store['drills'] as &$drill) {
            if ((int) $drill['id'] === $id) {
                $drill['company']  = (string) ($fields['company'] ?? $drill['company']);
                $drill['plant']    = (string) ($fields['plant']   ?? $drill['plant']);
                $drill['date']     = (string) ($fields['date']    ?? $drill['date']);
                $drill['time']     = (string) ($fields['time']    ?? $drill['time']);
                $drill['duration'] = (int)   ($fields['duration'] ?? $drill['duration']);
                $updated = true;
                break;
            }
        }
        unset($drill);

        if ($updated) {
            $this->write($store);
        }

        return $updated;
    }

    public function deleteDrill(int $id): bool
    {
        $store     = $this->read();
        $before    = count($store['drills'] ?? []);
        $store['drills'] = array_values(
            array_filter($store['drills'] ?? [], fn($d) => (int) $d['id'] !== $id)
        );

        if (count($store['drills']) === $before) {
            return false;
        }

        $this->write($store);
        return true;
    }

    // ── Sync back to drill-dashboard.json (user-facing drill page) ─

    /**
     * Update drill-dashboard.json drill[0] (1st Half active drill) with the
     * self-service window settings saved by the admin.
     */
    public function syncSelfServiceToDrillDashboard(array $firstHalf): void
    {
        $path = public_path('data/drill-dashboard.json');
        if (!file_exists($path)) {
            return;
        }

        $data = json_decode(file_get_contents($path), true);
        if (!is_array($data)) {
            return;
        }

        $drills = &$data['drillSimulation']['drills'];
        if (empty($drills[0])) {
            return;
        }

        // Format dates as human-readable: "1 Apr 2026"
        $fmt = function (string $date): string {
            try {
                return \Carbon\Carbon::parse($date)->format('j M Y');
            } catch (\Exception $e) {
                return $date;
            }
        };

        $drills[0]['periodStart'] = $fmt($firstHalf['start_date'] ?? '');
        $drills[0]['periodEnd']   = $fmt($firstHalf['end_date']   ?? '');
        $drills[0]['duration']    = ($firstHalf['duration'] ?? 10) . ' Minutes';

        file_put_contents(
            $path,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }

    /**
     * Update drill-dashboard.json drill[1] (the "comingSoon" entry) with the
     * scheduled drill settings, and also add the new entry to the drill list.
     */
    public function saveScheduleDrillAndSync(array $data): array
    {
        // 1. Save to schedule store
        $this->saveScheduleDrill($data);

        // 2. Add new entry to the drill list
        $record = $this->createDrill($data);

        // 3. Sync to drill-dashboard.json "comingSoon" card
        $path = public_path('data/drill-dashboard.json');
        if (file_exists($path)) {
            $json = json_decode(file_get_contents($path), true);
            if (is_array($json)) {
                $drills = &$json['drillSimulation']['drills'];

                // Find the comingSoon entry and update it, or update index [1]
                $updated = false;
                foreach ($drills as &$drill) {
                    if (!empty($drill['comingSoon'])) {
                        $drill['periodStart'] = $data['date'] ?? '';
                        $drill['periodEnd']   = $data['date'] ?? '';
                        $drill['duration']    = ($data['duration'] ?? 30) . ' Minutes';
                        $drill['notifyNote']  = 'Company: ' . ($data['company'] ?? '') . ' — Plant: ' . ($data['plant'] ?? '');
                        $updated = true;
                        break;
                    }
                }
                unset($drill);

                if (!$updated && isset($drills[1])) {
                    $drills[1]['periodStart'] = $data['date'] ?? '';
                    $drills[1]['periodEnd']   = $data['date'] ?? '';
                    $drills[1]['duration']    = ($data['duration'] ?? 30) . ' Minutes';
                }

                file_put_contents(
                    $path,
                    json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                );
            }
        }

        return $record;
    }

    // ── Internal ───────────────────────────────────────────────────

    private function read(): array
    {
        if (!file_exists($this->path)) {
            return $this->defaultData();
        }

        $raw     = file_get_contents($this->path);
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : $this->defaultData();
    }

    private function write(array $data): void
    {
        file_put_contents(
            $this->path,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }

    private function defaultData(): array
    {
        return [
            'next_id'        => count($this->defaultDrills) + 1,
            'self_service'   => $this->defaultSelfService,
            'schedule_drill' => $this->defaultScheduleDrill,
            'drills'         => $this->defaultDrills,
        ];
    }
}
