<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\BaseDrink;
use App\Models\Distribution;
use App\Models\DistributionOperationalDetail;
use App\Models\DistributionProductDetail;
use App\Models\OperationalItem;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class KopiKeunAdditionalDistributionsSeeder extends Seeder
{
    private const MARKER = 'Seeder KopiKeun Additional Distribution 2026';

    public function run(): void
    {
        DB::transaction(function (): void {
            mt_srand(202608);

            // Mengambil assignment yang sudah tersedia.
            $assignments = Assignment::with('employee.user')
                ->whereHas('employee.user', function ($query) {
                    $query->whereIn('name', ['Ajis Muslim', 'Hafidz']);
                })
                ->get()
                ->keyBy(fn ($assignment) => $assignment->employee->user->name);

            foreach (['Ajis Muslim', 'Hafidz'] as $employeeName) {
                if (!$assignments->has($employeeName)) {
                    throw new RuntimeException(
                        "Assignment untuk {$employeeName} tidak ditemukan."
                    );
                }
            }

            // Mengambil produk yang sudah tersedia.
            $products = Product::whereIn('name', [
                'Kopi Susu Butterscotch',
                'Kopi Susu Hazelnut',
                'Kopi Susu Karamel',
                'Kopi Susu Gula Aren',
                'Kopi Susu Latte',
                'Americano',
            ])->get()->keyBy('name');

            // Mengambil kategori minuman dasar.
            $baseDrinks = BaseDrink::whereIn('name', [
                'Americano',
                'Kopi Susu',
                'Kopi Susu Gula Aren',
            ])->get()->keyBy('name');

            $this->validateMasterData($products, $baseDrinks);

            // Menambahkan cup dan tutup cup sebagai barang operasional.
            $operationalItems = $this->getOperationalItems();

            // Hanya menghapus data yang dibuat oleh seeder tambahan ini.
            $this->removePreviousGeneratedData();

            $this->seedDistributions(
                $assignments,
                $products,
                $baseDrinks,
                $operationalItems
            );
        });
    }

    private function validateMasterData($products, $baseDrinks): void
    {
        $requiredProducts = [
            'Kopi Susu Butterscotch',
            'Kopi Susu Hazelnut',
            'Kopi Susu Karamel',
            'Kopi Susu Gula Aren',
            'Kopi Susu Latte',
            'Americano',
        ];

        $requiredBaseDrinks = [
            'Americano',
            'Kopi Susu',
            'Kopi Susu Gula Aren',
        ];

        foreach ($requiredProducts as $name) {
            if (!$products->has($name)) {
                throw new RuntimeException(
                    "Produk {$name} tidak ditemukan. Jalankan seeder master terlebih dahulu."
                );
            }
        }

        foreach ($requiredBaseDrinks as $name) {
            if (!$baseDrinks->has($name)) {
                throw new RuntimeException(
                    "Base drink {$name} tidak ditemukan."
                );
            }
        }
    }

    private function getOperationalItems()
    {
        // firstOrCreate tidak mengubah stok barang yang sudah ada.
        $definitions = [
            'Cup plastik' => [
                'unit' => 'pcs',
                'stock' => 1000,
                'minimum_stock' => 200,
            ],
            'Tutup cup' => [
                'unit' => 'pcs',
                'stock' => 1000,
                'minimum_stock' => 200,
            ],
        ];

        foreach ($definitions as $name => $data) {
            OperationalItem::firstOrCreate(
                ['name' => $name],
                $data + ['status' => 'active']
            );
        }

        return OperationalItem::whereIn('name', [
            'Cup plastik',
            'Tutup cup',
            'Cup holder',
            'Sedotan',
            'Kantong plastik',
            'Tisu',
            'Lap kecil',
        ])->get()->keyBy('name');
    }

    private function removePreviousGeneratedData(): void
    {
        $distributionIds = Distribution::where(
            'notes',
            'like',
            self::MARKER . '%'
        )->pluck('id');

        if ($distributionIds->isEmpty()) {
            return;
        }

        // Hapus detail penjualan terlebih dahulu.
        $saleIds = Sale::whereIn(
            'distribution_id',
            $distributionIds
        )->pluck('id');

        if ($saleIds->isNotEmpty()) {
            SaleDetail::whereIn('sale_id', $saleIds)->delete();
            Sale::whereIn('id', $saleIds)->delete();
        }

        // Hapus detail distribusi.
        DistributionOperationalDetail::whereIn(
            'distribution_id',
            $distributionIds
        )->delete();

        DistributionProductDetail::whereIn(
            'distribution_id',
            $distributionIds
        )->delete();

        Distribution::whereIn('id', $distributionIds)->delete();
    }

    private function seedDistributions(
        $assignments,
        $products,
        $baseDrinks,
        $operationalItems
    ): void {
        /*
         * Seluruh nominal menggunakan satuan ribuan rupiah.
         * Tanggal yang tidak memiliki nominal tidak dibuatkan transaksi.
         */
        $dailyTotals = [
            // AGUSTUS 2026
            '2026-08-01' => 107,
            '2026-08-02' => 78,
            '2026-08-03' => 31,
            '2026-08-04' => 74,
            '2026-08-05' => 50,
            '2026-08-06' => 50,
            '2026-08-07' => 50,
            '2026-08-08' => 50,
            '2026-08-09' => 145,
            '2026-08-10' => 48,
            '2026-08-11' => 66,
            '2026-08-14' => 157,
            '2026-08-15' => 84,
            '2026-08-16' => 84,
            '2026-08-18' => 80,
            '2026-08-19' => 89,
            '2026-08-20' => 94,
            '2026-08-21' => 104,
            '2026-08-22' => 63,
            '2026-08-23' => 88,
            '2026-08-24' => 95,
            '2026-08-25' => 91,
            '2026-08-26' => 15,
            '2026-08-27' => 22,
            '2026-08-28' => 46,
            '2026-08-29' => 58,
            '2026-08-30' => 273,
            '2026-08-31' => 90,

            // SEPTEMBER 2026
            '2026-09-01' => 158,
            '2026-09-05' => 136,
            '2026-09-06' => 183,
            '2026-09-07' => 43,
            '2026-09-08' => 50,
            '2026-09-09' => 25,
            '2026-09-10' => 50,
            '2026-09-11' => 50,
            '2026-09-12' => 54,
            '2026-09-13' => 50,
            '2026-09-14' => 56,
            '2026-09-15' => 50,
            '2026-09-16' => 75,
            '2026-09-17' => 31,
            '2026-09-20' => 154,
            '2026-09-22' => 94,
            '2026-09-24' => 230,
            '2026-09-28' => 27,
            '2026-09-29' => 42,

            // OKTOBER 2026
            '2026-10-01' => 59,
            '2026-10-02' => 79,
            '2026-10-03' => 67,
            '2026-10-04' => 237,
            '2026-10-06' => 71,
            '2026-10-07' => 140,
            '2026-10-10' => 67,
            '2026-10-11' => 104,
            '2026-10-12' => 97,
            '2026-10-15' => 354,
            '2026-10-20' => 80,
            '2026-10-21' => 53,
            '2026-10-24' => 118,
            '2026-10-29' => 79,

            // NOVEMBER 2026
            '2026-11-01' => 133,
            '2026-11-02' => 77,
            '2026-11-03' => 49,
            '2026-11-04' => 70,
            '2026-11-06' => 99,
            '2026-11-08' => 80,
        ];

        // Produk dikelompokkan berdasarkan harga dalam ribuan.
        $productByPrice = [
            9 => [
                $products['Kopi Susu Butterscotch']->id,
                $products['Kopi Susu Hazelnut']->id,
                $products['Kopi Susu Karamel']->id,
            ],
            8 => [
                $products['Kopi Susu Gula Aren']->id,
                $products['Kopi Susu Latte']->id,
            ],
            5 => [
                $products['Americano']->id,
            ],
        ];

        $baseForProduct = [
            $products['Kopi Susu Butterscotch']->id =>
                $baseDrinks['Kopi Susu']->id,

            $products['Kopi Susu Hazelnut']->id =>
                $baseDrinks['Kopi Susu']->id,

            $products['Kopi Susu Karamel']->id =>
                $baseDrinks['Kopi Susu']->id,

            $products['Kopi Susu Gula Aren']->id =>
                $baseDrinks['Kopi Susu Gula Aren']->id,

            $products['Kopi Susu Latte']->id =>
                $baseDrinks['Kopi Susu']->id,

            $products['Americano']->id =>
                $baseDrinks['Americano']->id,
        ];

        $recordIndex = 0;

        foreach ($dailyTotals as $date => $totalThousands) {
            // Membentuk kombinasi produk yang tepat sesuai target harian.
            $productMix = $this->makeExactProductMix($totalThousands);

            // Dua distribusi hanya untuk penjualan di atas Rp200.000.
            $distributionCount = $totalThousands > 200 ? 2 : 1;

            $groups = $distributionCount === 2
                ? $this->splitProductMix($productMix)
                : [$productMix];

            foreach ($groups as $groupIndex => $group) {
                $employeeName = $distributionCount === 2
                    ? ['Ajis Muslim', 'Hafidz'][$groupIndex]
                    : ['Ajis Muslim', 'Hafidz'][$recordIndex % 2];

                $assignment = $assignments[$employeeName];

                $this->createDistribution(
                    $date,
                    $employeeName,
                    $assignment,
                    $group,
                    $productByPrice,
                    $baseForProduct,
                    $baseDrinks,
                    $operationalItems
                );
            }

            $recordIndex++;
        }
    }

    private function createDistribution(
        string $date,
        string $employeeName,
        Assignment $assignment,
        array $group,
        array $productByPrice,
        array $baseForProduct,
        $baseDrinks,
        $operationalItems
    ): void {
        $distribution = Distribution::create([
            'assignment_id' => $assignment->id,
            'distribution_date' => $date,
            'notes' => self::MARKER . " - distribusi {$employeeName} untuk {$date}",
        ]);

        // Menghitung jumlah produk berdasarkan kombinasi harga.
        $productCounts = [];

        foreach ($group as $price) {
            $options = $productByPrice[$price];

            $productId = $options[
                mt_rand(0, count($options) - 1)
            ];

            $productCounts[$productId] =
                ($productCounts[$productId] ?? 0) + 1;
        }

        $sale = Sale::create([
            'sale_source' => 'Distribusi',
            'distribution_id' => $distribution->id,
            'sale_date' => $date,
            'notes' => self::MARKER . " - penjualan {$employeeName} {$date}",
        ]);

        $baseQuantities = [];
        $totalDrinksSold = 0;

        foreach ($productCounts as $productId => $quantity) {
            $product = Product::findOrFail($productId);

            SaleDetail::create([
                'sale_id' => $sale->id,
                'product_id' => $productId,
                'quantity' => $quantity,
                'subtotal' => $quantity * (int) $product->price,
            ]);

            $baseId = $baseForProduct[$productId];

            $baseQuantities[$baseId] =
                ($baseQuantities[$baseId] ?? 0) + $quantity;

            $totalDrinksSold += $quantity;
        }

        // Detail distribusi minuman berdasarkan base drink.
        foreach ($baseDrinks as $baseDrink) {
            $sold = $baseQuantities[$baseDrink->id] ?? 0;

            if ($sold > 0) {
                $distributed = max(
                    $sold + 2,
                    (int) ceil($sold * 1.2)
                );

                DistributionProductDetail::create([
                    'distribution_id' => $distribution->id,
                    'base_drink_id' => $baseDrink->id,
                    'quantity_distributed' => $distributed,
                    'quantity_returned' => 0,
                ]);
            }
        }

        $this->createOperationalDetails(
            $distribution,
            $totalDrinksSold,
            $operationalItems
        );
    }

    private function createOperationalDetails(
        Distribution $distribution,
        int $totalDrinksSold,
        $operationalItems
    ): void {
        /*
         * Cup dan tutup cup:
         * 10 minuman terjual = 10, 11, atau 12 unit terpakai.
         *
         * Jumlah pemakaian cup dan tutup dibuat sama.
         */
        $cupConsumption = $totalDrinksSold + mt_rand(0, 2);

        $cupNames = ['Cup plastik', 'Tutup cup'];

        foreach ($cupNames as $name) {
            $item = $operationalItems->get($name);

            if (!$item) {
                throw new RuntimeException(
                    "Barang operasional {$name} tidak ditemukan."
                );
            }

            // Sisa barang yang tidak terpakai dikembalikan.
            $returned = mt_rand(1, 4);
            $distributed = $cupConsumption + $returned;

            DistributionOperationalDetail::create([
                'distribution_id' => $distribution->id,
                'operational_item_id' => $item->id,
                'quantity_distributed' => $distributed,
                'quantity_returned' => $returned,
            ]);
        }

        // Barang operasional lainnya menggunakan jumlah dummy.
        $otherItems = [
            'Cup holder',
            'Sedotan',
            'Kantong plastik',
            'Tisu',
            'Lap kecil',
        ];

        foreach ($otherItems as $name) {
            $item = $operationalItems->get($name);

            if (!$item) {
                continue;
            }

            $distributed = mt_rand(1, 5);
            $returned = mt_rand(0, $distributed);

            DistributionOperationalDetail::create([
                'distribution_id' => $distribution->id,
                'operational_item_id' => $item->id,
                'quantity_distributed' => $distributed,
                'quantity_returned' => $returned,
            ]);
        }
    }

    /**
     * Menghasilkan kombinasi produk yang total harganya tepat
     * sesuai target penjualan harian.
     *
     * Denominasi harga: Rp9.000, Rp8.000, dan Rp5.000.
     */
    private function makeExactProductMix(int $target): array
    {
        $memo = [];

        $solve = function (int $remaining) use (&$solve, &$memo): ?array {
            if ($remaining === 0) {
                return [];
            }

            if ($remaining < 0) {
                return null;
            }

            if (array_key_exists($remaining, $memo)) {
                return $memo[$remaining];
            }

            $denominations = [9, 8, 5];
            shuffle($denominations);

            foreach ($denominations as $value) {
                if ($remaining >= $value) {
                    $tail = $solve($remaining - $value);

                    if ($tail !== null) {
                        return $memo[$remaining] =
                            array_merge([$value], $tail);
                    }
                }
            }

            return $memo[$remaining] = null;
        };

        $mix = $solve($target);

        if ($mix === null) {
            throw new RuntimeException(
                "Nominal {$target} ribu tidak dapat dibentuk dari harga produk."
            );
        }

        return $mix;
    }

    /**
     * Membagi kombinasi produk menjadi dua kelompok distribusi.
     */
    private function splitProductMix(array $coins): array
    {
        shuffle($coins);

        $cut = mt_rand(1, count($coins) - 1);

        return [
            array_slice($coins, 0, $cut),
            array_slice($coins, $cut),
        ];
    }
}
