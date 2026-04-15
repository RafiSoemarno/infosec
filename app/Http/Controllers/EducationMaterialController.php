<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Handles admin upload and delete of education materials.
 * All metadata is stored directly in public/data/drill-dashboard.json
 * under education.videos. No secondary JSON file is used.
 * Physical files are stored via the 'public' storage disk.
 */
class EducationMaterialController extends Controller
{
    private const MAX_FILE_SIZE_MB = 500;
    private const DASH_JSON = 'data/drill-dashboard.json';

    // ── Public API ────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        if (!$this->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'titles'   => ['required', 'array', 'min:1'],
            'titles.*' => ['required', 'string', 'max:255'],
            'files'    => ['required', 'array', 'min:1'],
            'files.*'  => [
                'file',
                'max:' . (self::MAX_FILE_SIZE_MB * 1024),
                'mimes:mp4,webm,ogv,mov,avi,pdf,ppt,pptx,doc,docx',
            ],
        ]);

        $titles     = $request->input('titles');
        $uploadedBy = session('auth_user.username', 'admin');
        $count      = 0;
        $newVideos  = [];

        foreach ($request->file('files') as $uploadedFile) {
            $originalName = $uploadedFile->getClientOriginalName();
            $mimeType     = $uploadedFile->getMimeType();
            $extension    = $uploadedFile->getClientOriginalExtension();

            $safeName    = Str::slug(pathinfo($originalName, PATHINFO_FILENAME))
                         . '_' . time() . '_' . $count
                         . '.' . $extension;

            $storagePath = $uploadedFile->storeAs('education', $safeName, 'public');

            $newVideos[] = [
                'title'             => $titles[$count] ?? $originalName,
                'embedUrl'          => '/files/' . $storagePath,
                'fileType'          => $mimeType,
                'originalFilename'  => $originalName,
                'uploadedBy'        => $uploadedBy,
                'watched'           => false,
            ];

            $count++;
        }

        $this->appendToDashboard($newVideos);

        $label = $count === 1 ? '"' . ($titles[0] ?? '') . '"' : $count . ' files';

        return redirect('/education')
            ->with('success', 'Material ' . $label . ' uploaded successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        if (!$this->isAdmin()) {
            abort(403);
        }

        $dash   = $this->readDashboard();
        $videos = $dash['education']['videos'] ?? [];

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

        // Delete the physical file if it was an upload (has fileType, not a plain embedUrl)
        if (!empty($target['fileType']) && !empty($target['embedUrl'])) {
            // embedUrl is "/files/education/filename.ext" — strip the leading "/files/"
            $storagePath = preg_replace('#^/files/#', '', $target['embedUrl']);
            Storage::disk('public')->delete($storagePath);
        }

        $dash['education']['videos'] = $remaining;
        $this->writeDashboard($dash);

        return redirect('/education')
            ->with('success', 'Material deleted successfully.');
    }

    // ── Helpers ───────────────────────────────────────────────────

    private function appendToDashboard(array $newVideos): void
    {
        $dash   = $this->readDashboard();
        $nextId = (int) ($dash['education']['next_id'] ?? 1);

        foreach ($newVideos as $v) {
            $v['id']                      = $nextId;
            $dash['education']['videos'][] = $v;
            $nextId++;
        }

        $dash['education']['next_id'] = $nextId;
        $this->writeDashboard($dash);
    }

    private function readDashboard(): array
    {
        $path = public_path(self::DASH_JSON);

        if (!file_exists($path)) {
            return [];
        }

        $decoded = json_decode(file_get_contents($path), true);
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
