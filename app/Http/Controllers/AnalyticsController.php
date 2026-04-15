<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    /**
     * Show the analytics dashboard with daily click trends for the last 30 days.
     */
    public function index(): View
    {
        $clickData = DB::select(
            "SELECT DATE(clicked_at) as date, COUNT(*) as count
             FROM clicks
             WHERE clicked_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
             GROUP BY DATE(clicked_at)
             ORDER BY date ASC"
        );

        // Map to a plain array of {date, count} objects for the view/chart
        $chartData = array_map(fn($row) => [
            'date'  => $row->date,
            'count' => (int) $row->count,
        ], $clickData);

        return view('analytics', ['chartData' => $chartData]);
    }
}
