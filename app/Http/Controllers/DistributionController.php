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
                'required',
                'array',
                'min:1',
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

            'products.*.quantity_stored' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'products.*.quantity_wasted' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'products.*.quantity_consumed' => [
                'nullable',
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
                'distinct',
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
        * Validate base drink quantities.
        */
        foreach ($validated['products'] as $index => $item) {
            $distributed = (int) $item['quantity_distributed'];
            $returned = (int) $item['quantity_returned'];

            $stored = (int) ($item['quantity_stored'] ?? 0);
            $wasted = (int) ($item['quantity_wasted'] ?? 0);
            $consumed = (int) ($item['quantity_consumed'] ?? 0);

            if ($returned > $distributed) {
                return back()
                    ->withInput()
                    ->withErrors([
                        "products.$index.quantity_returned" =>
                            'Returned quantity cannot be greater than distributed quantity.',
                    ]);
            }

            if ($returned !== ($stored + $wasted + $consumed)) {
                return back()
                    ->withInput()
                    ->withErrors([
                        "products.$index.quantity_returned" =>
                            'Returned quantity must equal the sum of stored, wasted, and consumed quantities.',
                    ]);
            }
        }

        /*
        * Validate operational item quantities.
        */
        foreach ($validated['operational_items'] ?? [] as $index => $item) {
            if (
                (int) $item['quantity_returned']
                > (int) $item['quantity_distributed']
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
            * 1. Create distribution header.
            */
            $distribution = Distribution::create([
                'assignment_id' => $validated['assignment_id'],
                'distribution_date' => $validated['distribution_date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            /*
            * 2. Create base drink details.
            * Base drinks do not affect operational stock.
            */
            foreach ($validated['products'] as $item) {
                $distribution->productDetails()->create([
                    'base_drink_id' => $item['base_drink_id'],
                    'quantity_distributed' => $item['quantity_distributed'],
                    'quantity_returned' => $item['quantity_returned'],
                    'quantity_stored' => $item['quantity_stored'] ?? 0,
                    'quantity_wasted' => $item['quantity_wasted'] ?? 0,
                    'quantity_consumed' => $item['quantity_consumed'] ?? 0,
                ]);
            }

            /*
            * 3. Calculate total base drinks.
            */
            $totalBaseDrinks = $this->calculateTotalBaseDrinks(
                $validated['products']
            );

            /*
            * 4. Create operational details.
            * Cup and lid quantities are automatic.
            */
            $this->createOperationalDetails(
                $distribution,
                $validated['operational_items'] ?? [],
                $totalBaseDrinks
            );
        });

        return redirect()
            ->route('distributions.index')
            ->with('success', 'Distribution created successfully.');
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
                'required',
                'array',
                'min:1',
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

            'products.*.quantity_stored' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'products.*.quantity_wasted' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'products.*.quantity_consumed' => [
                'nullable',
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
                'distinct',
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
        * Validate base drink quantities.
        */
        foreach ($validated['products'] as $index => $item) {
            $distributed = (int) $item['quantity_distributed'];
            $returned = (int) $item['quantity_returned'];

            $stored = (int) ($item['quantity_stored'] ?? 0);
            $wasted = (int) ($item['quantity_wasted'] ?? 0);
            $consumed = (int) ($item['quantity_consumed'] ?? 0);

            if ($returned > $distributed) {
                return back()
                    ->withInput()
                    ->withErrors([
                        "products.$index.quantity_returned" =>
                            'Returned quantity cannot be greater than distributed quantity.',
                    ]);
            }

            if ($returned !== ($stored + $wasted + $consumed)) {
                return back()
                    ->withInput()
                    ->withErrors([
                        "products.$index.quantity_returned" =>
                            'Returned quantity must equal the sum of stored, wasted, and consumed quantities.',
                    ]);
            }
        }

        /*
        * Validate operational item quantities.
        */
        foreach ($validated['operational_items'] ?? [] as $index => $item) {
            if (
                (int) $item['quantity_returned']
                > (int) $item['quantity_distributed']
            ) {
                return back()
                    ->withInput()
                    ->withErrors([
                        "operational_items.$index.quantity_returned" =>
                            'Returned quantity cannot be greater than distributed quantity.',
                    ]);
            }
        }

        DB::transaction(function () use ($validated, $distribution) {

            /*
            * 1. Restore old operational stock.
            */
            $distribution->load('operationalDetails');

            foreach ($distribution->operationalDetails as $detail) {
                $netQuantity =
                    (int) $detail->quantity_distributed
                    - (int) $detail->quantity_returned;

                if ($netQuantity > 0) {
                    $operationalItem = OperationalItem::whereKey(
                        $detail->operational_item_id
                    )
                        ->lockForUpdate()
                        ->firstOrFail();

                    $operationalItem->increment('stock', $netQuantity);
                }
            }

            /*
            * 2. Update distribution header.
            */
            $distribution->update([
                'assignment_id' => $validated['assignment_id'],
                'distribution_date' => $validated['distribution_date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            /*
            * 3. Delete old details.
            */
            $distribution->productDetails()->delete();
            $distribution->operationalDetails()->delete();

            /*
            * 4. Create new base drink details.
            */
            foreach ($validated['products'] as $item) {
                $distribution->productDetails()->create([
                    'base_drink_id' => $item['base_drink_id'],
                    'quantity_distributed' => $item['quantity_distributed'],
                    'quantity_returned' => $item['quantity_returned'],
                    'quantity_stored' => $item['quantity_stored'] ?? 0,
                    'quantity_wasted' => $item['quantity_wasted'] ?? 0,
                    'quantity_consumed' => $item['quantity_consumed'] ?? 0,
                ]);
            }

            /*
            * 5. Calculate new total base drinks.
            */
            $totalBaseDrinks = $this->calculateTotalBaseDrinks(
                $validated['products']
            );

            /*
            * 6. Create new operational details.
            */
            $this->createOperationalDetails(
                $distribution,
                $validated['operational_items'] ?? [],
                $totalBaseDrinks
            );
        });

        return redirect()
            ->route('distributions.index')
            ->with('success', 'Distribution updated successfully.');
    }


    /**
     * Remove the specified distribution.
     */
    
    public function destroy(
        Distribution $distribution
    ): RedirectResponse {
        DB::transaction(function () use ($distribution) {

            /*
            * 1. Restore operational stock.
            */
            $distribution->load('operationalDetails');

            foreach ($distribution->operationalDetails as $detail) {
                $netQuantity =
                    (int) $detail->quantity_distributed
                    - (int) $detail->quantity_returned;

                if ($netQuantity > 0) {
                    $operationalItem = OperationalItem::whereKey(
                        $detail->operational_item_id
                    )
                        ->lockForUpdate()
                        ->firstOrFail();

                    $operationalItem->increment('stock', $netQuantity);
                }
            }

            /*
            * 2. Delete distribution.
            * Related details should be deleted through
            * cascadeOnDelete() in the database.
            */
            $distribution->delete();
        });

        return redirect()
            ->route('distributions.index')
            ->with('success', 'Distribution deleted successfully.');
    }


    /**
     * Get automatic operational items.
     */
    private function getAutomaticOperationalItems()
    {
        return OperationalItem::whereIn('name', [
            'Cup plastik',
            'Tutup cup',
        ])->get()->keyBy('name');
    }

    /**
     * Calculate total distributed base drinks.
     */
    private function calculateTotalBaseDrinks(array $products): int
    {
        return collect($products)->sum(
            fn ($item) => (int) $item['quantity_distributed']
        );
    }

    /**
     * Create operational distribution details.
     *
     * Cup and lid quantities are calculated automatically
     * from the total distributed base drinks.
     */
    private function createOperationalDetails(
        Distribution $distribution,
        array $items,
        int $totalBaseDrinks
    ): void {
        $automaticItems = $this->getAutomaticOperationalItems();

        if (
            !$automaticItems->has('Cup plastik') ||
            !$automaticItems->has('Tutup cup')
        ) {
            throw new \Exception(
                'Cup plastik or Tutup cup operational item was not found.'
            );
        }

        $submittedItems = collect($items)->keyBy('operational_item_id');

        $automaticIds = $automaticItems->pluck('id')->all();

        /*
        * 1. Create automatic cup and lid details.
        */
        foreach ($automaticItems as $name => $operationalItem) {
            $returned = (int) data_get(
                $submittedItems->get($operationalItem->id),
                'quantity_returned',
                0
            );

            if ($returned > $totalBaseDrinks) {
                throw new \Exception(
                    "Returned quantity for {$name} cannot exceed distributed quantity."
                );
            }

            $operationalItem = OperationalItem::whereKey($operationalItem->id)
                ->lockForUpdate()
                ->firstOrFail();

            $netQuantity = $totalBaseDrinks - $returned;

            if ($netQuantity > $operationalItem->stock) {
                throw new \Exception(
                    "Insufficient stock for {$name}."
                );
            }

            $distribution->operationalDetails()->create([
                'operational_item_id' => $operationalItem->id,
                'quantity_distributed' => $totalBaseDrinks,
                'quantity_returned' => $returned,
            ]);

            if ($netQuantity > 0) {
                $operationalItem->decrement('stock', $netQuantity);
            }
        }

        /*
        * 2. Process other operational items manually.
        */
        foreach ($items as $item) {
            $operationalItemId = (int) $item['operational_item_id'];

            // Skip cup and lid because they are handled automatically.
            if (in_array($operationalItemId, $automaticIds)) {
                continue;
            }

            $operationalItem = OperationalItem::whereKey($operationalItemId)
                ->lockForUpdate()
                ->firstOrFail();

            $distributed = (int) $item['quantity_distributed'];
            $returned = (int) $item['quantity_returned'];

            if ($returned > $distributed) {
                throw new \Exception(
                    "Returned quantity cannot exceed distributed quantity for {$operationalItem->name}."
                );
            }

            $netQuantity = $distributed - $returned;

            if ($netQuantity > $operationalItem->stock) {
                throw new \Exception(
                    "Insufficient stock for {$operationalItem->name}."
                );
            }

            $distribution->operationalDetails()->create([
                'operational_item_id' => $operationalItem->id,
                'quantity_distributed' => $distributed,
                'quantity_returned' => $returned,
            ]);

            if ($netQuantity > 0) {
                $operationalItem->decrement('stock', $netQuantity);
            }
        }
    }
}
