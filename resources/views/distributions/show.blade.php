<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-stone-900">
                    Distribution Details
                </h2>

                <p class="mt-1 text-sm text-stone-500">
                    View distribution information and distributed items.
                </p>
            </div>

            <a
                href="{{ route('distributions.index') }}"
                class="rounded-lg bg-stone-100 px-4 py-2
                       text-sm font-semibold text-stone-700
                       hover:bg-stone-200"
            >
                Back
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

            {{-- Actions --}}
            <div class="mb-6 flex items-center justify-end gap-2">

                <a
                    href="{{ route('distributions.edit', $distribution) }}"
                    class="rounded-lg bg-amber-700 px-4 py-2
                           text-sm font-semibold text-white
                           hover:bg-amber-800"
                >
                    Edit
                </a>

                <form
                    action="{{ route('distributions.destroy', $distribution) }}"
                    method="POST"
                >
                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        onclick="return confirm('Are you sure you want to delete this distribution?')"
                        class="rounded-lg bg-red-50 px-4 py-2
                               text-sm font-semibold text-red-600
                               hover:bg-red-100"
                    >
                        Delete
                    </button>
                </form>

            </div>


            {{-- ========================================================= --}}
            {{-- Distribution Information --}}
            {{-- ========================================================= --}}

            <x-app-card>

                <div class="mb-5">
                    <h3 class="text-base font-bold text-stone-900">
                        Distribution Information
                    </h3>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">

                    {{-- Employee --}}
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">
                            Employee
                        </p>

                        <p class="mt-1 text-sm font-semibold text-stone-900">
                            {{ $distribution->assignment->employee->user->name }}
                        </p>

                        <p class="mt-1 text-sm text-stone-500">
                            {{ $distribution->assignment->employee->employee_code }}
                        </p>
                    </div>

                    {{-- Date --}}
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">
                            Distribution Date
                        </p>

                        <p class="mt-1 text-sm font-semibold text-stone-900">
                            {{ $distribution->distribution_date->format('d M Y') }}
                        </p>
                    </div>

                </div>

                @php
                    $assignment = $distribution->assignment->employee
                        ->assignments
                        ->where('status', 'active')
                        ->first();
                @endphp

                @if ($assignment)

                    <div class="mt-5 border-t border-stone-200 pt-5">

                        <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">
                            Current Assignment
                        </p>

                        <div class="mt-3 grid gap-4 sm:grid-cols-2">

                            <div>
                                <p class="text-xs text-stone-500">
                                    Cart
                                </p>

                                <p class="mt-1 text-sm font-semibold text-stone-900">
                                    {{ $assignment->cart->name ?? '-' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-stone-500">
                                    Region
                                </p>

                                <p class="mt-1 text-sm font-semibold text-stone-900">
                                    {{ $assignment->region->name ?? '-' }}
                                </p>
                            </div>

                        </div>

                    </div>

                @endif

                @if ($distribution->notes)

                    <div class="mt-5 border-t border-stone-200 pt-5">

                        <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">
                            Notes
                        </p>

                        <p class="mt-2 whitespace-pre-line text-sm text-stone-700">
                            {{ $distribution->notes }}
                        </p>

                    </div>

                @endif

            </x-app-card>


            {{-- ========================================================= --}}
            {{-- Sales Summary --}}
            {{-- ========================================================= --}}

            <x-app-card class="mt-6">

                <div class="mb-5">
                    <h3 class="text-base font-bold text-stone-900">
                        Sales Summary
                    </h3>

                    <p class="mt-1 text-sm text-stone-500">
                        Comparison between distributed and sold base drinks.
                    </p>
                </div>

                @if ($salesSummary->isNotEmpty())

                    <div class="overflow-hidden rounded-lg border border-stone-200">

                        {{-- Table Header --}}
                        <div class="grid grid-cols-4 bg-stone-50 px-4 py-3
                                    text-xs font-semibold text-stone-600">

                            <span>Base Drink</span>

                            <span class="text-center">
                                Distributed
                            </span>

                            <span class="text-center">
                                Sold
                            </span>

                            <span class="text-right">
                                Remaining
                            </span>

                        </div>

                        {{-- Table Body --}}
                        @foreach ($salesSummary as $summary)

                            <div class="grid grid-cols-4 border-t border-stone-200
                                        px-4 py-3 text-sm">

                                <span class="font-medium text-stone-800">
                                    {{ $summary['base_drink'] }}
                                </span>

                                <span class="text-center text-stone-600">
                                    {{ $summary['distributed'] }}
                                </span>

                                <span class="text-center font-semibold text-stone-800">
                                    {{ $summary['sold'] }}
                                </span>

                                <span class="text-right font-semibold
                                    {{ $summary['remaining'] < 0
                                        ? 'text-red-600'
                                        : 'text-emerald-700' }}">

                                    {{ $summary['remaining'] }}

                                </span>

                            </div>

                        @endforeach

                    </div>

                    {{-- Explanation --}}
                    <div class="mt-4 rounded-lg bg-stone-50 px-4 py-3
                                text-sm text-stone-600">

                        <span class="font-semibold">
                            Remaining
                        </span>
                        represents the difference between distributed and sold quantities:

                        <span class="font-semibold">
                            Distributed − Sold
                        </span>.

                    </div>

                @else

                    <div class="rounded-lg border border-dashed border-stone-300
                                px-4 py-6 text-center">

                        <p class="text-sm text-stone-500">
                            No base drink distribution data available for sales summary.
                        </p>

                    </div>

                @endif

            </x-app-card>



            {{-- ========================================================= --}}
            {{-- Base Drinks --}}
            {{-- ========================================================= --}}

            <x-app-card class="mt-6">

                <div class="mb-5">
                    <h3 class="text-base font-bold text-stone-900">
                        Base Drinks
                    </h3>

                    <p class="mt-1 text-sm text-stone-500">
                        Base drink distribution records.
                    </p>
                </div>

                @if ($distribution->productDetails->isNotEmpty())

                    <div class="overflow-hidden rounded-lg border border-stone-200">

                        <div
                            class="grid grid-cols-3
                                   bg-stone-50 px-4 py-3
                                   text-xs font-semibold text-stone-600"
                        >
                            <span>Base Drink</span>

                            <span class="text-center">
                                Distributed
                            </span>

                            <span class="text-right">
                                Returned
                            </span>
                        </div>

                        @foreach ($distribution->productDetails as $detail)

                            <div
                                class="grid grid-cols-3
                                       border-t border-stone-200
                                       px-4 py-3 text-sm"
                            >

                                <span class="font-medium text-stone-800">
                                    {{ $detail->baseDrink->name ?? '-' }}
                                </span>

                                <span class="text-center text-stone-600">
                                    {{ $detail->quantity_distributed }}
                                </span>

                                <span class="text-right text-stone-600">
                                    {{ $detail->quantity_returned }}
                                </span>

                            </div>

                        @endforeach

                    </div>

                @else

                    <div
                        class="rounded-lg border border-dashed
                               border-stone-300 px-4 py-6 text-center"
                    >
                        <p class="text-sm text-stone-500">
                            No base drinks were distributed.
                        </p>
                    </div>

                @endif

            </x-app-card>


            {{-- ========================================================= --}}
            {{-- Operational Items --}}
            {{-- ========================================================= --}}

            <x-app-card class="mt-6">

                <div class="mb-5">
                    <h3 class="text-base font-bold text-stone-900">
                        Operational Items
                    </h3>

                    <p class="mt-1 text-sm text-stone-500">
                        Operational items deducted from stock.
                    </p>
                </div>

                @if ($distribution->operationalDetails->isNotEmpty())

                    <div class="overflow-hidden rounded-lg border border-stone-200">

                        <div
                            class="grid grid-cols-4
                                   bg-stone-50 px-4 py-3
                                   text-xs font-semibold text-stone-600"
                        >
                            <span>Item</span>

                            <span class="text-center">
                                Distributed
                            </span>

                            <span class="text-center">
                                Returned
                            </span>

                            <span class="text-right">
                                Used
                            </span>
                        </div>

                        @foreach ($distribution->operationalDetails as $detail)

                            @php
                                $used =
                                    $detail->quantity_distributed
                                    - $detail->quantity_returned;
                            @endphp

                            <div
                                class="grid grid-cols-4
                                       border-t border-stone-200
                                       px-4 py-3 text-sm"
                            >

                                <div>
                                    <p class="font-medium text-stone-800">
                                        {{ $detail->operationalItem->name ?? '-' }}
                                    </p>

                                    @if ($detail->operationalItem)
                                        <p class="mt-0.5 text-xs text-stone-500">
                                            Unit:
                                            {{ $detail->operationalItem->unit }}
                                        </p>
                                    @endif
                                </div>

                                <span class="text-center text-stone-600">
                                    {{ $detail->quantity_distributed }}
                                </span>

                                <span class="text-center text-stone-600">
                                    {{ $detail->quantity_returned }}
                                </span>

                                <span class="text-right font-semibold text-stone-800">
                                    {{ $used }}
                                </span>

                            </div>

                        @endforeach

                    </div>

                    <div
                        class="mt-4 rounded-lg bg-amber-50 px-4 py-3
                               text-sm text-amber-800"
                    >
                        <span class="font-semibold">
                            Used
                        </span>
                        represents the net quantity consumed:

                        <span class="font-semibold">
                            Distributed − Returned
                        </span>.
                    </div>

                @else

                    <div
                        class="rounded-lg border border-dashed
                               border-stone-300 px-4 py-6 text-center"
                    >
                        <p class="text-sm text-stone-500">
                            No operational items were distributed.
                        </p>
                    </div>

                @endif

            </x-app-card>


            {{-- Back --}}
            <div class="mt-6">
                <a
                    href="{{ route('distributions.index') }}"
                    class="text-sm font-semibold text-stone-600
                           hover:text-stone-900"
                >
                    ← Back to distributions
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
