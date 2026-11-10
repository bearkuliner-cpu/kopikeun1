<x-app-layout>

    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-stone-800">
                Owner Dashboard
            </h2>

            <p class="mt-1 text-sm text-stone-500">
                Business and financial overview
            </p>
        </div>
    </x-slot>


    <div class="py-6">
        <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12">

            {{-- ========================================================= --}}
            {{-- Period Information --}}
            {{-- ========================================================= --}}

            <div class="mb-6">

                <div class="rounded-xl border border-stone-200 bg-white p-4 shadow-sm">

                    <p class="text-sm text-stone-500">
                        Current Period
                    </p>

                    <p class="mt-1 text-sm font-semibold text-stone-800">
                        {{ $startDate->translatedFormat('d F Y') }}
                        -
                        {{ $endDate->translatedFormat('d F Y') }}
                    </p>

                </div>

            </div>


            {{-- ========================================================= --}}
            {{-- Summary Cards --}}
            {{-- ========================================================= --}}

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

                {{-- Total Income --}}
                <div class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm">

                    <p class="text-sm font-medium text-stone-500">
                        Total Income
                    </p>

                    <p class="mt-2 text-2xl font-bold text-stone-800">
                        Rp {{ number_format($totalIncome, 0, ',', '.') }}
                    </p>

                    <p class="mt-1 text-xs text-stone-500">
                        Outlet + Distribution Sales
                    </p>

                </div>


                {{-- Total Expense --}}
                <div class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm">

                    <p class="text-sm font-medium text-stone-500">
                        Total Expenses
                    </p>

                    <p class="mt-2 text-2xl font-bold text-stone-800">
                        Rp {{ number_format($totalExpense, 0, ',', '.') }}
                    </p>

                    <p class="mt-1 text-xs text-stone-500">
                        Recorded business expenses
                    </p>

                </div>


                {{-- Profit --}}
                <div class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm">

                    <p class="text-sm font-medium text-stone-500">
                        Profit
                    </p>

                    <p
                        class="mt-2 text-2xl font-bold
                        {{ $profit >= 0
                            ? 'text-green-700'
                            : 'text-red-700' }}"
                    >
                        Rp {{ number_format($profit, 0, ',', '.') }}
                    </p>

                    <p class="mt-1 text-xs text-stone-500">
                        Income - Expenses
                    </p>

                </div>


                {{-- Active Products --}}
                <div class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm">

                    <p class="text-sm font-medium text-stone-500">
                        Active Products
                    </p>

                    <p class="mt-2 text-2xl font-bold text-stone-800">
                        {{ $activeProducts }}
                    </p>

                    <p class="mt-1 text-xs text-stone-500">
                        Currently available
                    </p>

                </div>

            </div>



            {{-- ========================================================= --}}
            {{-- Income Breakdown --}}
            {{-- ========================================================= --}}

            <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-2">


                {{-- Income --}}
                <div class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm">

                    <h3 class="text-base font-semibold text-stone-800">
                        Income Summary
                    </h3>

                    <div class="mt-4 space-y-4">

                        {{-- Outlet --}}
                        <div class="flex items-center justify-between">

                            <span class="text-sm text-stone-500">
                                Outlet Sales
                            </span>

                            <span class="text-sm font-semibold text-stone-800">
                                Rp {{ number_format($outletIncome, 0, ',', '.') }}
                            </span>

                        </div>


                        {{-- Distribution --}}
                        <div class="flex items-center justify-between">

                            <span class="text-sm text-stone-500">
                                Distribution Sales
                            </span>

                            <span class="text-sm font-semibold text-stone-800">
                                Rp {{ number_format($distributionIncome, 0, ',', '.') }}
                            </span>

                        </div>


                        {{-- Total --}}
                        <div class="border-t border-stone-200 pt-4">

                            <div class="flex items-center justify-between">

                                <span class="text-sm font-semibold text-stone-700">
                                    Total Income
                                </span>

                                <span class="text-base font-bold text-stone-900">
                                    Rp {{ number_format($totalIncome, 0, ',', '.') }}
                                </span>

                            </div>

                        </div>

                    </div>

                </div>



                {{-- ===================================================== --}}
                {{-- Expenses --}}
                {{-- ===================================================== --}}

                <div class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm">

                    <h3 class="text-base font-semibold text-stone-800">
                        Expense Summary
                    </h3>

                    <div class="mt-4 space-y-4">

                        {{-- Incoming Goods --}}
                        <div class="flex items-center justify-between">

                            <span class="text-sm text-stone-500">
                                Incoming Goods
                            </span>

                            <span class="text-sm font-semibold text-stone-800">
                                Rp {{ number_format($incomingGoodsExpense, 0, ',', '.') }}
                            </span>

                        </div>


                        {{-- Operational --}}
                        <div class="flex items-center justify-between">

                            <span class="text-sm text-stone-500">
                                Operational Expenses
                            </span>

                            <span class="text-sm font-semibold text-stone-800">
                                Rp {{ number_format($operationalExpense, 0, ',', '.') }}
                            </span>

                        </div>


                        {{-- Total --}}
                        <div class="border-t border-stone-200 pt-4">

                            <div class="flex items-center justify-between">

                                <span class="text-sm font-semibold text-stone-700">
                                    Total Expenses
                                </span>

                                <span class="text-base font-bold text-stone-900">
                                    Rp {{ number_format($totalExpense, 0, ',', '.') }}
                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            </div>



            {{-- ========================================================= --}}
            {{-- Income and Expense Chart --}}
            {{-- ========================================================= --}}

            <div class="mt-6 rounded-xl border border-stone-200 bg-white p-5 shadow-sm">

                <div class="mb-5">

                    <h3 class="text-base font-semibold text-stone-800">
                        Income and Expense Overview
                    </h3>

                    <p class="mt-1 text-sm text-stone-500">
                        Daily income and expenses for the current period
                    </p>

                </div>


                <div class="relative h-80 w-full">

                    <canvas id="incomeExpenseChart"></canvas>

                </div>

            </div>

        </div>
    </div>



    {{-- ============================================================= --}}
    {{-- Chart --}}
    {{-- ============================================================= --}}

    @push('scripts')

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script>

            const chartLabels = @js($chartLabels);

            const chartIncome = @js($chartIncome);

            const chartExpense = @js($chartExpense);


            const chartCanvas =
                document.getElementById(
                    'incomeExpenseChart'
                );


            new Chart(chartCanvas, {

                type: 'line',


                data: {

                    labels: chartLabels,

                    datasets: [

                        {
                            label: 'Income',

                            data: chartIncome,

                            tension: 0.3,

                            borderWidth: 2,

                            fill: false,
                        },


                        {
                            label: 'Expenses',

                            data: chartExpense,

                            tension: 0.3,

                            borderWidth: 2,

                            fill: false,
                        }

                    ]

                },


                options: {

                    responsive: true,

                    maintainAspectRatio: false,


                    interaction: {

                        intersect: false,

                        mode: 'index',

                    },


                    plugins: {

                        legend: {

                            display: true,

                        },


                        tooltip: {

                            callbacks: {

                                label: function (context) {

                                    return (
                                        context.dataset.label +
                                        ': Rp ' +
                                        new Intl.NumberFormat(
                                            'id-ID'
                                        ).format(
                                            context.raw
                                        )
                                    );

                                }

                            }

                        }

                    },


                    scales: {

                        y: {

                            beginAtZero: true,


                            ticks: {

                                callback: function (value) {

                                    return (
                                        'Rp ' +
                                        new Intl.NumberFormat(
                                            'id-ID'
                                        ).format(value)
                                    );

                                }

                            }

                        }

                    }

                }

            });

        </script>

    @endpush

</x-app-layout>
