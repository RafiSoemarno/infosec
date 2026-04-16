<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Handles admin creation, editing, publishing, and deletion of education materials.
 * Materials are link-based (SharePoint / embed URLs) — no file uploads.
 * All metadata lives in public/data/drill-dashboard.json under education.videos.
 *
 * Statuses:
 *   draft     – saved by admin, not visible to users
 *   published – uploaded/published, visible in the User education page
 */
class EducationMaterialController extends Controller
{
    private const DASH_JSON = 'data/drill-dashboard.json';

    // ── Store (Save Video — draft) ─────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        if (!$this->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'title'      => ['required', 'string', 'max:255'],
            'video_link' => ['required', 'string', 'max:2048'],
        ]);

        $uploadedBy = session('auth_user.username', 'admin');

        $newVideo = [
            'title'      => $request->input('title'),
            'embedUrl'   => $request->input('video_link'),
            'fileType'   => 'video/link',
            'status'     => 'draft',
            'uploadedBy' => $uploadedBy,
            'watched'    => false,
            'created_at' => now()->toIso8601String(),
        ];

        $this->appendToDashboard([$newVideo]);

        return redirect('/education')
            ->with('success', 'Video "' . $request->input('title') . '" saved as draft.');
    }

    // ── Update (Edit title / link) ─────────────────────────────────

    public function update(Request $request, int $id): RedirectResponse
    {
        if (!$this->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'title'      => ['required', 'string', 'max:255'],
            'video_link' => ['nullable', 'string', 'max:2048'],
        ]);

        $dash   = $this->readDashboard();
        $videos = $dash['education']['videos'] ?? [];
        $found  = false;

        foreach ($videos as &$v) {
            if ((int) ($v['id'] ?? 0) === $id) {
                $v['title'] = $request->input('title');
                if ($request->filled('video_link')) {
                    $v['embedUrl'] = $request->input('video_link');
                }
                $found = true;
                break;
            }
        }
        unset($v);

        if (!$found) {
            return redirect('/education')->with('success', 'Material not found.');
        }

        $dash['education']['videos'] = $videos;
        $this->writeDashboard($dash);

        return redirect('/education')
            ->with('success', 'Material updated successfully.');
    }

    // ── Publish (Upload Video — draft → published) ─────────────────

    public function publish(int $id): RedirectResponse
    {
        if (!$this->isAdmin()) {
            abort(403);
        }

        $dash   = $this->readDashboard();
        $videos = $dash['education']['videos'] ?? [];
        $found  = false;

        foreach ($videos as &$v) {
            if ((int) ($v['id'] ?? 0) === $id) {
                $v['status'] = 'published';
                $found = true;
                break;
            }
        }
        unset($v);

        if (!$found) {
            return redirect('/education')->with('success', 'Material not found.');
        }

        $dash['education']['videos'] = $videos;
        $this->writeDashboard($dash);

        return redirect('/education')
            ->with('success', 'Video published and is now visible to users.');
    }

    // ── Destroy ────────────────────────────────────────────────────

    public function destroy(int $id): RedirectResponse
    {
        if (!$this->isAdmin()) {
            abort(403);
        }

        $dash      = $this->readDashboard();
        $videos    = $dash['education']['videos'] ?? [];
        $target    = null;
        $remaining = [];

        foreach ($videos as $v) {
            if ((int) ($v['id'] ?? 0) === $id) {
                $target = $v;
            } else {
                $remaining[] = $v;
            }
        }

        if (!$target) {
            return redirect('/education')->with('success', 'Material already removed.');
        }

        // Delete physical files for legacy file-based uploads only
        if (!empty($target['fileType']) && $target['fileType'] !== 'video/link' && !empty($target['embedUrl'])) {
            $embedUrl = $target['embedUrl'];
            if (str_starts_with($embedUrl, '/edu_material/')) {
                $filePath = public_path(ltrim($embedUrl, '/'));
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            } elseif (str_starts_with($embedUrl, '/storage/')) {
                $storagePath = preg_replace('#^/storage/#', '', $embedUrl);
                Storage::disk('public')->delete($storagePath);
            }
        }

        $dash['education']['videos'] = $remaining;
        $this->writeDashboard($dash);

        return redirect('/education')
            ->with('success', 'Material deleted successfully.');
    }

    // ── Helpers ────────────────────────────────────────────────────

    private function appendToDashboard(array $newVideos): void
    {
        $dash   = $this->readDashboard();
        $nextId = (int) ($dash['education']['next_id'] ?? 1);

        foreach ($newVideos as $v) {
            $v['id']                       = $nextId;
            $dash['education']['videos'][] = $v;
            $nextId++;
        }

        $dash['education']['next_id'] = $nextId;
        $this->writeDashboard($dash);
    }

    private function readDashboard(): array
    {
        $path    = public_path(self::DASH_JSON);
        $decoded = file_exists($path) ? json_decode(file_get_contents($path), true) : [];
        return is_array($decoded) ? $decoded : [];
    }

    private function writeDashboard(array $data): void
    {
        file_put_contents(
            public_path(self::DASH_JSON),
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }

    private function isAdmin(): bool
    {
        $user = session('auth_user');
        return !empty($user) && ($user['role'] ?? '') === 'admin';
    }
}
