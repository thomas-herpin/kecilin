<?php

namespace App\Http\Controllers;

use App\Models\Link;
use Illuminate\View\View;

class HistoryController extends Controller
{
    /**
     * Show all links ordered by newest first.
     */
    public function index(): View
    {
        $links = Link::latest()->get();

        return view('history', ['links' => $links]);
    }
}
