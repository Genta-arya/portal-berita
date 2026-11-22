@php
$paths = [
'close'=>'M6 6l12 12M18 6 6 18',
'check'=>'m5 12 4 4L19 6',
'edit'=>'m16 3 5 5-12 12H4v-5ZM14 5l5 5',
'trash'=>'M3 6h18M9 6V3h6v3M6 6l1 15h10l1-15M10 10v7M14 10v7',

'grid'=>'M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z',
'article'=>'M5 3h14v18H5zM9 7h6M9 11h6M9 15h4',
'image'=>'M3 4h18v16H3zM3 16l6-6 5 5 3-3 4 4M16 8h.01',
'star'=>'m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9Z',
'bolt'=>'m13 2-9 12h7l-1 8 10-13h-8Z',
'users'=>'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M16 4a4 4 0 0 1 0 8M22 21v-2a4 4 0 0 0-3-3.87M13 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0',
'categories'=>'M3 5h7l2 2h9v13H3z',
'tags'=>'M3 3h8l10 10-8 8L3 11zM7 7h.01',
'banners'=>'m3 10 18-6v16L3 14zM7 15l2 6h4l-2-5',
'pages'=>'M5 3h10l4 4v14H5zM14 3v5h5M9 12h6M9 16h6',
'settings'=>'M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8M12 2v3M12 19v3M2 12h3M19 12h3M5 5l2 2M17 17l2 2M5 19l2-2M17 7l2-2',
'audit'=>'M4 4h16v17H4zM8 2v4M16 2v4M8 11h8M8 15h5',
'logout'=>'M9 4H4v16h5M13 8l4 4-4 4M8 12h13',
'menu'=>'M4 6h16M4 12h16M4 18h16',
'external'=>'M14 3h7v7M21 3 10 14M10 3H3v18h18v-7',
'arrow'=>'M5 12h14M13 6l6 6-6 6',
'eye'=>'M2 12s4-7 10-7 10 7 10 7-4 7-10 7-10-7-10-7M12 9a3 3 0 1 0 0 6 3 3 0 0 0 0-6',
'plus'=>'M12 5v14M5 12h14',
];
@endphp
<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $paths[$name] ?? $paths['article'] }}"/></svg>
