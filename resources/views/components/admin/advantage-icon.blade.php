@props(['name' => 'default'])

{{-- SVG di bawah harus tetap identik dengan partials/keunggulan.blade.php,
     supaya pratinjau di admin benar-benar sama dengan yang tampil di homepage. --}}
@switch($name)
    @case('clipboard')
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-5.5 9l1.8 1.8L15 11"/></svg>
        @break
    @case('chat')
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10.5h8M8 14h5M21 11.5a8 8 0 01-8 8H7.5L3 22.5V11.5a8 8 0 1118 0z"/></svg>
        @break
    @case('users')
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="8" r="3"/><path stroke-linecap="round" stroke-linejoin="round" d="M3.5 20a5.5 5.5 0 0111 0M16 6.2a3 3 0 010 5.6M17 20a5.5 5.5 0 00-2.7-4.2"/></svg>
        @break
    @case('shield')
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v5c0 4.2-2.8 7.9-7 9-4.2-1.1-7-4.8-7-9V6l7-3z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4.5"/></svg>
        @break
    @case('star')
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3.2l2.6 5.3 5.8.8-4.2 4.1 1 5.8-5.2-2.7-5.2 2.7 1-5.8L3.4 9.3l5.8-.8L12 3.2z"/></svg>
        @break
    @default
        {{-- 'pin' dan nilai kosong sama-sama memakai ikon lokasi (default homepage). --}}
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s7-6.3 7-11a7 7 0 10-14 0c0 4.7 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
@endswitch
