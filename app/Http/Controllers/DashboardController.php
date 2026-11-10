<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

use App\Models\IncomingGood;
use App\Models\OperationalExpense;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Distribution;
use App\Models\Employee;
use App\Models\RawMaterial;

class DashboardController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $role = $user->role?->nama_role;


        /*
        |--------------------------------------------------------------------------
        | OWNER DASHBOARD
        |--------------------------------------------------------------------------
        */

        if ($role === 'Owner') {

            /*
            |--------------------------------------------------------------------------
            | Current Period
            |--------------------------------------------------------------------------
            |
            | Dashboard menggunakan waktu Indonesia (WIB).
            |
            */

            $today = now('Asia/Jakarta');

            $startDate = $today->copy()->startOfMonth();
            $endDate = $today->copy()->endOfDay();


            /*
            |--------------------------------------------------------------------------
            | SALES / INCOME
            |--------------------------------------------------------------------------
            |
            | Sale:
            |
            | Sale
            |   ├── sale_source = Outlet
            |   └── sale_source = Employee
            |
            | Employee sales tetap terhubung ke Distribution,
            | tetapi untuk laporan income kita hanya membutuhkan
            | sale_source dan sale_details.
            |
            */

            $sales = Sale::with('details')
                ->whereDate(
                    'sale_date',
                    '>=',
                    $startDate->toDateString()
                )
                ->whereDate(
                    'sale_date',
                    '<=',
                    $today->toDateString()
                )
                ->latest('sale_date')
                ->get();


            
            /*
            |--------------------------------------------------------------------------
            | Outlet Income
            |--------------------------------------------------------------------------
            */

            $outletIncome = $sales
                ->where('sale_source', 'Outlet')
                ->sum(function ($sale) {
                    return $sale->details->sum('subtotal');
                });


            /*
            |--------------------------------------------------------------------------
            | Distribution Income
            |--------------------------------------------------------------------------
            */

            $distributionIncome = $sales
                ->filter(function ($sale) {
                    return !is_null($sale->distribution_id);
                })
                ->sum(function ($sale) {
                    return $sale->details->sum('subtotal');
                });


            /*
            |--------------------------------------------------------------------------
            | Total Income
            |--------------------------------------------------------------------------
            */

            $totalIncome = $sales->sum(function ($sale) {
                return $sale->details->sum('subtotal');
            });

            /*
            |--------------------------------------------------------------------------
            | Incoming Goods Expense
            |--------------------------------------------------------------------------
            */

            $incomingGoods = IncomingGood::whereDate(
                'received_at',
                '>=',
                $startDate->toDateString()
            )
                ->whereDate(
                    'received_at',
                    '<=',
                    $today->toDateString()
                )
                ->get();


            $incomingGoodsExpense = $incomingGoods->sum(
                function ($item) {

                    return $item->quantity *
                        $item->unit_cost;

                }
            );


            /*
            |--------------------------------------------------------------------------
            | Operational Expense
            |--------------------------------------------------------------------------
            */

            $operationalExpense = OperationalExpense::whereDate(
                'expense_date',
                '>=',
                $startDate->toDateString()
            )
                ->whereDate(
                    'expense_date',
                    '<=',
                    $today->toDateString()
                )
                ->sum('amount');


            /*
            |--------------------------------------------------------------------------
            | Total Expense
            |--------------------------------------------------------------------------
            */

            $totalExpense =
                $incomingGoodsExpense +
                $operationalExpense;


            /*
            |--------------------------------------------------------------------------
            | Profit
            |--------------------------------------------------------------------------
            */

            $profit =
                $totalIncome -
                $totalExpense;


            /*
            |--------------------------------------------------------------------------
            | Active Products
            |--------------------------------------------------------------------------
            */

            $activeProducts = Product::where(
                'status',
                'Active'
            )->count();


            /*
            |--------------------------------------------------------------------------
            | Chart Data
            |--------------------------------------------------------------------------
            */

            $chartLabels = [];
            $chartIncome = [];
            $chartExpense = [];

            $currentDate = $startDate->copy();


            while ($currentDate->lte($today)) {

                $dateString =
                    $currentDate->toDateString();


                /*
                |--------------------------------------------------------------------------
                | Daily Income
                |--------------------------------------------------------------------------
                */

                $dailyIncome = $sales
                    ->filter(function ($sale) use ($dateString) {

                        return $sale->sale_date
                            ->toDateString() === $dateString;

                    })
                    ->sum(function ($sale) {

                        return $sale->details
                            ->sum('subtotal');

                    });


                /*
                |--------------------------------------------------------------------------
                | Daily Incoming Goods
                |--------------------------------------------------------------------------
                */

                $dailyIncomingGoods = $incomingGoods
                    ->filter(function ($item) use ($dateString) {

                        return $item->received_at
                            ->toDateString() === $dateString;

                    })
                    ->sum(function ($item) {

                        return $item->quantity *
                            $item->unit_cost;

                    });


                /*
                |--------------------------------------------------------------------------
                | Daily Operational Expense
                |--------------------------------------------------------------------------
                */

                $dailyOperationalExpense =
                    OperationalExpense::whereDate(
                        'expense_date',
                        $dateString
                    )->sum('amount');


                /*
                |--------------------------------------------------------------------------
                | Daily Expense
                |--------------------------------------------------------------------------
                */

                $dailyExpense =
                    $dailyIncomingGoods +
                    $dailyOperationalExpense;


                /*
                |--------------------------------------------------------------------------
                | Chart
                |--------------------------------------------------------------------------
                */

                $chartLabels[] =
                    $currentDate->format('d M');

                $chartIncome[] =
                    $dailyIncome;

                $chartExpense[] =
                    $dailyExpense;


                $currentDate->addDay();
            }


            /*
            |--------------------------------------------------------------------------
            | Recent Sales
            |--------------------------------------------------------------------------
            |
            | Kita sekalian kirim data penjualan ke Dashboard Owner
            | supaya nanti bisa ditampilkan sebagai daftar transaksi.
            |
            */

            $recentSales = Sale::with([
                'details.product',
                'distribution.assignment.employee.user',
            ])
                ->latest('sale_date')
                ->latest()
                ->take(5)
                ->get();


            /*
            |--------------------------------------------------------------------------
            | Return Owner Dashboard
            |--------------------------------------------------------------------------
            */

            return view(
                'dashboard.owner',
                compact(
                    'startDate',
                    'endDate',
                    'outletIncome',
                    'distributionIncome',
                    'totalIncome',
                    'incomingGoodsExpense',
                    'operationalExpense',
                    'totalExpense',
                    'profit',
                    'activeProducts',
                    'chartLabels',
                    'chartIncome',
                    'chartExpense',
                    'recentSales'
                )
            );
        }


        /*
        |--------------------------------------------------------------------------
        | STAFF OPERASIONAL DASHBOARD
        |--------------------------------------------------------------------------
        */

        if ($role === 'Staff Operasional') {

            /*
            |--------------------------------------------------------------------------
            | Basic Statistics
            |--------------------------------------------------------------------------
            */

            $activeProducts = Product::where(
                'status',
                'Active'
            )->count();


            $activeRawMaterials = RawMaterial::where(
                'status',
                'Active'
            )->count();


            $lowStockMaterials = RawMaterial::where(
                'status',
                'Active'
            )
                ->whereColumn(
                    'current_stock',
                    '<=',
                    'minimum_stock'
                )
                ->count();


            $activeEmployees = Employee::where(
                'status',
                'Active'
            )->count();


            /*
            |--------------------------------------------------------------------------
            | Recent Distributions
            |--------------------------------------------------------------------------
            |
            | Distribution sekarang tidak memiliki employee_id.
            |
            | Relasi:
            |
            | Distribution
            |   ↓
            | Assignment
            |   ↓
            | Employee
            |   ↓
            | User
            |
            */

            $recentDistributions = Distribution::with([
                'assignment.employee.user',
                'productDetails.baseDrink',
                'operationalDetails.operationalItem',
            ])
                ->latest('distribution_date')
                ->latest()
                ->take(5)
                ->get();


            /*
            |--------------------------------------------------------------------------
            | Recent Sales
            |--------------------------------------------------------------------------
            |
            | Sale sekarang:
            |
            | Sale
            |   ↓
            | Distribution
            |   ↓
            | Assignment
            |   ↓
            | Employee
            |
            */

            $recentSales = Sale::with([
                'distribution.assignment.employee.user',
                'details.product',
            ])
                ->latest('sale_date')
                ->latest()
                ->take(5)
                ->get();


            /*
            |--------------------------------------------------------------------------
            | Recent Incoming Goods
            |--------------------------------------------------------------------------
            */

            $recentIncomingGoods = IncomingGood::with(
                'rawMaterial'
            )
                ->latest('received_at')
                ->latest()
                ->take(5)
                ->get();


            return view(
                'dashboard.staff',
                compact(
                    'activeProducts',
                    'activeRawMaterials',
                    'lowStockMaterials',
                    'activeEmployees',
                    'recentDistributions',
                    'recentSales',
                    'recentIncomingGoods'
                )
            );
        }


        /*
        |--------------------------------------------------------------------------
        | KARYAWAN DASHBOARD
        |--------------------------------------------------------------------------
        */

        if ($role === 'Karyawan') {

            /*
            |--------------------------------------------------------------------------
            | Get Employee
            |--------------------------------------------------------------------------
            */

            $employee = $user->employee;

            if (!$employee) {
                abort(403, 'Employee profile not found.');
            }


            /*
            |--------------------------------------------------------------------------
            | Today's Distribution
            |--------------------------------------------------------------------------
            */

            $today = now()->toDateString();

            $todayDistribution = Distribution::with([
                'assignment.employee.user',
                'assignment.cart',
                'assignment.region',
                'productDetails.baseDrink',
                'operationalDetails.operationalItem',
            ])
                ->whereHas('assignment', function ($query) use ($employee) {
                    $query->where('employee_id', $employee->id);
                })
                ->whereDate('distribution_date', $today)
                ->latest('distribution_date')
                ->latest()
                ->first();


            /*
            |--------------------------------------------------------------------------
            | Today's Sales
            |--------------------------------------------------------------------------
            |
            | Employee sales are connected to the employee through:
            |
            | Sale
            |   ↓
            | Distribution
            |   ↓
            | Assignment
            |   ↓
            | Employee
            |
            */

            $todaySales = Sale::with([
                'details.product',
                'distribution.assignment.employee.user',
            ])
                ->whereHas('distribution.assignment', function ($query) use ($employee) {
                    $query->where('employee_id', $employee->id);
                })
                ->whereDate('sale_date', $today)
                ->latest()
                ->get();


            /*
            |--------------------------------------------------------------------------
            | Today's Sales Total
            |--------------------------------------------------------------------------
            */

            $todaySalesTotal = $todaySales->sum(function ($sale) {
                return $sale->details->sum('subtotal');
            });


            /*
            |--------------------------------------------------------------------------
            | Recent Distributions
            |--------------------------------------------------------------------------
            */

            $recentDistributions = Distribution::with([
                'assignment.employee.user',
                'assignment.cart',
                'assignment.region',
                'productDetails.baseDrink',
                'operationalDetails.operationalItem',
            ])
                ->whereHas('assignment', function ($query) use ($employee) {
                    $query->where('employee_id', $employee->id);
                })
                ->latest('distribution_date')
                ->latest()
                ->take(5)
                ->get();


            /*
            |--------------------------------------------------------------------------
            | Recent Sales
            |--------------------------------------------------------------------------
            */

            $recentSales = Sale::with([
                'details.product',
                'distribution.assignment.employee.user',
            ])
                ->where('sale_source', 'Employee')
                ->whereHas('distribution.assignment', function ($query) use ($employee) {
                    $query->where('employee_id', $employee->id);
                })
                ->latest('sale_date')
                ->latest()
                ->take(5)
                ->get();


            /*
            |--------------------------------------------------------------------------
            | Return Employee Dashboard
            |--------------------------------------------------------------------------
            */

            return view('dashboard.karyawan', compact(
                'employee',
                'todayDistribution',
                'todaySales',
                'todaySalesTotal',
                'recentDistributions',
                'recentSales'
            ));
        }


        /*
        |--------------------------------------------------------------------------
        | Unknown Role
        |--------------------------------------------------------------------------
        */

        abort(
            403,
            'Role pengguna tidak dikenali.'
        );
    }
}
