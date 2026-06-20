<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\Delivery;
use App\Models\Payment;


class DashboardController extends Controller
{
    public function index(Request $request)
    {
        //  WORK POINT (ERP STANDARD)
        $workPointId = session('work_point_id');

        //  PRODUCTS / INVENTORY
     
        $products = Product::when($workPointId, function ($q) use ($workPointId) {
                $q->where('work_point_id', $workPointId);
            })
            ->get();

        //  LOW STOCK ALERT
        $lowStockProducts = Product::when($workPointId, function ($q) use ($workPointId) {
                $q->where('work_point_id', $workPointId);
            })
            ->whereColumn('total_qty', '<=', 'reorder_level')
            ->orderBy('total_qty', 'asc')
            ->get();


        //  DELIVERIES
  
        $deliveries = Delivery::with(['order.customer'])
            ->when($workPointId, function ($q) use ($workPointId) {
                $q->where('work_point_id', $workPointId);
            })
            ->latest()
            ->take(10)
            ->get();

        //  FINANCIALS
       
        $totalSales = Invoice::when($workPointId, function ($q) use ($workPointId) {
                $q->where('work_point_id', $workPointId);
            })
            ->sum('total');

        $totalPayments = Payment::when($workPointId, function ($q) use ($workPointId) {
                $q->where('work_point_id', $workPointId);
            })
            ->sum('amount');

        $outstanding = $totalSales - $totalPayments;

        //  DELIVERY STATUS
       
        $pendingDeliveries = Delivery::where('delivery_status', 'pending')
            ->when($workPointId, fn($q) => $q->where('work_point_id', $workPointId))
            ->count();

        $inTransitDeliveries = Delivery::where('delivery_status', 'in_transit')
            ->when($workPointId, fn($q) => $q->where('work_point_id', $workPointId))
            ->count();

        $completedDeliveries = Delivery::where('delivery_status', 'delivered')
            ->when($workPointId, fn($q) => $q->where('work_point_id', $workPointId))
            ->count();

        //  COMPLIANCE
       
        $nonCompliant = Delivery::where('storage_type', 'explosive')
            ->where(function ($q) {
                $q->whereNull('permit_no')
                  ->orWhereNull('safety_officer');
            })
            ->when($workPointId, fn($q) => $q->where('work_point_id', $workPointId))
            ->count();

       
        //  STATS
      
        $stats = [
            'stock' => $products->sum('total_qty'),
            'stock_value' => $products->sum('total_value'),

            'sales' => $totalSales,
            'payments' => $totalPayments,
            'outstanding' => $outstanding,

            'pending_delivery' => $pendingDeliveries,
            'in_transit' => $inTransitDeliveries,
            'delivered' => $completedDeliveries,

            'low_stock' => $lowStockProducts->count(),
            'non_compliant' => $nonCompliant,
        ];

  
        // RETURN VIEW (UPDATED PATH)
       
        return view('admin.store.stock.dashboard', compact(
            'stats',
            'lowStockProducts',
            'deliveries',
            'products'
        ));
    }
}