<?php

namespace App\Http\Controllers;

use App\Models\SystemMetric;

class ServerHealthController extends Controller
{
    /**
     * Display server health metrics.
     */
    public function index()
    {
        $chartHours = config('monitoring.chart_hours', 6);
        $systemMetrics = SystemMetric::getRecentMetrics($chartHours);
        $latestMetric = SystemMetric::getLatest();

        return view('server-health', compact(
            'systemMetrics',
            'latestMetric'
        ));
    }
}
