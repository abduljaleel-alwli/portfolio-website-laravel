<?php

namespace App\Services\Dashboard;

use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DashboardMetrics
{
    /* =========================
       BASIC COUNTS
    ========================= */

    public function totalUsers(): int
    {
        return User::count();
    }

    public function totalProducts(): int
    {
        return Product::count();
    }

    public function totalVisits(): int
    {
        return DB::table('analytics_events')
            ->where('event', 'page_view')
            ->count();
    }

    public function totalContactMessages(): int
    {
        return DB::table('analytics_events')
            ->where('event', 'contact_submitted')
            ->count();
    }

    /* =========================
       CLICK EVENTS
    ========================= */

    public function whatsappClicks(): int
    {
        return DB::table('analytics_events')
            ->where('event', 'whatsapp_click')
            ->count();
    }

    public function socialClicks(): int
    {
        return DB::table('analytics_events')
            ->where('event', 'social_click')
            ->count();
    }

    /* =========================
       TOP PAGES
    ========================= */

    public function topPages(int $limit = 5)
    {
        return DB::table('analytics_events')
            ->select('page', DB::raw('COUNT(*) as visits'))
            ->where('event', 'page_view')
            ->groupBy('page')
            ->orderByDesc('visits')
            ->limit($limit)
            ->get();
    }

    /* =========================
       CONVERSION RATE
       Visitors → Contact
    ========================= */

    public function conversionRate(): float
    {
        $visits = $this->totalVisits();
        $contacts = $this->totalContactMessages();

        if ($visits === 0) {
            return 0;
        }

        return round(($contacts / $visits) * 100, 2);
    }

    /* =========================
       DAILY CHART
    ========================= */

    public function dailyVisits(int $days = 7)
    {
        return DB::table('analytics_events')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('event', 'page_view')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /* =========================
       MONTHLY CHART
    ========================= */

    public function monthlyVisits(int $months = 6)
    {
        return DB::table('analytics_events')
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as total')
            ->where('event', 'page_view')
            ->where('created_at', '>=', now()->subMonths($months))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    /* =========================
       ACTIVITY LOGS
    ========================= */

    public function latestActivities(int $limit = 10)
    {
        return DB::table('analytics_events')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /* =========================
       ALL METRICS (ONE CALL)
    ========================= */

    public function all(): array
    {
        return [
            'users'            => $this->totalUsers(),
            'products'         => $this->totalProducts(),
            'visits'           => $this->totalVisits(),
            'contacts'         => $this->totalContactMessages(),
            'whatsapp_clicks'  => $this->whatsappClicks(),
            'social_clicks'    => $this->socialClicks(),
            'conversion_rate'  => $this->conversionRate(),
            'top_pages'        => $this->topPages(),
            'daily_visits'     => $this->dailyVisits(),
            'monthly_visits'   => $this->monthlyVisits(),
            'activities'       => $this->latestActivities(),
        ];
    }
}