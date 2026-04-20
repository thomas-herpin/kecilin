<?php

namespace App\Http\Controllers;

use App\Models\Link;
use Illuminate\View\View;

class HistoryController extends Controller
{
    // Tunjukkan semua link diurutkan dari yang terbaru
    public function index(): View
{
    return view('history', [
        'links' => Link::latest()->get()
    ]);
}
}
