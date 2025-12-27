<?php

use Livewire\Volt\Component;
use App\Models\AuditLog;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\WithPagination;

new class extends Component {
    use AuthorizesRequests;
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public function mount(): void
    {
        $this->authorize('viewAny', \App\Models\User::class);
    }

    // ✅ pagination method (لا تُخزَّن)
    public function logs()
    {
        return AuditLog::with('actor')
            ->latest()
            ->paginate(10);
    }
};
?>


<div class="space-y-6">

    <h1 class="text-xl font-bold">Audit Logs</h1>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3 text-left">Actor</th>
                    <th class="p-3 text-left">Action</th>
                    <th class="p-3 text-left">Target</th>
                    <th class="p-3 text-left">Details</th>
                    <th class="p-3 text-left">Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->logs() as $log)
                    <tr class="border-t">
                        <td class="p-3">
                            {{ $log->actor->name ?? 'System' }}
                        </td>

                        <td class="p-3 font-mono text-xs">
                            {{ $log->action }}
                        </td>

                        <td class="p-3">
                            {{ class_basename($log->target_type) }}
                            #{{ $log->target_id }}
                        </td>

                        <td class="p-3 text-xs text-gray-600">
                            @if (!empty($log->metadata))
                                <pre>{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            @else
                                —
                            @endif
                        </td>

                        <td class="p-3">
                            {{ $log->created_at->format('Y-m-d H:i') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-6 text-center text-gray-500">
                            No audit logs found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $this->logs()->links() }}
    </div>

</div>