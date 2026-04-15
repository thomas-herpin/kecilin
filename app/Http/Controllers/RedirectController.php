<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Services\ClickTrackerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class RedirectController extends Controller
{
    public function __construct(
        private ClickTrackerService $clickTrackerService,
    ) {}

    /**
     * Redirect a slug to its original URL, recording the click.
     */
    public function redirect(string $slug, Request $request): RedirectResponse|Response
    {
        $link = Link::where('slug', $slug)->first();

        if ($link === null) {
            return response()->view('errors.404', [], 404);
        }

        $this->clickTrackerService->record($link, $request);

        return redirect($link->original_url, 301);
    }
}
