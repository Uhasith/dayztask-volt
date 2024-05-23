<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <x-mary-stat title="Messages" value="44" icon="o-envelope" tooltip="Hello" />
 
<x-mary-stat
    title="Sales"
    description="This month"
    value="22.124"
    icon="o-arrow-trending-up"
    tooltip-bottom="There" />
 
<x-mary-stat
    title="Lost"
    description="This month"
    value="34"
    icon="o-arrow-trending-down"
    tooltip-left="Ops!" />
 
<x-mary-stat
    title="Sales"
    description="This month"
    value="22.124"
    icon="o-arrow-trending-down"
    class="text-orange-500"
    color="text-pink-500"
    tooltip-right="Gosh!" />
            </div>
        </div>
    </div>
</x-app-layout>
