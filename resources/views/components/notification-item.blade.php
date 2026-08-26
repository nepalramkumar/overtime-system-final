@props(['notif', 'compact' => false])

@php
    $type = class_basename($notif->type ?? '');

    // Notification को प्रकार अनुसार Icon + रङ — छिटो चिन्न मिल्ने बनाउन
    $meta = match ($type) {
        'PetrolBillReminderNotification'     => ['icon' => 'fa-gas-pump',      'color' => 'orange'],
        'RepairExpenseReminderNotification'  => ['icon' => 'fa-wrench',        'color' => 'amber'],
        'EventCreatedNotification'           => ['icon' => 'fa-calendar-plus', 'color' => 'purple'],
        'EventApprovedForEntryNotification'  => ['icon' => 'fa-calendar-check','color' => 'emerald'],
        'EventCreationRejectedNotification'  => ['icon' => 'fa-calendar-xmark','color' => 'rose'],
        'EventSubmittedNotification'         => ['icon' => 'fa-paper-plane',   'color' => 'blue'],
        'EventRecommendedNotification'       => ['icon' => 'fa-thumbs-up',     'color' => 'blue'],
        'EventRejectedNotification'          => ['icon' => 'fa-circle-xmark', 'color' => 'rose'],
        default                              => ['icon' => 'fa-bell',         'color' => 'slate'],
    };

    $colorMap = [
        'orange'  => ['bg' => 'bg-orange-50',  'text' => 'text-orange-600',  'ring' => 'ring-orange-100'],
        'amber'   => ['bg' => 'bg-amber-50',   'text' => 'text-amber-600',   'ring' => 'ring-amber-100'],
        'purple'  => ['bg' => 'bg-purple-50',  'text' => 'text-purple-600',  'ring' => 'ring-purple-100'],
        'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'ring' => 'ring-emerald-100'],
        'rose'    => ['bg' => 'bg-rose-50',    'text' => 'text-rose-600',    'ring' => 'ring-rose-100'],
        'blue'    => ['bg' => 'bg-blue-50',    'text' => 'text-blue-600',    'ring' => 'ring-blue-100'],
        'slate'   => ['bg' => 'bg-slate-100',  'text' => 'text-slate-500',   'ring' => 'ring-slate-100'],
    ];
    $c = $colorMap[$meta['color']];
    $isUnread = is_null($notif->read_at);
@endphp

<form method="POST" action="{{ route('notifications.read', $notif->id) }}" class="block">
    @csrf
    <button type="submit"
            class="w-full text-left flex items-start gap-3 {{ $compact ? 'px-4 py-3' : 'px-5 py-4' }} transition
                   {{ $isUnread ? 'bg-blue-50/40 hover:bg-blue-50' : 'bg-white hover:bg-slate-50' }}">

        {{-- Icon badge --}}
        <span class="mt-0.5 flex-shrink-0 inline-flex items-center justify-center w-9 h-9 rounded-full {{ $c['bg'] }} {{ $c['text'] }} ring-4 {{ $c['ring'] }}">
            <i class="fas {{ $meta['icon'] }} text-xs"></i>
        </span>

        <span class="flex-1 min-w-0">
            <span class="flex items-center gap-2">
                <span class="text-sm truncate {{ $isUnread ? 'font-bold text-slate-800' : 'font-medium text-slate-500' }}">
                    {{ $notif->data['title'] ?? '' }}
                </span>
                @if($isUnread)
                    <span class="flex-shrink-0 w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                @endif
            </span>
            <span class="block text-xs {{ $isUnread ? 'text-slate-600' : 'text-slate-400' }} mt-0.5 leading-relaxed {{ $compact ? 'line-clamp-2' : '' }}">
                {{ $notif->data['message'] ?? '' }}
            </span>
            <span class="block text-[11px] text-slate-400 mt-1.5">
                {{ $notif->created_at->diffForHumans() }}
            </span>
        </span>
    </button>
</form>