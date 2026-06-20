<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Vendor;
use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\CashPurchase;
use App\Models\DebitNote;
use App\Models\StockLedger;
use App\Models\RawMaterialRequest;
use App\Models\AccountingTransaction;
use App\Models\RawMaterial;
use App\Models\CompanySite;
use App\Models\CompanyUnit;
use App\Models\Department;
use App\Models\Section;
use App\Models\Account;
use App\Models\WorkPoint;
class PurchaseManagementController extends Controller
{

    public function dashboard()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $stats = [
            'suppliers' => Vendor::count(),
            'requisitions' => RawMaterialRequest::count(),
            'purchase_orders' => PurchaseOrder::count(),
            'goods_receipts' => GoodsReceipt::count(),
            'debit_notes' => DebitNote::count(),
            'monthly_purchases' => PurchaseOrder::whereBetween(
                'po_date',
                [$startOfMonth, $endOfMonth]
            )->sum('total_amount'),
            'draft_orders' => PurchaseOrder::where('status','Draft')->count(),
            'approved_orders' => PurchaseOrder::where('status','Approved')->count(),
            'ordered_orders' => PurchaseOrder::where('status','Ordered')->count(),

            'pending_orders' => PurchaseOrder::where('receive_status','pending')->count(),
            'partial_orders' => PurchaseOrder::where('receive_status','partial')->count(),

            'received_orders' => PurchaseOrder::where('receive_status','received')->count(),
            'paid_orders' => PurchaseOrder::where('payment_status','paid')->count(),

            'unpaid_orders' => PurchaseOrder::where('payment_status','unpaid')->count(),
            'partial_paid_orders' => PurchaseOrder::where('payment_status','partial')->count(),

        ];

        $analytics = [

            'orders' => PurchaseOrder::count(),

            'goods_receipts' => GoodsReceipt::count(),

            'debit_notes' => DebitNote::count(),

            'suppliers' => Vendor::count(),

            'requisitions' => RawMaterialRequest::count(),

            'monthly_purchases' => $stats['monthly_purchases'],

            'paid_orders' => $stats['paid_orders'],

            'unpaid_orders' => $stats['unpaid_orders'],

        ];

        $flow = [

            'suppliers' => Vendor::count(),

            'requisitions' => RawMaterialRequest::count(),

            'orders' => PurchaseOrder::count(),

            'receipts' => GoodsReceipt::count(),

            'debits' => DebitNote::count(),

            'draft_orders' => $stats['draft_orders'],

            'approved_orders' => $stats['approved_orders'],

            'received_orders' => $stats['received_orders'],

            'paid_orders' => $stats['paid_orders'],

            'unpaid_orders' => $stats['unpaid_orders'],

        ];

        $recentOrders = PurchaseOrder::latest()->take(10)->get();

        $recentGRN = GoodsReceipt::latest()->take(10)->get();
        return view(
            'admin.purchase.dashboard',
            compact(
                'stats',
                'analytics',
                'flow',
                'recentOrders',
                'recentGRN'
            )
        );
    }
    public function debitNotes()
    {

        $debitNotes = DebitNote::latest()->get();

        return view(
            'admin.purchase.debits.index',
            compact('debitNotes')
        );
    }
}