@php $isStaff = Auth::user() && in_array(Auth::user()->role, ['admin', 'medewerker']); @endphp
@if($isStaff)
{{-- Notities & Taken menu item uitgeschakeld op verzoek gebruiker --}}
{{-- 
<a href="/staff-notes" class="relative flex items-center gap-3 pl-24 pr-3 py-2 transition-colors {{ request()->is('staff-notes*') ? 'text-gray-900' : 'text-gray-700 hover:text-gray-900' }}" style="padding-left:48px;{{ request()->is('staff-notes*') ? 'background:#f6fbfe' : '' }}">@if(request()->is('staff-notes*'))<span style="position:absolute;left:0;top:0;bottom:0;width:5px;background:#c1dfeb;"></span>@endif<svg width="22" height="22" fill="none" viewBox="0 0 20 20"><rect x="3" y="4" width="14" height="12" rx="2" stroke="#9bb3bd" stroke-width="1.5"/><path d="M7 8h6M7 12h4" stroke="#9bb3bd" stroke-width="1.5"/></svg><span class="font-medium text-[17px]">Notities &amp; Taken</span></a>
--}}
@endif
