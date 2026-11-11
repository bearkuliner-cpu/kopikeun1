<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-bold text-stone-900">
                Edit Distribution
            </h2>

            <p class="mt-1 text-sm text-stone-500">
                Update distribution information and distributed items.
            </p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">

            @if ($errors->any())
                <div
                    class="mb-6 rounded-xl border border-red-200
                           bg-red-50 px-4 py-3 text-sm text-red-700"
                >
                    <p class="font-semibold">
                        Please fix the following errors:
                    </p>

                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif


            <form
                action="{{ route('distributions.update', $distribution) }}"
                method="POST"
                id="distribution-form"
                class="space-y-6"
                x-data="{ submitting: false }"
                @submit="
                    if (submitting) {
                        $event.preventDefault();
                    } else {
                        submitting = true;
                    }
                "
            >
                @csrf
                @method('PUT')


                {{-- ========================================================= --}}
                {{-- Distribution Information --}}
                {{-- ========================================================= --}}

                <x-app-card>

                    <div class="mb-5">
                        <h3 class="text-base font-bold text-stone-900">
                            Distribution Information
                        </h3>

                        <p class="mt-1 text-sm text-stone-500">
                            Basic information about this distribution.
                        </p>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">

                        {{-- Assignment --}}
                        <div>
                            <label
                                for="assignment_id"
                                class="mb-1.5 block text-sm font-semibold text-stone-700"
                            >
                                Assignment
                            </label>

                            <select
                                id="assignment_id"
                                name="assignment_id"
                                required
                                class="w-full rounded-lg border-stone-300
                                    text-sm shadow-sm
                                    focus:border-amber-500
                                    focus:ring-amber-500"
                            >
                                <option value="">
                                    Select assignment
                                </option>

                                @foreach ($assignments as $assignment)
                                    <option
                                        value="{{ $assignment->id }}"
                                        {{ old(
                                            'assignment_id',
                                            $distribution->assignment_id
                                        ) == $assignment->id ? 'selected' : '' }}
                                    >
                                        {{ $assignment->employee->employee_code }}
                                        -
                                        {{ $assignment->employee->user->name }}
                                        |
                                        {{ $assignment->cart->name ?? '-' }}
                                        -
                                        {{ $assignment->region->name ?? '-' }}
                                    </option>
                                @endforeach
                            </select>

                            @error('assignment_id')
                                <p class="mt-1 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                            <p class="mt-1 text-xs text-stone-500">
                                Select the assignment carried out by the employee.
                            </p>
                        </div>


                        {{-- Distribution Date --}}
                        <div>
                            <label
                                for="distribution_date"
                                class="mb-1.5 block text-sm font-semibold text-stone-700"
                            >
                                Distribution Date
                            </label>

                            <input
                                type="date"
                                id="distribution_date"
                                name="distribution_date"
                                value="{{ old(
                                    'distribution_date',
                                    $distribution->distribution_date->toDateString()
                                ) }}"
                                required
                                class="w-full rounded-lg border-stone-300
                                       text-sm shadow-sm
                                       focus:border-amber-500
                                       focus:ring-amber-500"
                            >

                            @error('distribution_date')
                                <p class="mt-1 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                    </div>


                    {{-- Notes --}}
                    <div class="mt-5">

                        <label
                            for="notes"
                            class="mb-1.5 block text-sm font-semibold text-stone-700"
                        >
                            Notes
                        </label>

                        <textarea
                            id="notes"
                            name="notes"
                            rows="3"
                            class="w-full rounded-lg border-stone-300
                                   text-sm shadow-sm
                                   focus:border-amber-500
                                   focus:ring-amber-500"
                        >{{ old('notes', $distribution->notes) }}</textarea>

                        @error('notes')
                            <p class="mt-1 text-xs text-red-600">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                </x-app-card>


                {{-- ========================================================= --}}
                {{-- Sales Summary --}}
                {{-- ========================================================= --}}

                <x-app-card>
                    <div class="mb-5">
                        <h3 class="text-base font-bold text-stone-900">
                            Sales Summary
                        </h3>

                        <p class="mt-1 text-sm text-stone-500">
                            Comparison between distributed and sold base drinks.
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-stone-200 text-left">
                                    <th class="px-3 py-3 font-semibold text-stone-600">
                                        Base Drink
                                    </th>
                                    <th class="px-3 py-3 text-right font-semibold text-stone-600">
                                        Distributed
                                    </th>
                                    <th class="px-3 py-3 text-right font-semibold text-stone-600">
                                        Sold
                                    </th>
                                    <th class="px-3 py-3 text-right font-semibold text-stone-600">
                                        Difference
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($salesSummary as $item)
                                    <tr class="border-b border-stone-100">
                                        <td class="px-3 py-3 font-medium text-stone-800">
                                            {{ $item['base_drink'] }}
                                        </td>

                                        <td class="px-3 py-3 text-right text-stone-700">
                                            {{ number_format($item['distributed']) }}
                                        </td>

                                        <td class="px-3 py-3 text-right text-green-700">
                                            {{ number_format($item['sold']) }}
                                        </td>

                                        <td class="px-3 py-3 text-right font-semibold text-amber-700">
                                            {{ number_format($item['remaining']) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4"
                                            class="px-3 py-6 text-center text-stone-500">
                                            No base drink distribution data available.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                            @if ($salesSummary->isNotEmpty())
                                <tfoot>
                                    <tr class="bg-stone-50 font-bold">
                                        <td class="px-3 py-3 text-stone-800">
                                            Total
                                        </td>

                                        <td class="px-3 py-3 text-right">
                                            {{ number_format($salesSummary->sum('distributed')) }}
                                        </td>

                                        <td class="px-3 py-3 text-right text-green-700">
                                            {{ number_format($salesSummary->sum('sold')) }}
                                        </td>

                                        <td class="px-3 py-3 text-right text-amber-700">
                                            {{ number_format($salesSummary->sum('remaining')) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>

                    <p class="mt-3 text-xs text-stone-500">
                        Difference is calculated from distributed quantity minus sold quantity.
                        It does not represent the actual returned quantity.
                    </p>
                </x-app-card>



                {{-- ========================================================= --}}
                {{-- Base Drinks --}}
                {{-- ========================================================= --}}

                <x-app-card>

                    <div
                        class="flex flex-col gap-3
                               sm:flex-row sm:items-center
                               sm:justify-between"
                    >

                        <div>
                            <h3 class="text-base font-bold text-stone-900">
                                Base Drinks
                            </h3>

                            <p class="mt-1 text-sm text-stone-500">
                                Base drinks do not affect stock.
                            </p>
                        </div>

                        <button
                            type="button"
                            id="add-product"
                            class="rounded-lg bg-amber-700 px-3 py-2
                                   text-xs font-semibold text-white
                                   hover:bg-amber-800"
                        >
                            Add Base Drink
                        </button>

                    </div>


                    <div
                        id="products-container"
                        class="mt-5 space-y-3"
                    >

                        @foreach ($distribution->productDetails as $index => $detail)

                            <div
                                class="product-row rounded-lg
                                       border border-stone-200 p-4"
                            >

                                <div
                                    class="grid gap-3
                                           sm:grid-cols-[1fr_150px_150px_auto]"
                                >

                                    {{-- Base Drink --}}
                                    <div>

                                        <label
                                            class="mb-1 block text-xs font-semibold text-stone-600"
                                        >
                                            Base Drink
                                        </label>

                                        <select
                                            name="products[{{ $index }}][base_drink_id]"
                                            required
                                            class="w-full rounded-lg border-stone-300
                                                   text-sm shadow-sm
                                                   focus:border-amber-500
                                                   focus:ring-amber-500"
                                        >
                                            @foreach ($baseDrinks as $baseDrink)

                                                <option
                                                    value="{{ $baseDrink->id }}"
                                                    {{ $detail->base_drink_id == $baseDrink->id ? 'selected' : '' }}
                                                >
                                                    {{ $baseDrink->name }}
                                                </option>

                                            @endforeach
                                        </select>

                                    </div>


                                    {{-- Distributed --}}
                                    <div>

                                        <label
                                            class="mb-1 block text-xs font-semibold text-stone-600"
                                        >
                                            Distributed
                                        </label>

                                        <input
                                            type="number"
                                            step="1"
                                            min="1"
                                            name="products[{{ $index }}][quantity_distributed]"
                                            value="{{ old(
                                                "products.$index.quantity_distributed",
                                                (int) $detail->quantity_distributed
                                            ) }}"
                                            required
                                            class="w-full rounded-lg border-stone-300
                                                   text-sm shadow-sm
                                                   focus:border-amber-500
                                                   focus:ring-amber-500"
                                        >

                                    </div>


                                    {{-- Returned --}}
                                    <div>

                                        <label
                                            class="mb-1 block text-xs font-semibold text-stone-600"
                                        >
                                            Returned
                                        </label>

                                        <input
                                            type="number"
                                            step="1"
                                            min="0"
                                            name="products[{{ $index }}][quantity_returned]"
                                            value="{{ old(
                                                "products.$index.quantity_returned",
                                                (int) $detail->quantity_returned
                                            ) }}"
                                            required
                                            class="w-full rounded-lg border-stone-300
                                                   text-sm shadow-sm
                                                   focus:border-amber-500
                                                   focus:ring-amber-500"
                                        >

                                    </div>


                                    {{-- Remove --}}
                                    <div class="flex items-end">

                                        <button
                                            type="button"
                                            class="remove-product w-full rounded-lg
                                                   bg-red-50 px-3 py-2
                                                   text-xs font-semibold text-red-600
                                                   hover:bg-red-100
                                                   sm:w-auto"
                                        >
                                            Remove
                                        </button>

                                    </div>

                                </div>

                            </div>

                        @endforeach

                    </div>


                    <div
                        id="products-empty"
                        class="mt-5 rounded-lg border border-dashed
                               border-stone-300 px-4 py-6 text-center
                               {{ $distribution->productDetails->isNotEmpty()
                                    ? 'hidden'
                                    : '' }}"
                    >
                        <p class="text-sm font-medium text-stone-600">
                            No base drinks added.
                        </p>

                        <p class="mt-1 text-xs text-stone-500">
                            Click "Add Base Drink" to add an item.
                        </p>
                    </div>

                </x-app-card>


                {{-- ========================================================= --}}
                {{-- Operational Items --}}
                {{-- ========================================================= --}}

                <x-app-card>

                    <div
                        class="flex flex-col gap-3
                               sm:flex-row sm:items-center
                               sm:justify-between"
                    >

                        <div>
                            <h3 class="text-base font-bold text-stone-900">
                                Operational Items
                            </h3>

                            <p class="mt-1 text-sm text-stone-500">
                                Changes will be reflected in operational item stock.
                            </p>
                        </div>

                        <button
                            type="button"
                            id="add-operational-item"
                            class="rounded-lg bg-amber-700 px-3 py-2
                                   text-xs font-semibold text-white
                                   hover:bg-amber-800"
                        >
                            Add Operational Item
                        </button>

                    </div>


                    <div
                        id="operational-items-container"
                        class="mt-5 space-y-3"
                    >

                        @foreach (
                            $distribution->operationalDetails
                            as $index => $detail
                        )

                            <div
                                class="operational-item-row rounded-lg
                                       border border-stone-200 p-4"
                            >

                                <div
                                    class="grid gap-3
                                           sm:grid-cols-[1fr_150px_150px_auto]"
                                >

                                    {{-- Item --}}
                                    <div>

                                        <label
                                            class="mb-1 block text-xs font-semibold text-stone-600"
                                        >
                                            Operational Item
                                        </label>

                                        <input
                                            type="hidden"
                                            name="operational_items[{{ $index }}][id]"
                                            value="{{ $detail->id }}"
                                        >

                                        <select
                                            name="operational_items[{{ $index }}][operational_item_id]"
                                            required
                                            class="operational-item-select w-full
                                                   rounded-lg border-stone-300
                                                   text-sm shadow-sm
                                                   focus:border-amber-500
                                                   focus:ring-amber-500"
                                        >

                                            @foreach ($operationalItems as $operationalItem)

                                                <option
                                                    value="{{ $operationalItem->id }}"
                                                    data-stock="{{ $operationalItem->stock }}"
                                                    {{ $detail->operational_item_id == $operationalItem->id ? 'selected' : '' }}
                                                >
                                                    {{ $operationalItem->name }}
                                                    (Stock:
                                                    {{ $operationalItem->stock }}
                                                    {{ $operationalItem->unit }})
                                                </option>

                                            @endforeach

                                        </select>

                                        <p class="operational-stock mt-1 text-xs text-stone-500">
                                            Available stock:
                                            {{ $detail->operationalItem->stock ?? 0 }}
                                            {{ $detail->operationalItem->unit ?? '' }}
                                        </p>

                                    </div>


                                    {{-- Distributed --}}
                                    <div>

                                        <label
                                            class="mb-1 block text-xs font-semibold text-stone-600"
                                        >
                                            Distributed
                                        </label>

                                        <input
                                            type="number"
                                            min="1"
                                            name="operational_items[{{ $index }}][quantity_distributed]"
                                            value="{{ old(
                                                "operational_items.$index.quantity_distributed",
                                                (int) $detail->quantity_distributed
                                            ) }}"
                                            required
                                            class="operational-quantity w-full
                                                   rounded-lg border-stone-300
                                                   text-sm shadow-sm
                                                   focus:border-amber-500
                                                   focus:ring-amber-500"
                                        >

                                    </div>


                                    {{-- Returned --}}
                                    <div>

                                        <label
                                            class="mb-1 block text-xs font-semibold text-stone-600"
                                        >
                                            Returned
                                        </label>

                                        <input
                                            type="number"
                                            min="0"
                                            name="operational_items[{{ $index }}][quantity_returned]"
                                            value="{{ old(
                                                "operational_items.$index.quantity_returned",
                                                (int) $detail->quantity_returned
                                            ) }}"
                                            required
                                            class="w-full rounded-lg border-stone-300
                                                   text-sm shadow-sm
                                                   focus:border-amber-500
                                                   focus:ring-amber-500"
                                        >

                                    </div>


                                    {{-- Remove --}}
                                    <div class="flex items-end">

                                        <button
                                            type="button"
                                            class="remove-operational-item w-full
                                                   rounded-lg bg-red-50 px-3 py-2
                                                   text-xs font-semibold text-red-600
                                                   hover:bg-red-100
                                                   sm:w-auto"
                                        >
                                            Remove
                                        </button>

                                    </div>

                                </div>

                            </div>

                        @endforeach

                    </div>


                    <div
                        id="operational-items-empty"
                        class="mt-5 rounded-lg border border-dashed
                               border-stone-300 px-4 py-6 text-center
                               {{ $distribution->operationalDetails->isNotEmpty()
                                    ? 'hidden'
                                    : '' }}"
                    >
                        <p class="text-sm font-medium text-stone-600">
                            No operational items added.
                        </p>

                        <p class="mt-1 text-xs text-stone-500">
                            Click "Add Operational Item" to add an item.
                        </p>
                    </div>

                </x-app-card>


                {{-- ========================================================= --}}
                {{-- Actions --}}
                {{-- ========================================================= --}}

                <div class="flex items-center justify-end gap-3">

                    <a
                        href="{{ route('distributions.show', $distribution) }}"
                        class="rounded-lg bg-stone-100 px-4 py-2
                               text-sm font-semibold text-stone-700
                               hover:bg-stone-200"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        :disabled="submitting"
                        class="rounded-lg bg-amber-700 px-5 py-2
                               text-sm font-semibold text-white
                               hover:bg-amber-800"
                    >
                        <span x-show="!submitting">Update</span>
                        <span x-show="submitting">Saving...</span>
                    </button>

                </div>

            </form>

        </div>
    </div>


    {{-- ============================================================= --}}
    {{-- JavaScript --}}
    {{-- ============================================================= --}}

    <script>

        let productIndex =
            {{ $distribution->productDetails->count() }};

        let operationalItemIndex =
            {{ $distribution->operationalDetails->count() }};


        /*
        |--------------------------------------------------------------------------
        | Base Drinks
        |--------------------------------------------------------------------------
        */

        const addProductButton =
            document.getElementById('add-product');

        const productsContainer =
            document.getElementById('products-container');

        const productsEmpty =
            document.getElementById('products-empty');


        addProductButton.addEventListener(
            'click',
            () => {

                const row =
                    document.createElement('div');

                row.className =
                    'product-row rounded-lg border border-stone-200 p-4';

                row.innerHTML = `
                    <div class="grid gap-3
                                sm:grid-cols-[1fr_150px_150px_auto]">

                        <div>

                            <label
                                class="mb-1 block text-xs font-semibold text-stone-600"
                            >
                                Base Drink
                            </label>

                            <select
                                name="products[${productIndex}][base_drink_id]"
                                required
                                class="w-full rounded-lg border-stone-300
                                       text-sm shadow-sm
                                       focus:border-amber-500
                                       focus:ring-amber-500"
                            >

                                <option value="">
                                    Select base drink
                                </option>

                                @foreach ($baseDrinks as $baseDrink)

                                    <option value="{{ $baseDrink->id }}">
                                        {{ $baseDrink->name }}
                                    </option>

                                @endforeach

                            </select>

                        </div>


                        <div>

                            <label
                                class="mb-1 block text-xs font-semibold text-stone-600"
                            >
                                Distributed
                            </label>

                            <input
                                type="number"
                                step="1"
                                min="1"
                                name="products[${productIndex}][quantity_distributed]"
                                required
                                class="w-full rounded-lg border-stone-300
                                       text-sm shadow-sm
                                       focus:border-amber-500
                                       focus:ring-amber-500"
                            >

                        </div>


                        <div>

                            <label
                                class="mb-1 block text-xs font-semibold text-stone-600"
                            >
                                Returned
                            </label>

                            <input
                                type="number"
                                step="1"
                                min="0"
                                name="products[${productIndex}][quantity_returned]"
                                value="0"
                                required
                                class="w-full rounded-lg border-stone-300
                                       text-sm shadow-sm
                                       focus:border-amber-500
                                       focus:ring-amber-500"
                            >

                        </div>


                        <div class="flex items-end">

                            <button
                                type="button"
                                class="remove-product w-full rounded-lg
                                       bg-red-50 px-3 py-2
                                       text-xs font-semibold text-red-600
                                       hover:bg-red-100
                                       sm:w-auto"
                            >
                                Remove
                            </button>

                        </div>

                    </div>
                `;

                productsContainer.appendChild(row);

                productIndex++;

                updateProductEmptyState();
            }
        );


        document.addEventListener(
            'click',
            (event) => {

                if (
                    event.target.classList.contains(
                        'remove-product'
                    )
                ) {

                    event.target
                        .closest('.product-row')
                        .remove();

                    updateProductEmptyState();
                }

            }
        );


        function updateProductEmptyState()
        {
            const hasRows =
                productsContainer.querySelector(
                    '.product-row'
                );

            productsEmpty.classList.toggle(
                'hidden',
                !!hasRows
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Operational Items
        |--------------------------------------------------------------------------
        */

        const addOperationalItemButton =
            document.getElementById(
                'add-operational-item'
            );

        const operationalItemsContainer =
            document.getElementById(
                'operational-items-container'
            );

        const operationalItemsEmpty =
            document.getElementById(
                'operational-items-empty'
            );


        addOperationalItemButton.addEventListener(
            'click',
            () => {

                const row =
                    document.createElement('div');

                row.className =
                    'operational-item-row rounded-lg border border-stone-200 p-4';

                row.innerHTML = `
                    <div class="grid gap-3
                                sm:grid-cols-[1fr_150px_150px_auto]">

                        <div>

                            <label
                                class="mb-1 block text-xs font-semibold text-stone-600"
                            >
                                Operational Item
                            </label>

                            <select
                                name="operational_items[${operationalItemIndex}][operational_item_id]"
                                required
                                class="operational-item-select w-full
                                       rounded-lg border-stone-300
                                       text-sm shadow-sm
                                       focus:border-amber-500
                                       focus:ring-amber-500"
                            >

                                <option value="">
                                    Select item
                                </option>

                                @foreach ($operationalItems as $operationalItem)

                                    <option
                                        value="{{ $operationalItem->id }}"
                                        data-stock="{{ $operationalItem->stock }}"
                                    >
                                        {{ $operationalItem->name }}
                                        (Stock:
                                        {{ $operationalItem->stock }}
                                        {{ $operationalItem->unit }})
                                    </option>

                                @endforeach

                            </select>

                            <p class="operational-stock mt-1 text-xs text-stone-500">
                            </p>

                        </div>


                        <div>

                            <label
                                class="mb-1 block text-xs font-semibold text-stone-600"
                            >
                                Distributed
                            </label>

                            <input
                                type="number"
                                min="1"
                                name="operational_items[${operationalItemIndex}][quantity_distributed]"
                                required
                                class="operational-quantity w-full
                                       rounded-lg border-stone-300
                                       text-sm shadow-sm
                                       focus:border-amber-500
                                       focus:ring-amber-500"
                            >

                        </div>


                        <div>

                            <label
                                class="mb-1 block text-xs font-semibold text-stone-600"
                            >
                                Returned
                            </label>

                            <input
                                type="number"
                                min="0"
                                name="operational_items[${operationalItemIndex}][quantity_returned]"
                                value="0"
                                required
                                class="w-full rounded-lg border-stone-300
                                       text-sm shadow-sm
                                       focus:border-amber-500
                                       focus:ring-amber-500"
                            >

                        </div>


                        <div class="flex items-end">

                            <button
                                type="button"
                                class="remove-operational-item w-full
                                       rounded-lg bg-red-50 px-3 py-2
                                       text-xs font-semibold text-red-600
                                       hover:bg-red-100
                                       sm:w-auto"
                            >
                                Remove
                            </button>

                        </div>

                    </div>
                `;

                operationalItemsContainer.appendChild(row);

                operationalItemIndex++;

                updateOperationalItemEmptyState();
            }
        );


        document.addEventListener(
            'click',
            (event) => {

                if (
                    event.target.classList.contains(
                        'remove-operational-item'
                    )
                ) {

                    event.target
                        .closest('.operational-item-row')
                        .remove();

                    updateOperationalItemEmptyState();
                }

            }
        );


        document.addEventListener(
            'change',
            (event) => {

                if (
                    event.target.classList.contains(
                        'operational-item-select'
                    )
                ) {

                    const select =
                        event.target;

                    const row =
                        select.closest(
                            '.operational-item-row'
                        );

                    const stockDisplay =
                        row.querySelector(
                            '.operational-stock'
                        );

                    const selectedOption =
                        select.options[
                            select.selectedIndex
                        ];

                    if (
                        selectedOption &&
                        selectedOption.dataset.stock
                    ) {

                        stockDisplay.textContent =
                            `Available stock: ${
                                selectedOption.dataset.stock
                            }`;

                    } else {

                        stockDisplay.textContent =
                            '';

                    }

                }

            }
        );


        function updateOperationalItemEmptyState()
        {
            const hasRows =
                operationalItemsContainer.querySelector(
                    '.operational-item-row'
                );

            operationalItemsEmpty.classList.toggle(
                'hidden',
                !!hasRows
            );
        }

    </script>

</x-app-layout>
