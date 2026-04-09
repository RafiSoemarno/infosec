<?php

namespace App\Http\Controllers;

use App\Services\DrillDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DrillController extends Controller
{
    private DrillDataService $drillData;

    private string $drillVideoPath = 'C:\\test drill\\Information Security Video.mp4';

    public function __construct(DrillDataService $drillData)
    {
        $this->drillData = $drillData;
    }

    public function index()
    {
        if (!session()->has('auth_user')) {
            return redirect('/')
                ->withErrors([
                    'auth' => 'Please sign in first.',
                ]);
        }

        $payload = $this->drillData->getDrillPayload((array) session('auth_user'));

        return view('drill', $payload);
    }

    public function complete(Request $request)
    {
        if (!session()->has('auth_user')) {
            return redirect('/');
        }

        $request->validate([
            'drill_id' => ['required', 'integer'],
        ]);

        $this->drillData->completeDrill(
            (array) session('auth_user'),
            (int) $request->input('drill_id')
        );

        return redirect('/drill/video');
    }

    public function videoPlayer()
    {
        if (!session()->has('auth_user')) {
            return redirect('/');
        }

        return view('drill-video');
    }

    public function video()
    {
        if (!session()->has('auth_user')) {
            return redirect('/');
        }

        $path = $this->drillVideoPath;

        if (!file_exists($path)) {
            abort(404, 'Drill video not found.');
        }

        $size = filesize($path);
        $mime = 'video/mp4';

        $start = 0;
        $end = $size - 1;
        $statusCode = 200;
        $headers = [
            'Content-Type'              => $mime,
            'Accept-Ranges'             => 'bytes',
            'Content-Length'            => $size,
            'Content-Disposition'       => 'inline; filename="Information Security Video.mp4"',
            'Cache-Control'             => 'no-cache, no-store',
        ];

        if (request()->hasHeader('Range')) {
            [$start, $end] = $this->parseRange(request()->header('Range'), $size);
            $statusCode = 206;
            $headers['Content-Range']  = "bytes {$start}-{$end}/{$size}";
            $headers['Content-Length'] = $end - $start + 1;
        }

        $stream = fopen($path, 'rb');
        fseek($stream, $start);

        $chunkSize = 1024 * 64;
        $remaining = $end - $start + 1;

        return response()->stream(function () use ($stream, $chunkSize, $remaining) {
            $left = $remaining;
            while (!feof($stream) && $left > 0) {
                $read = min($chunkSize, $left);
                echo fread($stream, $read);
                $left -= $read;
                flush();
            }
            fclose($stream);
        }, $statusCode, $headers);
    }

    private function parseRange(string $range, int $size): array
    {
        preg_match('/bytes=(\d*)-(\d*)/', $range, $matches);
        $start = $matches[1] !== '' ? (int) $matches[1] : 0;
        $end   = $matches[2] !== '' ? (int) $matches[2] : $size - 1;
        $end   = min($end, $size - 1);
        return [$start, $end];
    }
}
