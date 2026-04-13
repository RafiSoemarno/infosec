<?php

namespace App\Http\Controllers;

use App\Services\EducationJsonStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Handles admin upload and delete of education materials.
 * All metadata is stored in storage/app/education-materials.json.
 * Physical files are stored in storage/app/public/education/ and
 * served publicly via the /storage/education/ URL path.
 * No database is used anywhere in this controller.
 */
class EducationMaterialController extends Controller
{
    private const MAX_FILE_SIZE_MB = 500;

    private EducationJsonStore $store;

    public function __construct(EducationJsonStore $store)
    {
        $this->store = $store;
    }

    /**
     * Handle the upload form submitted by the admin.
     *
     * 1. Validate title + file
     * 2. Save the physical file → storage/app/public/education/<safe-name>
     * 3. Write a new record into education-materials.json (auto-incremented id)
     * 4. Redirect back to /education with a success message
     */
    public function store(Request $request): RedirectResponse
    {
        if (!$this->isAdmin()) {
            abort(403);
        }

        // --- Validate input ------------------------------------------------
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'file'  => [
                'required',
                'file',
                'max:' . (self::MAX_FILE_SIZE_MB * 1024),  // Laravel max is in KB
                'mimes:mp4,webm,ogv,mov,avi,pdf,ppt,pptx,doc,docx',
            ],
        ]);

        // --- Save the physical file ----------------------------------------
        $uploadedFile    = $request->file('file');
        $originalName    = $uploadedFile->getClientOriginalName();
        $mimeType        = $uploadedFile->getMimeType();
        $extension       = $uploadedFile->getClientOriginalExtension();

        // Build a safe filename: "my video_1713000000.mp4"
        $safeName    = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '_' . time() . '.' . $extension;

        // storeAs('education', $safeName, 'public')
        //   → writes to:  storage/app/public/education/<safeName>
        //   → served at:  /storage/education/<safeName>  (via the storage symlink)
        $storagePath = $uploadedFile->storeAs('education', $safeName, 'public');

        // --- Write metadata to the JSON file --------------------------------
        // $storagePath is relative: "education/<safeName>"
        // The public URL is:  asset('storage/' . $storagePath)
        $this->store->create([
            'title'             => $request->input('title'),
            'file_path'         => $storagePath,   // e.g. "education/my-video_1713000000.mp4"
            'file_type'         => $mimeType,       // e.g. "video/mp4"
            'original_filename' => $originalName,   // e.g. "my video.mp4"
            'uploaded_by'       => session('auth_user.username', 'admin'),
        ]);

        return redirect('/education')
            ->with('success', 'Material "' . $request->input('title') . '" uploaded successfully.');
    }

    /**
     * Delete a material by id.
     *
     * 1. Look up the record in the JSON file
     * 2. Delete the physical file from storage/app/public/education/
     * 3. Remove the record from the JSON file
     * 4. Redirect back to /education with a success message
     */
    public function destroy(int $id): RedirectResponse
    {
        if (!$this->isAdmin()) {
            abort(403);
        }

        // Find the record in the JSON store
        $material = $this->store->find($id);

        if (!$material) {
            return redirect('/education')->with('success', 'Material already removed.');
        }

        // Delete the physical file from  storage/app/public/education/
        if (!empty($material['file_path'])) {
            Storage::disk('public')->delete($material['file_path']);
        }

        // Remove the record from the JSON file
        $this->store->delete($id);

        return redirect('/education')
            ->with('success', 'Material deleted successfully.');
    }

    // ── Helper ────────────────────────────────────────────────────

    private function isAdmin(): bool
    {
        $user = session('auth_user');
        return !empty($user) && ($user['role'] ?? '') === 'admin';
    }
}
