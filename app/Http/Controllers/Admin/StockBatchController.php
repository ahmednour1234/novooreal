<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\StockBatch;
use App\Models\Branch;

class StockBatchController extends Controller
{
    /**
     * Display a listing of the products with grouped totals.
     * Optionally filter by branch_id if provided.
     *
     * @param Request $request
     * @return View|Factory|Application
     */
    public function index(Request $request)
    {
        $branchId = $request->input('branch_id');

        $query = StockBatch::selectRaw('
                stock_batches.product_id, 
                products.name as product_name, 
                products.product_code as product_code, 
                SUM(stock_batches.quantity) as total_quantity, 
                SUM(stock_batches.quantity * stock_batches.price) as total_price
            ')
            ->join('products', 'stock_batches.product_id', '=', 'products.id');

        // If a branch_id filter is provided, add it to the query.
        if ($branchId) {
            $query->where('stock_batches.branch_id', $branchId);
        }

        $products = $query->groupBy('stock_batches.product_id', 'products.name', 'products.product_code')
            ->get();
            $branches=Branch::all();

        return view('admin-views.stock_batches.index', compact('products','branches'));
    }

    /**
     * Display the specified product's stock batches.
     * Optionally filter by branch_id if provided.
     *
     * @param Request $request
     * @param int $productId
     * @return View|Factory|Application
     */
  public function show(Request $request, $productId)
{
    $branchId = $request->input('branch_id');

    $batchesQuery = StockBatch::where('product_id', $productId);
    
    // If a branch_id filter is provided, add it to the query.
    if ($branchId) {
        $batchesQuery->where('branch_id', $branchId);
    }

    $batches = $batchesQuery->get();

    // Calculate the overall total cost (quantity * price for each batch).
    $totalCost = $batches->reduce(function ($carry, $batch) {
        return $carry + ($batch->quantity * $batch->price);
    }, 0);

    return response()->json([
        'success' => 1,
        'view' => view('admin-views.stock_batches.invoice', compact('batches', 'totalCost', 'productId', 'branchId'))->render(),
    ]);
}

}
