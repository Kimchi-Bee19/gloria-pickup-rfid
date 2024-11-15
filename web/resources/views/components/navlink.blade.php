<a {{ $attributes->merge(['class' => 'h-[30px] cursor-pointer mb-1 rounded-full flex items-center transition-all ease-in-out duration-[320]']) }}
    :class="{ 'px-2': !isOpen, 'px-4': isOpen, 'bg-slate-100 text-dark-blue': {{ $active ? 'true' : 'false' }}, 'text-slate-100 hover:text-dark-blue hover:bg-slate-100': !{{ $active ? 'true' : 'false' }} }">
    {{-- Icon --}}
    @if(isset($icon))
    {!!$icon !!}
    @endif
    {{-- Title --}}
    @if(isset($title))
    <div class = "text-xs leading-none" :class="{ 'hidden': !isOpen, '': isOpen }" >
        {{ $title }}
    </div>
    @endif
</a>
