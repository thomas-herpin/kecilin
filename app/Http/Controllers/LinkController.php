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

    /**
     * Show the home page.
     */
    public function index(): View
    {
        return view('home');
    }

    /**
     * Shorten a URL and return the result.
     */
    public function store(Request $request): View|RedirectResponse
    {
        $request->validate([
            'url'   => ['required', 'string'],
            'alias' => ['nullable', 'string'],
        ]);

        $url   = $request->input('url');
        $alias = $request->input('alias') ?: null;

        try {
            $link = $this->urlShortenerService->shorten($url, $alias);

            $shortUrl = config('app.url') . '/' . $link->slug;

            return view('home', [
                'link'     => $link,
                'shortUrl' => $shortUrl,
                'qrCode'   => $link->qr_code_svg,
            ]);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['url' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Delete a link and redirect to history.
     */
    public function destroy(Link $link): RedirectResponse
    {
        $this->urlShortenerService->delete($link);

        return redirect('/history');
    }

    /**
     * Update a link's destination URL and redirect to history.
     */
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
