<?php

namespace App\Services;

/**
 * EducationJsonStore
 *
 * The ONLY class that reads from and writes to education-materials.json.
 * No database is used anywhere in this class.
 *
 * JSON file location: storage/app/education-materials.json
 *
 * JSON structure:
 * {
 *   "next_id": 4,
 *   "materials": [
 *     {
 *       "id": 1,
 *       "title": "Introduction to Phishing",
 *       "file_path": "education/intro-to-phishing_1713000000.mp4",
 *       "file_type": "video/mp4",
 *       "original_filename": "intro-to-phishing.mp4",
 *       "uploaded_by": "dnia.admin",
 *       "created_at": "2026-04-13 10:00:00"
 *     }
 *   ]
 * }
 */
class EducationJsonStore
{
    // Absolute path to the JSON file inside Laravel's private storage.
    // This folder is NOT publicly accessible — only physical files in
    // storage/app/public/education/ are served to the browser.
    private string $jsonPath;

    public function __construct()
    {
        $this->jsonPath = storage_path('app/education-materials.json');
    }

    // ── Read ─────────────────────────────────────────────────────

    /**
     * Return all materials as a plain PHP array, ordered by id ascending.
     */
    public function all(): array
    {
        $data = $this->read();
        return $data['materials'] ?? [];
    }

    /**
     * Find one material by id. Returns null when not found.
     */
    public function find(int $id): ?array
    {
        foreach ($this->all() as $material) {
            if ((int) $material['id'] === $id) {
                return $material;
            }
        }
        return null;
    }

    // ── Write ─────────────────────────────────────────────────────

    /**
     * Add a new material record to the JSON file.
     * The id is auto-incremented using the "next_id" counter stored in the file.
     *
     * @param array $fields  Keys: title, file_path, file_type, original_filename, uploaded_by
     * @return array         The newly created record (including the auto id and created_at)
     */
    public function create(array $fields): array
    {
        $data = $this->read();

        // Auto-increment: read next_id, then bump it for next time
        $newId = (int) ($data['next_id'] ?? 1);
        $data['next_id'] = $newId + 1;

        $record = [
            'id'                => $newId,
            'title'             => (string) ($fields['title'] ?? ''),
            'file_path'         => (string) ($fields['file_path'] ?? ''),
            'file_type'         => (string) ($fields['file_type'] ?? ''),
            'original_filename' => (string) ($fields['original_filename'] ?? ''),
            'uploaded_by'       => (string) ($fields['uploaded_by'] ?? 'admin'),
            'created_at'        => now()->format('Y-m-d H:i:s'),
        ];

        $data['materials'][] = $record;

        $this->write($data);

        return $record;
    }

    /**
     * Delete a material record by id.
     * Returns the deleted record (so the caller can also delete the physical file),
     * or null if the id was not found.
     */
    public function delete(int $id): ?array
    {
        $data = $this->read();

        $deleted = null;
        $remaining = [];

        foreach ($data['materials'] as $material) {
            if ((int) $material['id'] === $id) {
                $deleted = $material;   // keep a copy to return to the caller
            } else {
                $remaining[] = $material;
            }
        }

        if ($deleted === null) {
            return null;  // not found
        }

        $data['materials'] = $remaining;
        $this->write($data);

        return $deleted;
    }

    // ── Internal helpers ──────────────────────────────────────────

    /**
     * Read the JSON file and return its content as a PHP array.
     * Returns a safe default structure when the file is missing or malformed.
     */
    private function read(): array
    {
        if (!file_exists($this->jsonPath)) {
            return ['next_id' => 1, 'materials' => []];
        }

        $raw = file_get_contents($this->jsonPath);
        $decoded = json_decode($raw, true);

        if (!is_array($decoded)) {
            return ['next_id' => 1, 'materials' => []];
        }

        return $decoded;
    }

    /**
     * Write a PHP array back to the JSON file, formatted for readability.
     */
    private function write(array $data): void
    {
        file_put_contents(
            $this->jsonPath,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }
}
