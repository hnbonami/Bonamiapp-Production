<a href="{{ url('/sjabloon-manager') }}" class="relative flex items-center gap-3 pl-24 pr-3 py-2 transition-colors {{ request()->is('sjabloon-manager*') || request()->is('templates*') ? 'text-gray-900' : 'text-gray-700 hover:text-gray-900' }}" style="padding-left:48px;{{ request()->is('sjabloon-manager*') || request()->is('templates*') ? 'background:#f6fbfe' : '' }}">
    @if(request()->is('sjabloon-manager*') || request()->is('templates*'))
        <span style="position:absolute;left:0;top:0;bottom:0;width:5px;background:#c1dfeb;"></span>
    @endif
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#9bb3bd" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
    </svg>
    <span class="font-medium text-[17px]">Sjablonen</span>
</a>