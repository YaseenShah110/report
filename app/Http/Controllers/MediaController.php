<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * MediaController
 * ───────────────────────────────────────────────────────────────────
 * Two responsibilities:
 *
 * 1. upload()  — stores a user-uploaded image in
 *               storage/app/public/report-media/{report_id}/
 *               and returns the public URL.
 *
 * 2. search()  — proxies keyword searches to a free image API so
 *               the API key never leaves the server.
 *               Priority:  Pixabay → Unsplash → Picsum (no-key fallback)
 */
class MediaController extends Controller
{
    // Maximum file size for uploads (bytes)
    private const MAX_UPLOAD_BYTES = 10 * 1024 * 1024; // 10 MB

    // ── Upload ────────────────────────────────────────────────────────
    public function upload(Request $request)
    {
        $request->validate([
            'image'     => 'required|image|max:10240',   // max 10 MB
            'report_id' => 'nullable|integer',
        ]);

        $file   = $request->file('image');
        $dir    = 'report-media/' . ($request->input('report_id', 'general'));
        $name   = Str::uuid() . '.' . $file->extension();
        $path   = $file->storeAs($dir, $name, 'public');

        return response()->json([
            'url'  => Storage::url($path),
            'path' => $path,
            'name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime' => $file->getMimeType(),
        ]);
    }

    // ── Search ────────────────────────────────────────────────────────
    public function search(Request $request)
    {
        $request->validate([
            'q'        => 'required|string|max:120',
            'per_page' => 'integer|min:1|max:40',
            'page'     => 'integer|min:1',
        ]);

        $q       = trim($request->input('q'));
        $perPage = (int) $request->input('per_page', 20);
        $page    = (int) $request->input('page', 1);

        // Try providers in order
        if ($images = $this->pixabay($q, $perPage, $page)) {
            return response()->json(['images' => $images, 'source' => 'pixabay']);
        }

        if ($images = $this->unsplash($q, $perPage, $page)) {
            return response()->json(['images' => $images, 'source' => 'unsplash']);
        }

        // No-key fallback — deterministic Picsum images based on keyword hash
        return response()->json(['images' => $this->picsum($q, $perPage, $page), 'source' => 'picsum']);
    }

    // ── Pixabay ───────────────────────────────────────────────────────
    private function pixabay(string $q, int $perPage, int $page): ?array
    {
        $key = config('services.pixabay.key');
        if (empty($key)) return null;

        try {
            $res = Http::timeout(8)->get('https://pixabay.com/api/', [
                'key'        => $key,
                'q'          => $q,
                'image_type' => 'photo',
                'per_page'   => $perPage,
                'page'       => $page,
                'safesearch' => 'true',
            ]);

            if (! $res->successful()) return null;

            return collect($res->json('hits', []))->map(fn ($h) => [
                'id'     => $h['id'],
                'url'    => $h['largeImageURL'] ?? $h['webformatURL'],
                'thumb'  => $h['previewURL'],
                'alt'    => $h['tags'] ?? $q,
                'author' => $h['user'] ?? 'Pixabay',
                'source' => 'pixabay',
            ])->values()->all();
        } catch (\Throwable) {
            return null;
        }
    }

    // ── Unsplash ──────────────────────────────────────────────────────
    private function unsplash(string $q, int $perPage, int $page): ?array
    {
        $key = config('services.unsplash.access_key');
        if (empty($key)) return null;

        try {
            $res = Http::timeout(8)
                ->withHeaders(['Authorization' => "Client-ID {$key}"])
                ->get('https://api.unsplash.com/search/photos', [
                    'query'    => $q,
                    'per_page' => $perPage,
                    'page'     => $page,
                ]);

            if (! $res->successful()) return null;

            return collect($res->json('results', []))->map(fn ($p) => [
                'id'     => $p['id'],
                'url'    => $p['urls']['regular'],
                'thumb'  => $p['urls']['thumb'],
                'alt'    => $p['alt_description'] ?? $q,
                'author' => $p['user']['name'] ?? 'Unsplash',
                'source' => 'unsplash',
            ])->values()->all();
        } catch (\Throwable) {
            return null;
        }
    }

    // ── Picsum (no-key fallback) ──────────────────────────────────────
    private function picsum(string $q, int $perPage, int $page): array
    {
        $seed   = abs(crc32($q));
        $images = [];
        for ($i = 0; $i < $perPage; $i++) {
            $id      = (($seed + $page * $perPage + $i) % 1000) + 1;
            $images[]= [
                'id'     => $id,
                'url'    => "https://picsum.photos/id/{$id}/800/600",
                'thumb'  => "https://picsum.photos/id/{$id}/200/150",
                'alt'    => $q,
                'author' => 'Lorem Picsum',
                'source' => 'picsum',
            ];
        }
        return $images;
    }
}