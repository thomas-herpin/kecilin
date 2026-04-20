<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Services\UrlShortenerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class LinkController extends Controller
{
    public function __construct(
        private UrlShortenerService $urlShortenerService,
    ) {}

    // Tampilkan halaman home
    public function index(): View
    {
        return view('home');
    }

    // Perpendek URL dan kirim hasil
    public function store(Request $request): View|RedirectResponse
    {
        $request->validate([
            'url'   => ['required', 'string'],
            'alias' => ['nullable', 'string'],
        ]);

        try {
            $link = $this->urlShortenerService->shorten(
                $request->input('url'), 
                $request->input('alias')
            );

            // Langsung kirim ke view dengan variabel yang sudah siap pakai
            return view('home', [
                'link'     => $link,
                'shortUrl' => config('app.url') . '/' . $link->slug,
                'qrCode'   => $link->qr_code_svg,
            ]);
            
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['url' => $e->getMessage()])->withInput();
        }
    }

    // Hapus link dan balik ke history
    public function destroy(Link $link): RedirectResponse
    {
        $this->urlShortenerService->delete($link);

        return redirect('/history');
    }

    // Update tujuan URL dan balik ke history
    public function update(Request $request, Link $link): RedirectResponse
    {
        $request->validate([
            'url' => ['required', 'string'],
        ]);

        try {
            $this->urlShortenerService->update($link, $request->input('url'));
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['url' => $e->getMessage()])->withInput();
        }

        return redirect('/history');
    }
}
