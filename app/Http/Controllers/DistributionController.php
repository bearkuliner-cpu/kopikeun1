<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Distribution;
use App\Models\OperationalItem;
use App\Models\BaseDrink;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DistributionController extends Controller
{
    /**
     * Display distribution list.
     */
    public function index(): View
    {
        $distributions = Distribution::with([
            'assignment.employee.user',
            'assignment.cart',
            'assignment.region',
            'productDetails.baseDrink',
            'operationalDetails.operationalItem',
        ])
            ->latest('distribution_date')
            ->latest()
            ->get();

        return view(
            'distributions.index',
            compact('distributions')
        );
    }

    /**
     * Show create form.
     */
    public function create(): View
    {
        /*
         * Get assignments with their related data.
         */
        $assignments = Assignment::with([
            'employee.user',
            'cart',
            'region',
        ])
            ->orderBy('start_date', 'desc')
            ->get();

        /*
         * Base drinks.
         * Base drinks DO NOT affect stock.
         */
        $baseDrinks = BaseDrink::where('status', 'Active')
            ->orderBy('name')
            ->get();

        /*
         * Operational items.
         * These items DO affect stock.
         */
        $operationalItems = OperationalItem::orderBy('name')
            ->get();

        return view(
            'distributions.create',
            compact(
                'assignments',
                'baseDrinks',
                'operationalItems'
            )
        );
    }

    /**
     * Store a newly created distribution.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            /*
             * Distribution information
             */
            'assignment_id' => [
                'required',
                'exists:assignments,id',
            ],

            'distribution_date' => [
                'required',
                'date',
            ],

            'notes' => [
                'nullable',
                'string',
            ],

            /*
             * Base drinks
             */
            'products' => [
                'nullable',
                'array',
            ],

            'products.*.base_drink_id' => [
                'required',
                'exists:base_drinks,id',
            ],

            'products.*.quantity_distributed' => [
                'required',
                'integer',
                'min:1',
            ],

            'products.*.quantity_returned' => [
                'required',
                'integer',
                'min:0',
            ],

            /*
             * Operational items
             */
            'operational_items' => [
                'nullable',
                'array',
            ],

            'operational_items.*.operational_item_id' => [
                'required',
                'exists:operational_items,id',
            ],

            'operational_items.*.quantity_distributed' => [
                'required',
                'integer',
                'min:1',
            ],

            'operational_items.*.quantity_returned' => [
                'required',
                'integer',
                'min:0',
            ],
        ]);

        /*
         * Validate:
         *
         * returned <= distributed
         */
        foreach ($validated['products'] ?? [] as $index => $item) {
            if (
                $item['quantity_returned']
                > $item['quantity_distributed']
            ) {
                return back()
                    ->withInput()
                    ->withErrors([
                        "products.$index.quantity_returned" =>
                            'Returned quantity cannot be greater than distributed quantity.',
                    ]);
            }
        }

        foreach ($validated['operational_items'] ?? [] as $index => $item) {
            if (
                $item['quantity_returned']
                > $item['quantity_distributed']
            ) {
                return back()
                    ->withInput()
                    ->withErrors([
                        "operational_items.$index.quantity_returned" =>
                            'Returned quantity cannot be greater than distributed quantity.',
                    ]);
            }
        }

        DB::transaction(function () use ($validated) {

            /*
             * Create distribution header.
             */
            $distribution = Distribution::create([
                'assignment_id' =>
                    $validated['assignment_id'],

                'distribution_date' =>
                    $validated['distribution_date'],

                'notes' =>
                    $validated['notes'] ?? null,
            ]);

            /*
             * Base drinks.
             *
             * Base drinks do NOT affect stock.
             */
            foreach (
                $validated['products'] ?? []
                as $item
            ) {
                $distribution->productDetails()->create([
                    'base_drink_id' =>
                        $item['base_drink_id'],

                    'quantity_distributed' =>
                        $item['quantity_distributed'],

                    'quantity_returned' =>
                        $item['quantity_returned'],
                ]);
            }

            /*
             * Operational items.
             */
            foreach (
                $validated['operational_items'] ?? []
                as $item
            ) {
                $operationalItem = OperationalItem::findOrFail(
                    $item['operational_item_id']
                );

                $distributed =
                    $item['quantity_distributed'];

                $returned =
                    $item['quantity_returned'];

                /*
                 * Actual quantity that remains outside
                 * the warehouse.
                 */
                $netQuantity =
                    $distributed - $returned;

                /*
                 * Make sure stock is sufficient.
                 */
                if ($netQuantity > $operationalItem->stock) {
                    throw new \Exception(
                        "Insufficient stock for {$operationalItem->name}."
                    );
                }

                /*
                 * Create detail.
                 */
                $distribution->operationalDetails()->create([
                    'operational_item_id' =>
                        $operationalItem->id,

                    'quantity_distributed' =>
                        $distributed,

                    'quantity_returned' =>
                        $returned,
                ]);

                /*
                 * Reduce stock only by the net quantity.
                 */
                if ($netQuantity > 0) {
                    $operationalItem->decrement(
                        'stock',
                        $netQuantity
                    );
                }
            }
        });

        return redirect()
            ->route('distributions.index')
            ->with(
                'success',
                'Distribution created successfully.'
            );
    }

    /**
     * Display the specified distribution.
     */
    public function show(
        Distribution $distribution
    ): View {
        $distribution->load([
            'assignment.employee.user',
            'assignment.cart',
            'assignment.region',
            'productDetails.baseDrink',
            'operationalDetails.operationalItem',
        ]);

        return view(
            'distributions.show',
            compact('distribution')
        );
    }

    /**
     * Show edit form.
     */
    public function edit(
        Distribution $distribution
    ): View {
        $distribution->load([
            'assignment.employee.user',
            'assignment.cart',
            'assignment.region',
            'productDetails.baseDrink',
            'operationalDetails.operationalItem',
        ]);

        /*
         * Assignment list.
         */
        $assignments = Assignment::with([
            'employee.user',
            'cart',
            'region',
        ])
            ->orderBy('start_date', 'desc')
            ->get();

        /*
         * Base drinks.
         */
        $baseDrinks = BaseDrink::where('status', 'Active')
            ->orderBy('name')
            ->get();

        /*
         * Operational items.
         *
         * Include all items so that the user can select
         * any operational item during edit.
         */
        $operationalItems = OperationalItem::orderBy('name')
            ->get();
        
        $sales = Sale::with([
            'details.product',
        ])
            ->where('distribution_id', $distribution->id)
            ->get();

        $soldQuantities = $sales
            ->flatMap(fn ($sale) => $sale->details)
            ->filter(fn ($detail) => $detail->product !== null)
            ->groupBy(fn ($detail) => $detail->product->base_drink_id)
            ->map(fn ($details) => $details->sum('quantity'));

        $salesSummary = $distribution->productDetails
            ->groupBy('base_drink_id')
            ->map(function ($details, $baseDrinkId) use ($soldQuantities) {
                $firstDetail = $details->first();

                $distributed = $details->sum('quantity_distributed');
                $sold = (int) ($soldQuantities->get($baseDrinkId, 0));

                return [
                    'base_drink' => $firstDetail->baseDrink->name,
                    'distributed' => $distributed,
                    'sold' => $sold,
                    'remaining' => $distributed - $sold,
                ];
            })
            ->values();

        return view(
            'distributions.edit',
            compact(
                'distribution',
                'assignments',
                'baseDrinks',
                'operationalItems',
                'salesSummary'
            )
        );
    }

    /**
     * Update the specified distribution.
     */
    public function update(
        Request $request,
        Distribution $distribution
    ): RedirectResponse {
        $validated = $request->validate([
            /*
             * Distribution information
             */
            'assignment_id' => [
                'required',
                'exists:assignments,id',
            ],

            'distribution_date' => [
                'required',
                'date',
            ],

            'notes' => [
                'nullable',
                'string',
            ],

            /*
             * Base drinks
             */
            'products' => [
                'nullable',
                'array',
            ],

            'products.*.base_drink_id' => [
                'required',
                'exists:base_drinks,id',
            ],

            'products.*.quantity_distributed' => [
                'required',
                'integer',
                'min:1',
            ],

            'products.*.quantity_returned' => [
                'required',
                'integer',
                'min:0',
            ],

            /*
             * Operational items
             */
            'operational_items' => [
                'nullable',
                'array',
            ],

            'operational_items.*.operational_item_id' => [
                'required',
                'exists:operational_items,id',
            ],

            'operational_items.*.quantity_distributed' => [
                'required',
                'integer',
                'min:1',
            ],

            'operational_items.*.quantity_returned' => [
                'required',
                'integer',
                'min:0',
            ],
        ]);

        /*
         * Validate returned <= distributed.
         */
        foreach ($validated['products'] ?? [] as $index => $item) {
            if (
                $item['quantity_returned']
                > $item['quantity_distributed']
            ) {
                return back()
                    ->withInput()
                    ->withErrors([
                        "products.$index.quantity_returned" =>
                            'Returned quantity cannot be greater than distributed quantity.',
                    ]);
            }
        }

        foreach ($validated['operational_items'] ?? [] as $index => $item) {
            if (
                $item['quantity_returned']
                > $item['quantity_distributed']
            ) {
                return back()
                    ->withInput()
                    ->withErrors([
                        "operational_items.$index.quantity_returned" =>
                            'Returned quantity cannot be greater than distributed quantity.',
                    ]);
            }
        }

        DB::transaction(function () use (
            $validated,
            $distribution
        ) {

            /*
             * =========================================================
             * 1. RESTORE OLD OPERATIONAL STOCK
             * =========================================================
             *
             * Example:
             *
             * Old distributed = 10
             * Old returned    = 5
             *
             * Net stock used = 10 - 5 = 5
             *
             * Therefore restore 5.
             */
            foreach (
                $distribution->operationalDetails
                as $detail
            ) {
                $distributed =
                    $detail->quantity_distributed;

                $returned =
                    $detail->quantity_returned;

                $netQuantity =
                    $distributed - $returned;

                if ($netQuantity > 0) {
                    $detail->operationalItem->increment(
                        'stock',
                        $netQuantity
                    );
                }
            }

            /*
             * =========================================================
             * 2. UPDATE DISTRIBUTION HEADER
             * =========================================================
             */
            $distribution->update([
                'assignment_id' =>
                    $validated['assignment_id'],

                'distribution_date' =>
                    $validated['distribution_date'],

                'notes' =>
                    $validated['notes'] ?? null,
            ]);

            /*
             * =========================================================
             * 3. DELETE OLD DETAILS
             * =========================================================
             */
            $distribution->productDetails()->delete();

            $distribution->operationalDetails()->delete();

            /*
             * =========================================================
             * 4. CREATE NEW BASE DRINK DETAILS
             * =========================================================
             */
            foreach (
                $validated['products'] ?? []
                as $item
            ) {
                $distribution->productDetails()->create([
                    'base_drink_id' =>
                        $item['base_drink_id'],

                    'quantity_distributed' =>
                        $item['quantity_distributed'],

                    'quantity_returned' =>
                        $item['quantity_returned'],
                ]);
            }

            /*
             * =========================================================
             * 5. CREATE NEW OPERATIONAL DETAILS
             * =========================================================
             */
            foreach (
                $validated['operational_items'] ?? []
                as $item
            ) {
                $operationalItem = OperationalItem::findOrFail(
                    $item['operational_item_id']
                );

                $distributed =
                    $item['quantity_distributed'];

                $returned =
                    $item['quantity_returned'];

                $netQuantity =
                    $distributed - $returned;

                /*
                 * Check stock.
                 */
                if ($netQuantity > $operationalItem->stock) {
                    throw new \Exception(
                        "Insufficient stock for {$operationalItem->name}."
                    );
                }

                /*
                 * Create new detail.
                 */
                $distribution->operationalDetails()->create([
                    'operational_item_id' =>
                        $operationalItem->id,

                    'quantity_distributed' =>
                        $distributed,

                    'quantity_returned' =>
                        $returned,
                ]);

                /*
                 * Deduct only net quantity.
                 */
                if ($netQuantity > 0) {
                    $operationalItem->decrement(
                        'stock',
                        $netQuantity
                    );
                }
            }
        });

        return redirect()
            ->route('distributions.index')
            ->with(
                'success',
                'Distribution updated successfully.'
            );
    }

    /**
     * Remove the specified distribution.
     */
    public function destroy(
        Distribution $distribution
    ): RedirectResponse {
        $distribution->load([
            'operationalDetails.operationalItem',
        ]);

        DB::transaction(function () use ($distribution) {

            /*
             * Restore only the net quantity that was actually
             * taken out of stock.
             */
            foreach (
                $distribution->operationalDetails
                as $detail
            ) {
                $distributed =
                    $detail->quantity_distributed;

                $returned =
                    $detail->quantity_returned;

                $netQuantity =
                    $distributed - $returned;

                if ($netQuantity > 0) {
                    $detail->operationalItem->increment(
                        'stock',
                        $netQuantity
                    );
                }
            }

            /*
             * Delete distribution.
             *
             * Detail records should be deleted automatically
             * through cascadeOnDelete().
             */
            $distribution->delete();
        });

        return redirect()
            ->route('distributions.index')
            ->with(
                'success',
                'Distribution deleted successfully.'
            );
    }
}
