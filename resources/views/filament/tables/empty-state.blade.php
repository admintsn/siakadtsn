<x-filament-tables::empty-state.heading>
   
</x-filament-tables::empty-state.heading>

@if ($description)
<x-filament-tables::empty-state.description class="mt-1">
  
</x-filament-tables::empty-state.description>
@endif

@if ($actions)
<x-filament-tables::actions :actions="$actions" :alignment="Alignment::Center" wrap class="mt-6" />
@endif