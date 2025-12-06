@props([
  'name' => null,
  'size' => 18,
  'stroke' => 1.75,
  'class' => '',
])

@php
    $sz = is_numeric($size) ? $size : 18;
    $sw = is_numeric($stroke) ? $stroke : 1.75;
@endphp

<svg xmlns="http://www.w3.org/2000/svg" width="{{ $sz }}" height="{{ $sz }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="{{ $sw }}" stroke-linecap="round" stroke-linejoin="round" class="{{ $class }}">
    @switch($name)
        @case('house')
            <path d="M3 11l9-7 9 7" />
            <path d="M5 10v10h14V10" />
            <rect x="10" y="14" width="4" height="5" rx="1" />
            @break
        @case('calendar-range')
            <rect x="3" y="4" width="18" height="18" rx="3" />
            <line x1="8" y1="2.5" x2="8" y2="6" />
            <line x1="16" y1="2.5" x2="16" y2="6" />
            <line x1="3" y1="10" x2="21" y2="10" />
            <path d="M6.5 14.5h4M13 17.5h5" />
            @break
        @case('person')
            <circle cx="12" cy="8" r="3.5" />
            <path d="M4 20c0-3.8 3.3-5.5 8-5.5s8 1.7 8 5.5" />
            @break
        @case('people')
            <path d="M7 20c0-3 2-5 5-5s5 2 5 5" />
            <circle cx="9" cy="9" r="3" />
            <circle cx="15" cy="9" r="3" />
            @break
        @case('building')
            <rect x="5" y="3" width="14" height="18" rx="2" />
            <path d="M9 7h2M13 7h2M9 11h2M13 11h2M9 15h2M13 15h2" />
            @break
        @case('briefcase')
            <rect x="3" y="7" width="18" height="13" rx="2.5" />
            <path d="M9 7V5a3 3 0 0 1 6 0v2" />
            <path d="M3 12.5h18" />
            @break
        @case('cash-coin')
            <circle cx="9" cy="12" r="5" />
            <circle cx="16.5" cy="8.5" r="3.5" />
            <path d="M7.5 12h3M9 10.5v3" />
            @break
        @case('basket')
            <path d="M4 10h16" />
            <path d="M6 10l3-6h6l3 6" />
            <rect x="4" y="10" width="16" height="9" rx="2.5" />
            <path d="M10 13v3M14 13v3" />
            @break
        @case('cart3')
            <path d="M6 6h15l-2 8H8L6 6Z" />
            <circle cx="9" cy="19" r="1.8" />
            <circle cx="17" cy="19" r="1.8" />
            <path d="M6 6L5 3H3" />
            @break
        @case('headset')
            <path d="M4 12a8 8 0 1 1 16 0" />
            <rect x="3" y="12" width="4" height="6" rx="2" />
            <rect x="17" y="12" width="4" height="6" rx="2" />
            <path d="M12 18v2a3 3 0 0 0 3 3h2" />
            @break
        @case('calendar-event')
            <rect x="3" y="4" width="18" height="18" rx="2" />
            <line x1="8" y1="2" x2="8" y2="6" />
            <line x1="16" y1="2" x2="16" y2="6" />
            <line x1="3" y1="10" x2="21" y2="10" />
            <circle cx="12" cy="15" r="3" />
            @break
        @case('lock')
            <rect x="6" y="11" width="12" height="10" rx="2" />
            <path d="M8 11V8a4 4 0 0 1 8 0v3" />
            <circle cx="12" cy="16" r="1" />
            @break
        @case('clipboard')
            <rect x="6" y="3" width="12" height="18" rx="2" />
            <rect x="9" y="1" width="6" height="4" rx="1" />
            <path d="M9 9h6M9 13h6M9 17h6" />
            @break
        @case('note')
            <path d="M4 3h10l6 6v12H4z" />
            <path d="M14 3v6h6" />
            <path d="M8 14h8M8 18h8" />
            @break
        @case('journal-text')
            <rect x="4" y="3" width="15" height="18" rx="2" />
            <path d="M4 7h15M8 11h9M8 15h9" />
            <path d="M4 3v18" />
            @break
        @case('graph-up')
            <path d="M3 20h18" />
            <path d="M7 16l5-5 4 3 4-6" />
            @break
        @case('gear')
            <circle cx="12" cy="12" r="2.8" />
            <path d="M20 14.5a7.5 7.5 0 0 0 0-5l-2.1.5-1-1.7 1.4-1.4a7.5 7.5 0 0 0-5-2.1l-.5 2.1h-2.6l-.5-2.1a7.5 7.5 0 0 0-5 2.1L6.1 7.8l-1 1.7-2.1-.5a7.5 7.5 0 0 0 0 5l2.1-.5 1 1.7-1.4 1.4a7.5 7.5 0 0 0 5 2.1l.5-2.1h2.6l.5 2.1a7.5 7.5 0 0 0 5-2.1l-1.4-1.4 1-1.7Z" />
            @break
        @case('question')
            <circle cx="12" cy="12" r="10" />
            <path d="M9.5 9a3 3 0 1 1 4.5 2.6c-.8.6-1.5 1.2-1.5 2.4" />
            <circle cx="12" cy="18" r="1" />
            @break
        @case('box2')
            <path d="M3 8l9-5 9 5v8l-9 5-9-5V8Z" />
            <path d="M3 8l9 5 9-5" />
            <path d="M12 13v8" />
            @break
        @case('receipt')
            <path d="M6 2l1 2 2-2 2 2 2-2 2 2 1-2v20l-1-2-2 2-2-2-2 2-2-2-1 2V2Z" />
            <path d="M8 8h8M8 12h8M8 16h8" />
            @break
        @case('files')
            <rect x="7" y="2" width="11" height="16" rx="2" />
            <rect x="6" y="6" width="11" height="16" rx="2" />
            @break
        @case('incognito')
            <path d="M4 10l2-4h12l2 4" />
            <path d="M8 14h8" />
            <circle cx="9" cy="14" r="2" />
            <circle cx="15" cy="14" r="2" />
            @break
        @case('ticket-detailed')
            <rect x="3" y="5" width="18" height="14" rx="2" />
            <circle cx="7" cy="12" r="1.5" />
            <circle cx="17" cy="12" r="1.5" />
            <path d="M12 8v8" />
            @break
        @case('front')
            <rect x="3" y="4" width="18" height="16" rx="2" />
            <path d="M3 10h18" />
            <path d="M8 20v-6M16 20v-6" />
            @break
        @case('robot')
            <circle cx="12" cy="12" r="8" />
            <circle cx="12" cy="12" r="4" fill="currentColor" />
            @break
        @case('server')
            <rect x="4" y="3" width="16" height="6" rx="2" />
            <rect x="4" y="11" width="16" height="6" rx="2" />
            <rect x="4" y="19" width="16" height="2" rx="1" />
            <path d="M6 6h2M6 14h2" />
            @break
        @case('camera-video')
            <rect x="3" y="7" width="12" height="10" rx="2" />
            <path d="M15 9l6-3v12l-6-3v-6Z" />
            @break
        @case('wallet')
            <rect x="3" y="6" width="18" height="12" rx="3" />
            <path d="M15 10h4v4h-4" />
            <circle cx="17" cy="12" r="1" />
            @break
        @case('display')
            <rect x="3" y="4" width="18" height="12" rx="2" />
            <path d="M8 20h8M12 16v4" />
            @break
        @case('qrcode')
            <rect x="3" y="3" width="6" height="6" />
            <rect x="15" y="3" width="6" height="6" />
            <rect x="3" y="15" width="6" height="6" />
            <path d="M15 15h2v2h2v4M19 15v2h-2v-2M17 19h-2v2" />
            @break
        @case('users')
            <path d="M7 20c0-3 2-5 5-5s5 2 5 5" />
            <circle cx="9" cy="9" r="3" />
            <circle cx="15" cy="9" r="3" />
            @break
        @case('fingerprint')
            <path d="M12 2a9 9 0 0 0-9 9c0 2.5 1 4.5 2 6" />
            <path d="M12 2a9 9 0 0 1 9 9c0 2.5-1 4.5-2 6" />
            <path d="M12 6a5 5 0 0 0-5 5c0 1.5.6 2.7 1.2 3.6" />
            <path d="M12 6a5 5 0 0 1 5 5c0 1.5-.6 2.7-1.2 3.6" />
            <path d="M12 10a1 1 0 0 0-1 1c0 .6.3 1.2.7 1.7" />
            @break
        @case('file')
            <path d="M6 2h8l4 4v16H6z" />
            <path d="M14 2v4h4" />
            @break
        @default
            <circle cx="12" cy="12" r="10" />
    @endswitch
</svg>