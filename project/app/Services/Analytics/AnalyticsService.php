<?php

namespace App\Services\Analytics;

use App\Models\AnalyticsEvent;

class AnalyticsService
{
    public function track(string $event, array $data = []): void
    {
        AnalyticsEvent::create([
            'event'       => $event,
            'entity_type' => $data['entity_type'] ?? null,
            'entity_id'   => $data['entity_id'] ?? null,
            'page'        => request()->path(),
            'source'      => $data['source'] ?? null,
            'ip'          => request()->ip(),
            'user_agent'  => request()->userAgent(),
            'user_id'     => auth()->id(),
        ]);
    }
}
