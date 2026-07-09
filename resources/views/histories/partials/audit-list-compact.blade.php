@php
    use App\Models\History;

    $histories = $histories ?? collect();
@endphp

@if ($histories->isNotEmpty())
    <div class="form-page-history-compact">
        <span class="form-page-history-compact-title">Histórico da anotação</span>
        <ul class="form-page-history-compact-list">
            @foreach ($histories as $history)
                <li>
                    {{ History::actionLabel($history->action) }}
                    por {{ $history->user?->name ?? '—' }}
                    em {{ $history->created_at?->format('d/m/Y H:i') ?? '—' }}
                </li>
            @endforeach
        </ul>
    </div>
@endif
