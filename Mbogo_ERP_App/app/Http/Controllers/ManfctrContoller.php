<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\CompanySite;
use App\Models\WorkPoint;
use App\Models\Company_unit;
use App\Models\Department;
use App\Models\User;
use App\Models\RawMaterial;
use App\Models\RawPrice;
use App\Models\RawMaterialRequest;
use App\Models\RawMaterialIssue;
use App\Models\ManufacturingReceipt;
use App\Models\ManufacturingMaterialStock;
use App\Models\ManufacturingStockMovement;
use App\Models\ManufacturingMaterialConsumption;
use App\Models\Product;
use App\Models\PrdPrice;
use App\Models\PackedPrd;
use App\Models\PrdStock;
use App\Models\PrdOrder;
use App\Models\IssPrd;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Image;
use App;
use Carbon\Carbon;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Symfony\Component\Console\Input\Input;
use Illuminate\Support\Facades\Validator;
class ManfctrContoller extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // parent::__construct();
    }

    protected function isSuperRole()
    {
        return in_array(optional(auth()->user())->role, ['Admin', 'CEO', 'Admin-Developer','Managing Director (MD)'], true);
    }

public function manufacturing()
{
    $user = auth()->user();
    $isSuper = $this->isSuperRole();
    // =========================
    // RAW MATERIAL MASTER
    // =========================
    $qRaw = RawMaterial::query()
        ->where('company_id', $user->company_id)
        ->where('status', '!=', 'Deleted');

    if (!$isSuper) {
        $qRaw->where('work_point_id', $user->work_point_id);
    }

    $rawMaterials = $qRaw->orderBy('material_name')->get();

    // =========================
    // RAW MATERIAL REQUESTS
    // =========================
    $qRequests = RawMaterialRequest::query()
        ->where('company_id', $user->company_id);

    if (!$isSuper) {
        $qRequests->where('work_point_id', $user->work_point_id);
    }

    $requestedRawTotal = (float) $qRequests->sum('requested_qty');
    $issuedAgainstRequestTotal = (float) $qRequests->sum('issued_qty');
    $remainingRequestTotal = (float) $qRequests->sum('remaining_qty');

    // =========================
    // MANUFACTURING RECEIPTS
    // =========================
    $qReceipts = ManufacturingReceipt::query()
        ->where('company_id', $user->company_id);

    if (!$isSuper) {
        $qReceipts->where('work_point_id', $user->work_point_id);
    }

    $receivedRawTotal = (float) $qReceipts->sum('received_qty');

    // =========================
    // MANUFACTURING CONSUMPTION
    // =========================
    $qConsumption = ManufacturingMaterialConsumption::query()
        ->where('company_id', $user->company_id);

    if (!$isSuper) {
        $qConsumption->where('work_point_id', $user->work_point_id);
    }

    $consumedRawTotal = (float) $qConsumption->sum('consumed_qty');

    // =========================
    // MANUFACTURING STOCK
    // =========================
    $qStock = ManufacturingMaterialStock::with('rawMaterial')
        ->where('company_id', $user->company_id);

    if (!$isSuper) {
        $qStock->where('work_point_id', $user->work_point_id);
    }

    $materialStocks = $qStock->get();

    $stockQtyInTotal = (float) $materialStocks->sum('qty_in');
    $stockQtyOutTotal = (float) $materialStocks->sum('qty_out');
    $availableRaw = (float) $materialStocks->sum('balance');

    // =========================
    // PRODUCTS
    // =========================
    $qProducts = Product::query()
        ->where('company_id', $user->company_id)
        ->where('status', '!=', 'Deleted');

    if (!$isSuper) {
        $qProducts->where('work_point_id', $user->work_point_id);
    }

    $products = $qProducts->orderBy('product_name')->get();

    // Packed production total
    $packedProducts = PackedPrd::query()
        ->where('company_id', $user->company_id);

    if (!$isSuper) {
        $packedProducts->where('work_point_id', $user->work_point_id);
    }

    $packedProductsTotal = (float) $packedProducts->sum('pck_qnty');

    // Issued products total
    $issPrds = IssPrd::query()
        ->where('company_id', $user->company_id);

    if (!$isSuper) {
        $issPrds->where('work_point_id', $user->work_point_id);
    }

    $issPrdsTotal = (float) $issPrds->sum('issue_qnty');

    // Available packed products
    $availablePacked = $packedProductsTotal - $issPrdsTotal;
    if (!is_numeric($availablePacked)) {
        $availablePacked = 0.0;
    }

    // =========================
    // RAW CHARTS
    // =========================
    $graphLabels = ['Requested', 'Received', 'Consumed', 'Available Stock'];
    $graphRawData = [
        $requestedRawTotal ?: 0.0,
        $receivedRawTotal ?: 0.0,
        $consumedRawTotal ?: 0.0,
        $availableRaw ?: 0.0,
    ];

    // =========================
    // PRODUCT CHARTS
    // =========================
    $graphLabelsProducts = ['Packed Production', 'Issued Products', 'Available Packed'];
    $graphProductData = [
        $packedProductsTotal ?: 0.0,
        $issPrdsTotal ?: 0.0,
        $availablePacked ?: 0.0,
    ];

    return view('admin.home.manufacturing', compact(
        'rawMaterials',
        'materialStocks',
        'requestedRawTotal',
        'issuedAgainstRequestTotal',
        'remainingRequestTotal',
        'receivedRawTotal',
        'consumedRawTotal',
        'stockQtyInTotal',
        'stockQtyOutTotal',
        'availableRaw',
        'products',
        'packedProductsTotal',
        'issPrdsTotal',
        'availablePacked',
        'graphLabels',
        'graphRawData',
        'graphLabelsProducts',
        'graphProductData'
    ));
}
    // ================= RAW MATERIAL GLOBAL HELPERS =================
    protected function rawGlobalRoles(): array
    {
        return ['Admin', 'CEO', 'Managing Director (MD)', 'Admin-Developer'];
    }

    protected function rawCanAll($user): bool
    {
        return in_array($user->role, $this->rawGlobalRoles(), true)
            || $user->can('View-Raw-Material-All');
    }

    protected function rawCanCompany($user): bool
    {
        return $user->can('View-Raw-Material-Company')
            || $user->role === 'Company Manager';
    }

    protected function rawCanUnit($user): bool
    {
        return $user->can('View-Raw-Material-Unit')
            || $user->role === 'Unit Manager';
    }

    protected function rawAllowedWorkPoints($user)
    {
        if ($this->rawCanAll($user)) {
            return WorkPoint::where('status', '!=', 'Deleted')
                ->orderBy('work_name')
                ->get();
        }

        if ($this->rawCanCompany($user)) {
            return WorkPoint::where('company_id', $user->company_id)
                ->where('status', '!=', 'Deleted')
                ->orderBy('work_name')
                ->get();
        }

        if ($this->rawCanUnit($user)) {
            return WorkPoint::where('company_id', $user->company_id)
                ->where('comp_unit_id', $user->comp_unit_id)
                ->where('status', '!=', 'Deleted')
                ->orderBy('work_name')
                ->get();
        }

        return WorkPoint::where('id', $user->work_point_id)
            ->where('status', '!=', 'Deleted')
            ->orderBy('work_name')
            ->get();
    }

    protected function rawScopedQuery($user)
    {
        $query = RawMaterial::with(['company', 'workpoint'])
            ->where('status', '!=', 'Deleted');

        if ($this->rawCanAll($user)) {
            return $query;
        }

        if ($this->rawCanCompany($user)) {
            return $query->where('company_id', $user->company_id);
        }

        if ($this->rawCanUnit($user)) {
            return $query->where('company_id', $user->company_id)
                ->where('comp_unit_id', $user->comp_unit_id);
        }

        return $query->where('company_id', $user->company_id)
            ->where('work_point_id', $user->work_point_id);
    }

    protected function rawCanUseSelectableWorkPoint($user): bool
    {
        return $this->rawCanAll($user) || $this->rawCanCompany($user) || $this->rawCanUnit($user);
    }

    /**
     * Show Raw Materials view (separate page)
     */
    public function rawMaterials()
    {
        $user = auth()->user();

        $rawMaterials = $this->rawScopedQuery($user)
            ->orderBy('material_name')
            ->get();

        $workPoints = $this->rawAllowedWorkPoints($user);

        $companies = CompanySite::where('id', $user->company_id)->get();

        return view('admin.manfctr.raw_materials', compact('rawMaterials', 'workPoints', 'companies'));
    }

    public function storeRawMaterial(Request $request)
    {
        $user = auth()->user();

        $rules = [
            'material_name' => ['required', 'string', 'max:255'],
            'material_code' => ['nullable', 'string', 'max:255'],
            'unit_name'     => ['nullable', 'string', 'max:255'],
            'status'        => ['required', Rule::in(['Active', 'Inactive'])],
        ];

        if ($this->rawCanUseSelectableWorkPoint($user)) {
            $rules['work_point_id'] = [
                'required',
                'integer',
                Rule::exists('work_points', 'id')->where(function ($q) use ($user) {
                    if ($this->rawCanAll($user)) {
                        $q->where('status', '!=', 'Deleted');
                    } elseif ($this->rawCanCompany($user)) {
                        $q->where('company_id', $user->company_id)
                            ->where('status', '!=', 'Deleted');
                    } elseif ($this->rawCanUnit($user)) {
                        $q->where('company_id', $user->company_id)
                            ->where('comp_unit_id', $user->comp_unit_id)
                            ->where('status', '!=', 'Deleted');
                    }
                }),
            ];
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $workPointId = $this->rawCanUseSelectableWorkPoint($user)
            ? $request->work_point_id
            : $user->work_point_id;

        $workPoint = WorkPoint::find($workPointId);

        if (!$workPoint) {
            Alert::error('Error', 'Invalid work point selected');
            return back()->withInput();
        }

        RawMaterial::create([
            'user_id'       => $user->id,
            'company_id'    => $workPoint->company_id,
            'comp_unit_id'  => $workPoint->comp_unit_id,
            'work_point_id' => $workPoint->id,
            'material_name' => $request->material_name,
            'material_code' => $request->material_code,
            'unit_name'     => $request->unit_name,
            'status'        => $request->status,
        ]);

        Alert::success('Success', 'Raw material created successfully.');
        return redirect()->route('manfctr.rawmaterials.index');
    }

    public function updateRawMaterial(Request $request, $id)
    {
        try {
            $decrypted = decrypt($id);
        } catch (\Throwable $th) {
            Alert::error('Error', 'Invalid identifier');
            return back();
        }

        $user = auth()->user();
        $raw = RawMaterial::findOrFail($decrypted);

        $owned = false;

        if ($this->rawCanAll($user)) {
            $owned = true;
        } elseif ($this->rawCanCompany($user)) {
            $owned = ($raw->company_id == $user->company_id);
        } elseif ($this->rawCanUnit($user)) {
            $owned = ($raw->company_id == $user->company_id && $raw->comp_unit_id == $user->comp_unit_id);
        } else {
            $owned = ($raw->company_id == $user->company_id && $raw->work_point_id == $user->work_point_id);
        }

        if (!$owned) {
            Alert::error('Unauthorized', 'You cannot edit this raw material.');
            return back();
        }

        $rules = [
            'material_name' => ['required', 'string', 'max:255'],
            'material_code' => ['nullable', 'string', 'max:255'],
            'unit_name'     => ['nullable', 'string', 'max:255'],
            'status'        => ['required', Rule::in(['Active', 'Inactive', 'Deleted'])],
        ];

        if ($this->rawCanUseSelectableWorkPoint($user)) {
            $rules['work_point_id'] = [
                'required',
                'integer',
                Rule::exists('work_points', 'id')->where(function ($q) use ($user) {
                    if ($this->rawCanAll($user)) {
                        $q->where('status', '!=', 'Deleted');
                    } elseif ($this->rawCanCompany($user)) {
                        $q->where('company_id', $user->company_id)
                            ->where('status', '!=', 'Deleted');
                    } elseif ($this->rawCanUnit($user)) {
                        $q->where('company_id', $user->company_id)
                            ->where('comp_unit_id', $user->comp_unit_id)
                            ->where('status', '!=', 'Deleted');
                    }
                }),
            ];
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $workPointId = $this->rawCanUseSelectableWorkPoint($user)
            ? $request->work_point_id
            : $user->work_point_id;

        $workPoint = WorkPoint::find($workPointId);

        if (!$workPoint) {
            Alert::error('Error', 'Invalid work point selected');
            return back()->withInput();
        }

        $raw->update([
            'company_id'    => $workPoint->company_id,
            'comp_unit_id'  => $workPoint->comp_unit_id,
            'work_point_id' => $workPoint->id,
            'material_name' => $request->material_name,
            'material_code' => $request->material_code,
            'unit_name'     => $request->unit_name,
            'status'        => $request->status,
        ]);

        Alert::success('Success', 'Raw material updated successfully.');
        return redirect()->route('manfctr.rawmaterials.index');
    }

    public function removeRawMaterial($id)
    {
        try {
            $decrypted = decrypt($id);
        } catch (\Throwable $th) {
            Alert::error('Error', 'Invalid identifier');
            return back();
        }

        $user = auth()->user();
        $raw = RawMaterial::findOrFail($decrypted);

        $owned = false;

        if ($this->rawCanAll($user)) {
            $owned = true;
        } elseif ($this->rawCanCompany($user)) {
            $owned = ($raw->company_id == $user->company_id);
        } elseif ($this->rawCanUnit($user)) {
            $owned = ($raw->company_id == $user->company_id && $raw->comp_unit_id == $user->comp_unit_id);
        } else {
            $owned = ($raw->company_id == $user->company_id && $raw->work_point_id == $user->work_point_id);
        }

        if (!$owned) {
            Alert::error('Unauthorized', 'You cannot remove this raw material.');
            return back();
        }

        $raw->update(['status' => 'Deleted']);

        Alert::success('Success', 'Raw material removed successfully.');
        return redirect()->route('manfctr.rawmaterials.index');
    }
    /**
     * Show Raw Prices view (separate page)
     */
    public function rawPrices()
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();

        if ($isSuper) {
            $rawPrices = RawPrice::with(['company','workpoint','rawMaterial','user'])
                ->where('company_id', $user->company_id)
                ->where('Status', '!=', 'Deleted')
                ->orderBy('id','desc')->get();

            $workPoints = WorkPoint::where('company_id', $user->company_id)
                ->where('status', '!=', 'Deleted')->orderBy('work_name')->get();
        } else {
            $rawPrices = RawPrice::with(['company','workpoint','rawMaterial','user'])
                ->where('company_id', $user->company_id)
                ->where('work_point_id', $user->work_point_id)
                ->where('Status', '!=', 'Deleted')
                ->orderBy('id','desc')->get();

            $workPoints = collect();
        }
        // need rawMaterials list for selects
        $rawMaterials = RawMaterial::where('company_id', $user->company_id)
            ->where('status','!=','Deleted')
            ->when(!$isSuper, function($q) use ($user) {
                $q->where('work_point_id', $user->work_point_id);
            })->orderBy('material_name')->get();

        return view('admin.manfctr.raw_prices', compact('rawPrices','workPoints','rawMaterials'));
    }
    // ---------------- Raw Prices ----------------
    public function storeRawPrice(Request $request)
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();

        $rules = [
            'Raw_id' => ['required','integer', Rule::exists('raw_materials','id')->where(function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })],
            'RawPrice' => ['required','numeric'],
            'Status' => ['nullable', Rule::in(['Active','Inactive','Deleted'])],
        ];
        if ($isSuper) {
            $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')->where(function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })];
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        RawPrice::create([
            'company_id' => $user->company_id,
            'work_point_id' => $isSuper ? $request->work_point_id : $user->work_point_id,
            'Raw_id' => $request->Raw_id,
            'User_id' => $user->id,
            'RawPrice' => $request->RawPrice,
            'Status' => $request->Status ?? 'Active',
        ]);

        Alert::success('Success','Raw price created successfully.');
        return redirect()->route('manfctr.rawprices.index');
    }

    public function updateRawPrice(Request $request, $id)
    {
        try {
            $decrypted = decrypt($id);
        } catch (\Throwable $th) {
            Alert::error('Error','Invalid identifier');
            return back();
        }
        $user = auth()->user();
        $isSuper = $this->isSuperRole();
        $rp = RawPrice::findOrFail($decrypted);
        if ($rp->company_id !== $user->company_id) {
            Alert::error('Unauthorized','You cannot edit prices from other companies.');
            return back();
        }

        $rules = [
            'Raw_id' => ['required','integer', Rule::exists('raw_materials','id')->where(function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })],
            'RawPrice' => ['required','numeric'],
            'Status' => ['required', Rule::in(['Active','Inactive','Deleted'])],
        ];
        if ($isSuper) {
            $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')->where(function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })];
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $rp->update([
            'company_id' => $user->company_id,
            'work_point_id' => $isSuper ? $request->work_point_id : $user->work_point_id,
            'Raw_id' => $request->Raw_id,
            'User_id' => $user->id,
            'RawPrice' => $request->RawPrice,
            'Status' => $request->Status,
        ]);

        Alert::success('Success','Raw price updated successfully.');
        return redirect()->route('manfctr.rawprices.index');
    }

    public function removeRawPrice($id)
    {
        try {
            $decrypted = decrypt($id);
        } catch (\Throwable $th) {
            Alert::error('Error','Invalid identifier');
            return back();
        }
        $user = auth()->user();
        $rp = RawPrice::findOrFail($decrypted);
        if ($rp->company_id !== $user->company_id) {
            Alert::error('Unauthorized','You cannot remove raw prices from other companies.');
            return back();
        }
        $rp->update(['Status' => 'Deleted']);
        Alert::success('Success','Raw price removed successfully.');
        return redirect()->route('manfctr.rawprices.index');
    }
    /**
     * Show Products view (separate page)
     */
    public function products()
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();

        if ($isSuper) {
            $products = Product::with(['company','workpoint'])
                ->where('company_id', $user->company_id)
                ->where('status', '!=', 'Deleted')
                ->orderBy('product_name')->get();

            $workPoints = WorkPoint::where('company_id', $user->company_id)
                ->where('status', '!=', 'Deleted')->orderBy('work_name')->get();
        } else {
            $products = Product::with(['company','workpoint'])
                ->where('company_id', $user->company_id)
                ->where('work_point_id', $user->work_point_id)
                ->where('status', '!=', 'Deleted')->orderBy('product_name')->get();

            $workPoints = collect();
        }

        $companies = CompanySite::where('id', $user->company_id)->get();

        return view('admin.manfctr.products', compact('products','workPoints','companies'));
    }
    // ---------------- Products ----------------
    public function storeProduct(Request $request)
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();

        $rules = [
            'product_name' => ['required','string','max:255'],
            'product_size' => ['nullable','string','max:255'],
            'status' => ['nullable', Rule::in(['Active','Inactive','Deleted'])],
        ];

        if ($isSuper) {
            $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')->where(function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })];
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $companyId = $user->company_id;
        $workPointId = $isSuper ? $request->work_point_id : $user->work_point_id;

        Product::create([
            'user_id' => $user->id,
            'company_id' => $companyId,
            'work_point_id' => $workPointId,
            'product_name' => $request->product_name,
            'product_size' => $request->product_size,
            'status' => $request->status ?? 'Active',
        ]);

        Alert::success('Success','Product created successfully.');
        return redirect()->route('manfctr.products.index');
    }

    public function updateProduct(Request $request, $id)
    {
        try {
            $decrypted = decrypt($id);
        } catch (\Throwable $th) {
            Alert::error('Error','Invalid identifier');
            return back();
        }
        $user = auth()->user();
        $isSuper = $this->isSuperRole();
        $product = Product::findOrFail($decrypted);
        if ($product->company_id !== $user->company_id) {
            Alert::error('Unauthorized','You cannot edit products from other companies.');
            return back();
        }

        $rules = [
            'product_name' => ['required','string','max:255'],
            'product_size' => ['nullable','string','max:255'],
            'status' => ['required', Rule::in(['Active','Inactive','Deleted'])],
        ];
        if ($isSuper) {
            $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')->where(function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })];
        }
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $product->update([
            'company_id' => $user->company_id,
            'work_point_id' => $isSuper ? $request->work_point_id : $user->work_point_id,
            'product_name' => $request->product_name,
            'product_size' => $request->product_size,
            'status' => $request->status,
        ]);

        Alert::success('Success','Product updated successfully.');
        return redirect()->route('manfctr.products.index');
    }

    public function removeProduct($id)
    {
        try {
            $decrypted = decrypt($id);
        } catch (\Throwable $th) {
            Alert::error('Error','Invalid identifier');
            return back();
        }
        $user = auth()->user();
        $product = Product::findOrFail($decrypted);
        if ($product->company_id !== $user->company_id) {
            Alert::error('Unauthorized','You cannot remove products from other companies.');
            return back();
        }
        $product->update(['status' => 'Deleted']);
        Alert::success('Success','Product removed successfully.');
        return redirect()->route('manfctr.products.index');
    }

    /**
     * Show Product Prices view (separate page)
     */
    public function prdPrices()
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();
        if ($isSuper) {
            $prdPrices = PrdPrice::with(['company','workpoint','product','user'])
                ->where('company_id', $user->company_id)
                ->where('Status', '!=', 'Deleted')
                ->orderBy('id','desc')->get();

            $workPoints = WorkPoint::where('company_id', $user->company_id)
                ->where('status', '!=', 'Deleted')->orderBy('work_name')->get();
        } else {
            $prdPrices = PrdPrice::with(['company','workpoint','product','user'])
                ->where('company_id', $user->company_id)
                ->where('work_point_id', $user->work_point_id)
                ->where('Status', '!=', 'Deleted')
                ->orderBy('id','desc')->get();

            $workPoints = collect();
        }

        // also need products list for selects
        $products = Product::where('company_id', $user->company_id)
            ->where('status','!=','Deleted')
            ->when(!$isSuper, function($q) use ($user) {
                $q->where('work_point_id', $user->work_point_id);
            })->orderBy('product_name')->get();

        return view('admin.manfctr.prd_prices', compact('prdPrices','workPoints','products'));
    }
    // ---------------- Product Prices ----------------
    public function storePrdPrice(Request $request)
    {
        $user = auth()->user();
        $isSuper = $this->isSuperRole();

        $rules = [
            'Product_id' => ['required','integer', Rule::exists('products','id')->where(function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })],
            'RawPrice' => ['required','numeric'],
            'Status' => ['nullable', Rule::in(['Active','Inactive','Deleted'])],
        ];
        if ($isSuper) {
            $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')->where(function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })];
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        PrdPrice::create([
            'company_id' => $user->company_id,
            'work_point_id' => $isSuper ? $request->work_point_id : $user->work_point_id,
            'Product_id' => $request->Product_id,
            'User_id' => $user->id,
            'RawPrice' => $request->RawPrice,
            'Status' => $request->Status ?? 'Active',
        ]);

        Alert::success('Success','Product price created successfully.');
        return redirect()->route('manfctr.prdprices.index');
    }

    public function updatePrdPrice(Request $request, $id)
    {
        try {
            $decrypted = decrypt($id);
        } catch (\Throwable $th) {
            Alert::error('Error','Invalid identifier');
            return back();
        }
        $user = auth()->user();
        $isSuper = $this->isSuperRole();
        $pp = PrdPrice::findOrFail($decrypted);
        if ($pp->company_id !== $user->company_id) {
            Alert::error('Unauthorized','You cannot edit prices from other companies.');
            return back();
        }

        $rules = [
            'Product_id' => ['required','integer', Rule::exists('products','id')->where(function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })],
            'RawPrice' => ['required','numeric'],
            'Status' => ['required', Rule::in(['Active','Inactive','Deleted'])],
        ];
        if ($isSuper) {
            $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')->where(function($q) use ($user) {
                $q->where('company_id', $user->company_id);
            })];
        }
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $pp->update([
            'company_id' => $user->company_id,
            'work_point_id' => $isSuper ? $request->work_point_id : $user->work_point_id,
            'Product_id' => $request->Product_id,
            'User_id' => $user->id,
            'RawPrice' => $request->RawPrice,
            'Status' => $request->Status,
        ]);

        Alert::success('Success','Product price updated successfully.');
        return redirect()->route('manfctr.prdprices.index');
    }

    public function removePrdPrice($id)
    {
        try {
            $decrypted = decrypt($id);
        } catch (\Throwable $th) {
            Alert::error('Error','Invalid identifier');
            return back();
        }
        $user = auth()->user();
        $pp = PrdPrice::findOrFail($decrypted);
        if ($pp->company_id !== $user->company_id) {
            Alert::error('Unauthorized','You cannot remove product prices from other companies.');
            return back();
        }
        $pp->update(['Status' => 'Deleted']);
        Alert::success('Success','Product price removed successfully.');
        return redirect()->route('manfctr.prdprices.index');
    }
  // PACKED PRODUCTS (pack / list / update / remove)
public function packedPrds()
{
    $user = auth()->user();
    $isSuper = $this->isSuperRole();

    if ($isSuper) {
        $packs = PackedPrd::with(['product','company','workpoint','user'])
            ->where('company_id', $user->company_id)
            ->where('status','!=','Deleted')
            ->orderBy('pck_date','desc')->get();
        $workPoints = WorkPoint::where('company_id', $user->company_id)->where('status','!=','Deleted')->get();
    } else {
        $packs = PackedPrd::with(['product','company','workpoint','user'])
            ->where('company_id', $user->company_id)
            ->where('work_point_id', $user->work_point_id)
            ->where('status','!=','Deleted')
            ->orderBy('pck_date','desc')->get();
        $workPoints = collect();
    }

    $products = Product::where('company_id', $user->company_id)->where('status','!=','Deleted')->get();
    return view('admin.manfctr.packed_prds', compact('packs','products','workPoints'));
}

public function storePackedPrd(Request $request)
{
    $user = auth()->user();
    $isSuper = $this->isSuperRole();

    $rules = [
        'pck_date' => ['required','date'],
        'prd_id' => ['required','integer', Rule::exists('products','id')->where(function($q) use ($user) {
            $q->where('company_id', $user->company_id);
        })],
        'pck_qnty' => ['required','numeric'],
        'pck_unit' => ['required','string'],
    ];
    if ($isSuper) {
        $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')->where(function($q) use ($user) {
            $q->where('company_id', $user->company_id);
        })];
    }

    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) return back()->withErrors($validator)->withInput();

    DB::transaction(function() use ($request, $user, $isSuper) {
        $pack = PackedPrd::create([
            'pck_date' => $request->pck_date,
            'company_id' => $user->company_id,
            'work_point_id' => $isSuper ? $request->work_point_id : $user->work_point_id,
            'prd_id' => $request->prd_id,
            'pck_qnty' => $request->pck_qnty,
            'pck_unit' => $request->pck_unit,
            'user_id' => $user->id,
            'status' => $request->status ?? 'Active',
        ]);

        // update product stock (increase available)
        $stock = PrdStock::firstOrNew([
            'company_id' => $user->company_id,
            'work_point_id' => $isSuper ? $request->work_point_id : $user->work_point_id,
            'prd_id' => $request->prd_id,
        ]);
        $stock->stck_unit = $request->pck_unit;
        $stock->avlb_qnty = ($stock->avlb_qnty ?? 0) + floatval($request->pck_qnty);
        $stock->issd_qnty = $stock->issd_qnty ?? 0.0;
        $stock->save();
    });

    Alert::success('Success','Product packed and stock updated successfully.');
    return redirect()->route('manfctr.packed.index');
}

public function updatePackedPrd(Request $request, $id)
{
    try { $decrypted = decrypt($id); } catch (\Throwable $th) { Alert::error('Error','Invalid identifier'); return back(); }
    $user = auth()->user();
    $isSuper = $this->isSuperRole();
    $pack = PackedPrd::findOrFail($decrypted);
    if ($pack->company_id !== $user->company_id) { Alert::error('Unauthorized','You cannot edit this record.'); return back(); }

    $rules = [
        'pck_date' => ['required','date'],
        'prd_id' => ['required','integer', Rule::exists('products','id')->where(function($q) use ($user) { $q->where('company_id',$user->company_id); })],
        'pck_qnty' => ['required','numeric'],
        'pck_unit' => ['required','string'],
        'status' => ['required', Rule::in(['Active','Deleted'])],
    ];
    if ($isSuper) $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')->where(function($q) use ($user) { $q->where('company_id',$user->company_id); })];

    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) return back()->withErrors($validator)->withInput();

    DB::transaction(function() use ($request, $user, $isSuper, $pack) {
        // compute delta
        $old_qnty = floatval($pack->pck_qnty);
        $new_qnty = floatval($request->pck_qnty);
        $delta = $new_qnty - $old_qnty;

        $old_prd = $pack->prd_id;
        $pack->update([
            'pck_date' => $request->pck_date,
            'prd_id' => $request->prd_id,
            'pck_qnty' => $new_qnty,
            'pck_unit' => $request->pck_unit,
            'work_point_id' => $isSuper ? $request->work_point_id : $pack->work_point_id,
            'status' => $request->status,
        ]);

        // adjust stock: subtract old from old stock, add delta to new stock
        // handle if product changed
        if ($old_prd != $request->prd_id) {
            // revert old product stock
            $oldStock = PrdStock::where('company_id',$pack->company_id)->where('work_point_id',$pack->work_point_id)->where('prd_id',$old_prd)->first();
            if ($oldStock) {
                $oldStock->avlb_qnty = max(0, ($oldStock->avlb_qnty ?? 0) - $old_qnty);
                $oldStock->save();
            }
            // add new pack qnty to new product stock
            $newStock = PrdStock::firstOrNew([
                'company_id' => $pack->company_id,
                'work_point_id' => $isSuper ? $request->work_point_id : $pack->work_point_id,
                'prd_id' => $request->prd_id,
            ]);
            $newStock->stck_unit = $request->pck_unit;
            $newStock->avlb_qnty = ($newStock->avlb_qnty ?? 0) + $new_qnty;
            $newStock->issd_qnty = $newStock->issd_qnty ?? 0;
            $newStock->save();
        } else {
            // same product -> apply delta
            $stock = PrdStock::firstOrNew([
                'company_id' => $pack->company_id,
                'work_point_id' => $isSuper ? $request->work_point_id : $pack->work_point_id,
                'prd_id' => $request->prd_id,
            ]);
            $stock->stck_unit = $request->pck_unit;
            $stock->avlb_qnty = ($stock->avlb_qnty ?? 0) + $delta;
            $stock->issd_qnty = $stock->issd_qnty ?? 0;
            $stock->save();
        }
    });

    Alert::success('Success','Packed product updated successfully and stock adjusted.');
    return redirect()->route('manfctr.packed.index');
}

public function removePackedPrd($id)
{
    try { $decrypted = decrypt($id); } catch (\Throwable $th) { Alert::error('Error','Invalid identifier'); return back(); }
    $user = auth()->user();
    $pack = PackedPrd::findOrFail($decrypted);
    if ($pack->company_id !== $user->company_id) { Alert::error('Unauthorized','You cannot remove this record.'); return back(); }
    DB::transaction(function() use ($pack) {
        // revert stock
        $stock = PrdStock::where('company_id', $pack->company_id)->where('work_point_id', $pack->work_point_id)->where('prd_id', $pack->prd_id)->first();
        if ($stock) {
            $stock->avlb_qnty = max(0, ($stock->avlb_qnty ?? 0) - floatval($pack->pck_qnty));
            $stock->save();
        }
        $pack->update(['status' => 'Deleted']);
    });
    Alert::success('Success','Packed product removed successfully and stock adjusted.');
    return redirect()->route('manfctr.packed.index');
}
public function prdStocks()
{
    $user = auth()->user();
    $isSuper = $this->isSuperRole();

    if ($isSuper) {
        $stocks = PrdStock::with(['product','company','workpoint'])->where('company_id', $user->company_id)->orderBy('id','desc')->get();
        $workPoints = WorkPoint::where('company_id', $user->company_id)->where('status','!=','Deleted')->get();
    } else {
        $stocks = PrdStock::with(['product','company','workpoint'])
            ->where('company_id', $user->company_id)
            ->where('work_point_id', $user->work_point_id)
            ->orderBy('id','desc')->get();
        $workPoints = collect();
    }
    return view('admin.manfctr.prd_stocks', compact('stocks','workPoints'));
}
// PRD ORDERS
public function prdOrders()
{
    $user = auth()->user();
    $isSuper = $this->isSuperRole();

    if ($isSuper) {
        $orders = PrdOrder::with(['product','company','workpoint','user'])
            ->where('company_id', $user->company_id)->where('status','!=','Deleted')->orderBy('ord_date','desc')->get();
        $workPoints = WorkPoint::where('company_id', $user->company_id)->where('status','!=','Deleted')->get();
    } else {
        $orders = PrdOrder::with(['product','company','workpoint','user'])
            ->where('company_id', $user->company_id)->where('work_point_id', $user->work_point_id)
            ->where('status','!=','Deleted')->orderBy('ord_date','desc')->get();
        $workPoints = collect();
    }

    $products = Product::where('company_id', $user->company_id)->where('status','!=','Deleted')->get();
    return view('admin.manfctr.prd_orders', compact('orders','products','workPoints'));
}

public function storePrdOrder(Request $request)
{
    $user = auth()->user();
    $isSuper = $this->isSuperRole();

    $rules = [
        'ord_date' => ['required','date'],
        'ord_qnty' => ['required','numeric'],
        'ord_unit' => ['required','string'],
        'prd_id' => ['required','integer', Rule::exists('products','id')->where(function($q) use ($user) { $q->where('company_id',$user->company_id); })],
        'customer_name' => ['required','string','max:255'],
    ];
    if ($isSuper) $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')->where(function($q) use ($user){ $q->where('company_id',$user->company_id); })];

    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) return back()->withErrors($validator)->withInput();

    PrdOrder::create([
        'ord_date' => $request->ord_date,
        'ord_qnty' => $request->ord_qnty,
        'iss_qnty' => $request->iss_qnty ?? 0,
        'uniss_qnty' => ($request->ord_qnty ?? 0) - ($request->iss_qnty ?? 0),
        'ord_unit' => $request->ord_unit,
        'prd_id' => $request->prd_id,
        'customer_name' => $request->customer_name,
        'phone_no' => $request->phone_no ?? null,
        'location' => $request->location ?? null,
        'user_id' => $user->id,
        'status' => $request->status ?? 'Active',
        'company_id' => $user->company_id,
        'work_point_id' => $isSuper ? $request->work_point_id : $user->work_point_id,
    ]);

    Alert::success('Success','Product order created successfully.');
    return redirect()->route('manfctr.orders.index');
}

public function updatePrdOrder(Request $request, $id)
{
    try { $decrypted = decrypt($id); } catch (\Throwable $th) { Alert::error('Error','Invalid identifier'); return back(); }
    $user = auth()->user();
    $order = PrdOrder::findOrFail($decrypted);
    if ($order->company_id !== $user->company_id) { Alert::error('Unauthorized','You cannot edit this record.'); return back(); }

    $isSuper = $this->isSuperRole();
    $rules = [
        'ord_date' => ['required','date'],
        'ord_qnty' => ['required','numeric'],
        'ord_unit' => ['required','string'],
        'prd_id' => ['required','integer', Rule::exists('products','id')],
        'customer_name' => ['required','string','max:255'],
        'status' => ['required', Rule::in(['Active','Deleted'])],
    ];
    if ($isSuper) $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')];

    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) return back()->withErrors($validator)->withInput();

    // keep iss_qnty as is (unless you want to allow direct edit - here we keep control)
    $order->update([
        'ord_date' => $request->ord_date,
        'ord_qnty' => $request->ord_qnty,
        'uniss_qnty' => max(0, $request->ord_qnty - ($order->iss_qnty ?? 0)),
        'ord_unit' => $request->ord_unit,
        'prd_id' => $request->prd_id,
        'customer_name' => $request->customer_name,
        'phone_no' => $request->phone_no ?? null,
        'location' => $request->location ?? null,
        'status' => $request->status,
        'work_point_id' => $isSuper ? $request->work_point_id : $order->work_point_id,
    ]);

    Alert::success('Success','Product order updated successfully.');
    return redirect()->route('manfctr.orders.index');
}

public function removePrdOrder($id)
{
    try { $decrypted = decrypt($id); } catch (\Throwable $th) { Alert::error('Error','Invalid identifier'); return back(); }
    $user = auth()->user();
    $order = PrdOrder::findOrFail($decrypted);
    if ($order->company_id !== $user->company_id) { Alert::error('Unauthorized','You cannot remove this record.'); return back(); }

    $order->update(['status' => 'Deleted']);
    Alert::success('Success','Product order removed successfully.');
    return redirect()->route('manfctr.orders.index');
}
// ISSUED PRODUCTS
public function issPrds()
{
    $user = auth()->user();
    $isSuper = $this->isSuperRole();

    if ($isSuper) {
        $issues = IssPrd::with(['product','order','company','workpoint','user'])
            ->where('company_id', $user->company_id)->where('status','!=','Deleted')->orderBy('issue_date','desc')->get();
        $workPoints = WorkPoint::where('company_id', $user->company_id)->where('status','!=','Deleted')->get();
    } else {
        $issues = IssPrd::with(['product','order','company','workpoint','user'])
            ->where('company_id', $user->company_id)->where('work_point_id', $user->work_point_id)
            ->where('status','!=','Deleted')->orderBy('issue_date','desc')->get();
        $workPoints = collect();
    }

    $products = Product::where('company_id', $user->company_id)->where('status','!=','Deleted')->get();
    $orders = PrdOrder::where('company_id', $user->company_id)->where('status','!=','Deleted')->get();

    return view('admin.manfctr.iss_prds', compact('issues','products','orders','workPoints'));
}

public function storeIssPrd(Request $request)
{
    $user = auth()->user();
    $isSuper = $this->isSuperRole();

    $rules = [
        'issue_date' => ['required','date'],
        'prd_id' => ['required','integer', Rule::exists('products','id')->where(function($q) use ($user) { $q->where('company_id',$user->company_id); })],
        'issue_qnty' => ['required','numeric'],
        'iss_unit' => ['required','string'],
        // order_id optional
    ];
    if ($isSuper) $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')->where(function($q) use ($user){ $q->where('company_id',$user->company_id); })];

    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) return back()->withErrors($validator)->withInput();

    $companyId = $user->company_id;
    $workPointId = $isSuper ? $request->work_point_id : $user->work_point_id;

    // check stock availability
    $stock = PrdStock::where('company_id',$companyId)->where('work_point_id',$workPointId)->where('prd_id',$request->prd_id)->first();
    $available = ($stock->avlb_qnty ?? 0);
    if (floatval($request->issue_qnty) > $available) {
        Alert::warning('Issued Quantity','Insufficient stock. Available: '. number_format($available,2));
        return back();
        // return back()->withErrors(['issue_qnty' => 'Insufficient stock. Available: '. number_format($available,2) ])->withInput();
    }
    DB::transaction(function() use ($request, $user, $isSuper, $companyId, $workPointId) {
        $iss = IssPrd::create([
            'order_id' => $request->order_id ?? null,
            'prd_id' => $request->prd_id,
            'issue_date' => $request->issue_date,
            'issue_qnty' => $request->issue_qnty,
            'iss_unit' => $request->iss_unit,
            'user_id' => $user->id,
            'received_by' => $request->received_by ?? null,
            'status' => $request->status ?? 'Active',
            'company_id' => $companyId,
            'work_point_id' => $workPointId,
        ]);

        // update stock: reduce available and increase issued
        $stock = PrdStock::firstOrNew(['company_id'=>$companyId,'work_point_id'=>$workPointId,'prd_id'=>$request->prd_id]);
        $stock->stck_unit = $request->iss_unit;
        $stock->avlb_qnty = max(0, ($stock->avlb_qnty ?? 0) - floatval($request->issue_qnty));
        $stock->issd_qnty = ($stock->issd_qnty ?? 0) + floatval($request->issue_qnty);
        $stock->save();

        // update order if linked
        if ($request->order_id) {
            $order = PrdOrder::find($request->order_id);
            if ($order && $order->company_id == $companyId) {
                $order->iss_qnty = ($order->iss_qnty ?? 0) + floatval($request->issue_qnty);
                $order->uniss_qnty = max(0, $order->ord_qnty - $order->iss_qnty);
                $order->save();
            }
        }
    });

    Alert::success('Success','Issue recorded and stock updated successfully.');
    return redirect()->route('manfctr.iss.index');
}

public function updateIssPrd(Request $request, $id)
{
    try { $decrypted = decrypt($id); } catch (\Throwable $th) { Alert::error('Error','Invalid identifier'); return back(); }
    $user = auth()->user();
    $isSuper = $this->isSuperRole();
    $iss = IssPrd::findOrFail($decrypted);
    if ($iss->company_id !== $user->company_id) { Alert::error('Unauthorized','You cannot edit this record.'); return back(); }

    $rules = [
        'issue_date' => ['required','date'],
        'prd_id' => ['required','integer', Rule::exists('products','id')],
        'issue_qnty' => ['required','numeric'],
        'iss_unit' => ['required','string'],
        'status' => ['required', Rule::in(['Active','Deleted'])],
    ];
    if ($isSuper) $rules['work_point_id'] = ['required','integer', Rule::exists('work_points','id')];

    $validator = Validator::make($request->all(), $rules);
    if ($validator->fails()) return back()->withErrors($validator)->withInput();

    DB::transaction(function() use ($request, $user, $isSuper, $iss) {
        $old_qnty = floatval($iss->issue_qnty);
        $new_qnty = floatval($request->issue_qnty);
        $delta = $new_qnty - $old_qnty;
        $companyId = $iss->company_id;
        $workPointId = $isSuper ? $request->work_point_id : $iss->work_point_id;

        // check availability for increase
        if ($delta > 0) {
            $stockCheck = PrdStock::where('company_id',$companyId)->where('work_point_id',$workPointId)->where('prd_id',$request->prd_id)->first();
            $available = ($stockCheck->avlb_qnty ?? 0);
            if ($delta > $available) {
                Alert::warning('Issued Quantity','Insufficient stock for increased issue. Available: '. number_format($available,2));
                return back();
                // throw new \Exception('Insufficient stock for increased issue. Available: '. number_format($available,2));
            }
        }

        // If order changed, revert old order and apply new
        $oldOrderId = $iss->order_id;
        if ($oldOrderId && $oldOrderId != $request->order_id) {
            $oldOrder = PrdOrder::find($oldOrderId);
            if ($oldOrder) {
                $oldOrder->iss_qnty = max(0, ($oldOrder->iss_qnty ?? 0) - $old_qnty);
                $oldOrder->uniss_qnty = max(0, $oldOrder->ord_qnty - $oldOrder->iss_qnty);
                $oldOrder->save();
            }
        }

        // update iss record
        $iss->update([
            'order_id' => $request->order_id ?? null,
            'prd_id' => $request->prd_id,
            'issue_date' => $request->issue_date,
            'issue_qnty' => $new_qnty,
            'iss_unit' => $request->iss_unit,
            'received_by' => $request->received_by ?? $iss->received_by,
            'status' => $request->status,
            'work_point_id' => $isSuper ? $request->work_point_id : $iss->work_point_id,
        ]);

        // update stock (apply delta)
        $stock = PrdStock::firstOrNew(['company_id'=>$companyId,'work_point_id'=>$workPointId,'prd_id'=>$request->prd_id]);
        $stock->stck_unit = $request->iss_unit;
        $stock->avlb_qnty = max(0, ($stock->avlb_qnty ?? 0) - $delta);
        $stock->issd_qnty = ($stock->issd_qnty ?? 0) + $delta;
        $stock->save();

        // update new order if provided
        if ($request->order_id) {
            $order = PrdOrder::find($request->order_id);
            if ($order && $order->company_id == $companyId) {
                $order->iss_qnty = ($order->iss_qnty ?? 0) + $new_qnty;
                $order->uniss_qnty = max(0, $order->ord_qnty - $order->iss_qnty);
                $order->save();
            }
        }
    });

    Alert::success('Success','Issue updated successfully and stock adjusted.');
    return redirect()->route('manfctr.iss.index');
}

public function removeIssPrd($id)
{
    try { $decrypted = decrypt($id); } catch (\Throwable $th) { Alert::error('Error','Invalid identifier'); return back(); }
    $user = auth()->user();
    $iss = IssPrd::findOrFail($decrypted);
    if ($iss->company_id !== $user->company_id) { Alert::error('Unauthorized','You cannot remove this record.'); return back(); }

    DB::transaction(function() use ($iss) {
        // revert stock
        $stock = PrdStock::where('company_id', $iss->company_id)->where('work_point_id', $iss->work_point_id)->where('prd_id', $iss->prd_id)->first();
        if ($stock) {
            $stock->avlb_qnty = ($stock->avlb_qnty ?? 0) + floatval($iss->issue_qnty);
            $stock->issd_qnty = max(0, ($stock->issd_qnty ?? 0) - floatval($iss->issue_qnty));
            $stock->save();
        }

        // revert order if linked
        if ($iss->order_id) {
            $order = PrdOrder::find($iss->order_id);
            if ($order) {
                $order->iss_qnty = max(0, ($order->iss_qnty ?? 0) - floatval($iss->issue_qnty));
                $order->uniss_qnty = max(0, $order->ord_qnty - $order->iss_qnty);
                $order->save();
            }
        }

        $iss->update(['status' => 'Deleted']);
    });

    Alert::success('Success','Issue removed successfully and stock adjusted.');
    return redirect()->route('manfctr.iss.index');
}
public function prdStockMovement(Request $request)
{
    $user = auth()->user();
    $isSuper = $this->isSuperRole();

    // filters
    $start = $request->start_date ? date('Y-m-d', strtotime($request->start_date)) : date('Y-m-d', strtotime('-15 days'));
    $end   = $request->end_date   ? date('Y-m-d', strtotime($request->end_date))   : date('Y-m-d');
    $filterProduct = $request->prd_id ?: null;
    $filterWorkPoint = $isSuper ? ($request->work_point_id ?: null) : $user->work_point_id;

    // products for this company (and optional filter)
    $productsQuery = Product::where('company_id', $user->company_id)->where('status','!=','Deleted');
    if ($filterProduct) $productsQuery->where('id', $filterProduct);
    $products = $productsQuery->orderBy('product_name')->get();

    // 1) Opening balances BEFORE start date (packed_before - issued_before) per product
    $packedBeforeQ = PackedPrd::select('prd_id', DB::raw('COALESCE(SUM(pck_qnty),0) as total_packed'))
        ->where('company_id', $user->company_id)
        ->whereDate('pck_date', '<', $start)
        ->where('status', '!=', 'Deleted');

    $issuedBeforeQ = IssPrd::select('prd_id', DB::raw('COALESCE(SUM(issue_qnty),0) as total_issued'))
        ->where('company_id', $user->company_id)
        ->whereDate('issue_date', '<', $start)
        ->where('status', '!=', 'Deleted');

    if ($filterWorkPoint) {
        $packedBeforeQ->where('work_point_id', $filterWorkPoint);
        $issuedBeforeQ->where('work_point_id', $filterWorkPoint);
    }
    if ($filterProduct) {
        $packedBeforeQ->where('prd_id', $filterProduct);
        $issuedBeforeQ->where('prd_id', $filterProduct);
    }

    $packedBefore = $packedBeforeQ->groupBy('prd_id')->get()->keyBy('prd_id')->map(fn($r) => (float)$r->total_packed)->toArray();
    $issuedBefore = $issuedBeforeQ->groupBy('prd_id')->get()->keyBy('prd_id')->map(fn($r) => (float)$r->total_issued)->toArray();

    $opening = [];
    foreach ($products as $p) {
        $opening[$p->id] = round( ($packedBefore[$p->id] ?? 0.0) - ($issuedBefore[$p->id] ?? 0.0), 6 );
    }

    // 2) Aggregate packed and issued per date between start..end
    $packedQ = PackedPrd::select(DB::raw('pck_date as date'), 'prd_id', DB::raw('COALESCE(SUM(pck_qnty),0) as packed'))
        ->where('company_id', $user->company_id)
        ->whereBetween(DB::raw('pck_date'), [$start, $end])
        ->where('status', '!=', 'Deleted');

    $issuedQ = IssPrd::select(DB::raw('issue_date as date'), 'prd_id', DB::raw('COALESCE(SUM(issue_qnty),0) as issued'))
        ->where('company_id', $user->company_id)
        ->whereBetween(DB::raw('issue_date'), [$start, $end])
        ->where('status', '!=', 'Deleted');

    if ($filterWorkPoint) {
        $packedQ->where('work_point_id', $filterWorkPoint);
        $issuedQ->where('work_point_id', $filterWorkPoint);
    }
    if ($filterProduct) {
        $packedQ->where('prd_id', $filterProduct);
        $issuedQ->where('prd_id', $filterProduct);
    }

    $packedRows = $packedQ->groupBy('date','prd_id')->orderBy('date')->get();
    $issuedRows = $issuedQ->groupBy('date','prd_id')->orderBy('date')->get();

    // 3) merge into map[date][prd_id] => ['packed'=>x,'issued'=>y]
    $map = [];
    foreach ($packedRows as $r) {
        $d = $r->date;
        $pid = $r->prd_id;
        $map[$d][$pid]['packed'] = (float)$r->packed;
    }
    foreach ($issuedRows as $r) {
        $d = $r->date;
        $pid = $r->prd_id;
        $map[$d][$pid]['issued'] = (float)$r->issued;
    }

    // 4) build date period
    $period = [];
    $current = new \DateTime($start);
    $endDate = new \DateTime($end);
    while ($current <= $endDate) {
        $period[] = $current->format('Y-m-d');
        $current->modify('+1 day');
    }

    // 5) build rows with running balance per product
    $rows = []; // each: date, product, opening, packed, issued, closing, unit
    $running = $opening;

    // product default unit: try prd_stocks stck_unit, else null
    $stockUnits = PrdStock::where('company_id', $user->company_id)
        ->when($filterWorkPoint, fn($q) => $q->where('work_point_id', $filterWorkPoint))
        ->when($filterProduct, fn($q) => $q->where('prd_id', $filterProduct))
        ->get()->keyBy('prd_id')->map(fn($s) => $s->stck_unit ?? null)->toArray();

    foreach ($period as $date) {
        foreach ($products as $prod) {
            $pid = $prod->id;
            $openingForDay = round($running[$pid] ?? 0.0, 6);
            $packed = round($map[$date][$pid]['packed'] ?? 0.0, 6);
            $issued = round($map[$date][$pid]['issued'] ?? 0.0, 6);
            $closing = round($openingForDay + $packed - $issued, 6);
            $running[$pid] = $closing;

            $unit = $stockUnits[$pid] ?? null;

            $rows[] = [
                'date' => $date,
                'product' => $prod,
                'opening' => $openingForDay,
                'packed'  => $packed,
                'issued'  => $issued,
                'closing' => $closing,
                'unit'    => $unit,
            ];
        }
    }

    // 6) aggregated totals across all rows
    $totals = [
        'opening' => 0.0,
        'packed'  => 0.0,
        'issued'  => 0.0,
        'closing' => 0.0,
    ];
    foreach ($rows as $r) {
        $totals['opening'] += $r['opening'];
        $totals['packed']  += $r['packed'];
        $totals['issued']  += $r['issued'];
        $totals['closing'] += $r['closing'];
    }

    $workPoints = $isSuper ? WorkPoint::where('company_id', $user->company_id)->where('status','!=','Deleted')->get() : collect();
    $companies = CompanySite::where('id', $user->company_id)->get();

    return view('admin.manfctr.prd_stock_movement', compact(
        'rows','products','start','end','filterProduct','filterWorkPoint','workPoints','companies','totals'
    ));
}// ========================= REQUESTS =========================
public function requestIndex()
{
    $user = auth()->user();

    $requests = RawMaterialRequest::with(['rawMaterial', 'workPoint', 'requester'])
        ->where('company_id', $user->company_id)
        ->where('work_point_id', $user->work_point_id)
        ->orderByDesc('id')
        ->get();

    $raws = RawMaterial::
    // where('company_id', $user->company_id)->
        where('status', '!=', 'Deleted')
        ->orderBy('material_name')
        ->get();

    return view('admin.manfctr.raw_material_requests', compact('requests', 'raws'));
}

public function requestStore(Request $request)
{
    $user = auth()->user();

    $validator = Validator::make($request->all(), [
        'request_date' => ['required', 'date'],
        'raw_material_id' => ['required', 'integer', Rule::exists('raw_materials', 'id')],
        'requested_qty' => ['required', 'numeric', 'gt:0'],
        'unit_name' => ['nullable', 'string', 'max:100'],
        'no_of_bags' => ['nullable', 'integer', 'min:0'],
        'bag_size' => ['nullable', 'numeric', 'min:0'],
        'remarks' => ['nullable', 'string'],
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    RawMaterialRequest::create([
        'request_no' => 'RMR-' . now()->format('YmdHis'),
        'request_date' => $request->request_date,
        'company_id' => $user->company_id,
        'comp_unit_id' => $user->comp_unit_id,
        'work_point_id' => $user->work_point_id,
        'raw_material_id' => $request->raw_material_id,
        'requested_qty' => $request->requested_qty,
        'issued_qty' => 0,
        'remaining_qty' => $request->requested_qty,
        'unit_name' => $request->unit_name,
        'no_of_bags' => $request->no_of_bags,
        'bag_size' => $request->bag_size,
        'remarks' => $request->remarks,
        'status' => 'Pending',
        'requested_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    Alert::success('Success', 'Raw material request created successfully.');
    return redirect()->route('manfctr.requests.index');
}

public function requestUpdate(Request $request, $id)
{
    $req = RawMaterialRequest::findOrFail(decrypt($id));
    $user = auth()->user();

    if ($req->company_id != $user->company_id || $req->work_point_id != $user->work_point_id) {
        Alert::error('Error', 'Unauthorized.');
        return back();
    }

    if (in_array($req->status, ['Partially Issued', 'Fully Issued'])) {
        Alert::error('Error', 'Issued or partially issued request cannot be edited.');
        return back();
    }

    $validator = Validator::make($request->all(), [
        'request_date' => ['required', 'date'],
        'raw_material_id' => ['required', 'integer', Rule::exists('raw_materials', 'id')],
        'requested_qty' => ['required', 'numeric', 'gt:0'],
        'unit_name' => ['nullable', 'string', 'max:100'],
        'no_of_bags' => ['nullable', 'integer', 'min:0'],
        'bag_size' => ['nullable', 'numeric', 'min:0'],
        'remarks' => ['nullable', 'string'],
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    $newRemaining = (float) $request->requested_qty - (float) $req->issued_qty;
    if ($newRemaining < 0) {
        Alert::error('Error', 'Requested qty cannot be less than already issued qty.');
        return back()->withInput();
    }

    $req->update([
        'request_date' => $request->request_date,
        'raw_material_id' => $request->raw_material_id,
        'requested_qty' => $request->requested_qty,
        'remaining_qty' => $newRemaining,
        'unit_name' => $request->unit_name,
        'no_of_bags' => $request->no_of_bags,
        'bag_size' => $request->bag_size,
        'remarks' => $request->remarks,
        'updated_by' => $user->id,
    ]);

    Alert::success('Success', 'Request updated successfully.');
    return redirect()->route('manfctr.requests.index');
}

public function requestDestroy($id)
{
    $req = RawMaterialRequest::findOrFail(decrypt($id));
    $user = auth()->user();

    if ($req->company_id != $user->company_id || $req->work_point_id != $user->work_point_id) {
        Alert::error('Error', 'Unauthorized.');
        return back();
    }

    if ((float) $req->issued_qty > 0 || in_array($req->status, ['Partially Issued', 'Fully Issued'])) {
        Alert::error('Error', 'Issued or partially issued request cannot be deleted.');
        return back();
    }

    $req->delete();

    Alert::success('Success', 'Request removed successfully.');
    return redirect()->route('manfctr.requests.index');
}

// ========================= RECEIPTS =========================
public function receiptIndex()
{
    $user = auth()->user();

    $receipts = ManufacturingReceipt::with(['rawMaterial', 'request'])
        ->where('company_id', $user->company_id)
        ->where('work_point_id', $user->work_point_id)
        ->orderByDesc('id')
        ->get();

    // Only store-issued records for this manufacturing work point that still have receivable balance
    $issues = RawMaterialIssue::with('material')
        ->where('company_id', $user->company_id)
        ->where('issue_to_work_point_id', $user->work_point_id)
        ->orderByDesc('id')
        ->get();

    $issueOptions = $issues->map(function ($issue) {
        $alreadyReceived = ManufacturingReceipt::where('raw_material_issue_id', $issue->id)->sum('received_qty');
        $remainingToReceive = (float) $issue->issued_qty - (float) $alreadyReceived;

        return [
            'id' => $issue->id,
            'issue_no' => 'ISS-' . $issue->id,
            'material_name' => optional($issue->material)->material_name ?? '-',
            'raw_material_id' => $issue->raw_material_id,
            'request_id' => $issue->manufacturing_request_id,
            'issued_qty' => (float) $issue->issued_qty,
            'remaining_qty' => $remainingToReceive > 0 ? $remainingToReceive : 0,
            'remarks' => $issue->remarks,
        ];
    })->filter(function ($x) {
        return $x['remaining_qty'] > 0;
    })->values();

    return view('admin.manfctr.manufacturing_receipts', compact('receipts', 'issueOptions'));
}

public function receiptStore(Request $request)
{
    $user = auth()->user();

    $validator = Validator::make($request->all(), [
        'receipt_date' => ['required', 'date'],
        'raw_material_issue_id' => ['required', 'integer', Rule::exists('raw_material_issues', 'id')],
        'received_qty' => ['required', 'numeric', 'gt:0'],
        'unit_name' => ['nullable', 'string', 'max:100'],
        'no_of_bags' => ['nullable', 'integer', 'min:0'],
        'bag_size' => ['nullable', 'numeric', 'min:0'],
        'remarks' => ['nullable', 'string'],
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    try {
        DB::transaction(function () use ($request, $user) {
            $issue = RawMaterialIssue::lockForUpdate()->findOrFail($request->raw_material_issue_id);

            if ($issue->company_id != $user->company_id || $issue->issue_to_work_point_id != $user->work_point_id) {
                throw new \Exception('Unauthorized issue receipt.');
            }

            $alreadyReceived = (float) ManufacturingReceipt::where('raw_material_issue_id', $issue->id)->sum('received_qty');
            $remainingToReceive = (float) $issue->issued_qty - $alreadyReceived;

            if ((float) $request->received_qty > $remainingToReceive) {
                throw new \Exception('Received qty cannot exceed remaining issued qty.');
            }

            $receipt = ManufacturingReceipt::create([
                'receipt_no' => 'MRC-' . now()->format('YmdHis'),
                'receipt_date' => $request->receipt_date,
                'company_id' => $user->company_id,
                'comp_unit_id' => $user->comp_unit_id,
                'work_point_id' => $user->work_point_id,
                'raw_material_request_id' => $issue->manufacturing_request_id,
                'raw_material_issue_id' => $issue->id,
                'raw_material_id' => $issue->raw_material_id,
                'received_qty' => $request->received_qty,
                'unit_name' => $request->unit_name,
                'no_of_bags' => $request->no_of_bags,
                'bag_size' => $request->bag_size,
                'received_by' => $user->id,
                'remarks' => $request->remarks,
                'status' => 'Received',
            ]);

            $stock = ManufacturingMaterialStock::lockForUpdate()
                ->where('company_id', $user->company_id)
                ->where('comp_unit_id', $user->comp_unit_id)
                ->where('work_point_id', $user->work_point_id)
                ->where('raw_material_id', $issue->raw_material_id)
                ->first();

            if ($stock) {
                $stock->qty_in += $request->received_qty;
                $stock->balance = $stock->qty_in - $stock->qty_out;
                $stock->status = 'Active';
                $stock->save();
            } else {
                $stock = ManufacturingMaterialStock::create([
                    'company_id' => $user->company_id,
                    'comp_unit_id' => $user->comp_unit_id,
                    'work_point_id' => $user->work_point_id,
                    'raw_material_id' => $issue->raw_material_id,
                    'qty_in' => $request->received_qty,
                    'qty_out' => 0,
                    'balance' => $request->received_qty,
                    'status' => 'Active',
                ]);
            }

            ManufacturingStockMovement::create([
                'movement_date' => $request->receipt_date,
                'company_id' => $user->company_id,
                'comp_unit_id' => $user->comp_unit_id,
                'work_point_id' => $user->work_point_id,
                'raw_material_id' => $issue->raw_material_id,
                'reference_type' => 'Receipt',
                'reference_id' => $receipt->id,
                'qty_in' => $request->received_qty,
                'qty_out' => 0,
                'balance_after' => $stock->balance,
                'remarks' => $request->remarks,
                'created_by' => $user->id,
            ]);
        });

        Alert::success('Success', 'Manufacturing receipt saved successfully.');
        return redirect()->route('manfctr.receipts.index');
    } catch (\Throwable $e) {
        Alert::error('Error', $e->getMessage() ?: 'Failed to save receipt.');
        return back()->withInput();
    }
}

public function receiptUpdate(Request $request, $id)
{
    $user = auth()->user();

    $validator = Validator::make($request->all(), [
        'receipt_date' => ['required', 'date'],
        'received_qty' => ['required', 'numeric', 'gt:0'],
        'unit_name' => ['nullable', 'string', 'max:100'],
        'no_of_bags' => ['nullable', 'integer', 'min:0'],
        'bag_size' => ['nullable', 'numeric', 'min:0'],
        'remarks' => ['nullable', 'string'],
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    try {
        DB::transaction(function () use ($request, $id, $user) {
            $receipt = ManufacturingReceipt::lockForUpdate()->findOrFail(decrypt($id));

            if ($receipt->company_id != $user->company_id || $receipt->work_point_id != $user->work_point_id) {
                throw new \Exception('Unauthorized receipt update.');
            }

            $issue = RawMaterialIssue::lockForUpdate()->findOrFail($receipt->raw_material_issue_id);

            $otherReceived = (float) ManufacturingReceipt::where('raw_material_issue_id', $issue->id)
                ->where('id', '!=', $receipt->id)
                ->sum('received_qty');

            $remainingAllowed = (float) $issue->issued_qty - $otherReceived;

            if ((float) $request->received_qty > $remainingAllowed) {
                throw new \Exception('Updated received qty cannot exceed remaining issued qty.');
            }

            $stock = ManufacturingMaterialStock::lockForUpdate()
                ->where('company_id', $receipt->company_id)
                ->where('comp_unit_id', $receipt->comp_unit_id)
                ->where('work_point_id', $receipt->work_point_id)
                ->where('raw_material_id', $receipt->raw_material_id)
                ->firstOrFail();

            // reverse old receipt effect
            $stock->qty_in -= (float) $receipt->received_qty;
            if ($stock->qty_in < 0) {
                $stock->qty_in = 0;
            }

            // apply new receipt effect
            $stock->qty_in += (float) $request->received_qty;
            $stock->balance = $stock->qty_in - $stock->qty_out;
            if ($stock->balance < 0) {
                throw new \Exception('Receipt update would make stock balance invalid.');
            }
            $stock->save();

            $receipt->update([
                'receipt_date' => $request->receipt_date,
                'received_qty' => $request->received_qty,
                'unit_name' => $request->unit_name,
                'no_of_bags' => $request->no_of_bags,
                'bag_size' => $request->bag_size,
                'remarks' => $request->remarks,
            ]);

            $movement = ManufacturingStockMovement::where('reference_type', 'Receipt')
                ->where('reference_id', $receipt->id)
                ->first();

            if ($movement) {
                $movement->update([
                    'movement_date' => $request->receipt_date,
                    'qty_in' => $request->received_qty,
                    'qty_out' => 0,
                    'balance_after' => $stock->balance,
                    'remarks' => $request->remarks,
                ]);
            }
        });

        Alert::success('Success', 'Manufacturing receipt updated successfully.');
        return redirect()->route('manfctr.receipts.index');
    } catch (\Throwable $e) {
        Alert::error('Error', $e->getMessage() ?: 'Failed to update receipt.');
        return back()->withInput();
    }
}

public function receiptDestroy($id)
{
    Alert::error('Error', 'For stock consistency, receipt delete is disabled.');
    return back();
}

// ========================= STOCK =========================
public function stockIndex()
{
    $user = auth()->user();

    $stocks = ManufacturingMaterialStock::with(['rawMaterial'])
        ->where('company_id', $user->company_id)
        ->where('work_point_id', $user->work_point_id)
        ->orderByDesc('id')
        ->get();

    return view('admin.manfctr.manufacturing_stock', compact('stocks'));
}

// ========================= STOCK MOVEMENT =========================
public function stockMovementIndex(Request $request)
{
    $user = auth()->user();

    $start = $request->start_date ?? now()->startOfMonth()->toDateString();
    $end = $request->end_date ?? now()->toDateString();
    $filterRaw = $request->raw_id;

    $raws = RawMaterial::where('company_id', $user->company_id)
        ->where('status', '!=', 'Deleted')
        ->orderBy('material_name')
        ->get();

    $rows = ManufacturingStockMovement::with('rawMaterial')
        ->where('company_id', $user->company_id)
        ->where('work_point_id', $user->work_point_id)
        ->when($filterRaw, function ($q) use ($filterRaw) {
            $q->where('raw_material_id', $filterRaw);
        })
        ->whereBetween('movement_date', [$start, $end])
        ->orderBy('movement_date')
        ->orderBy('id')
        ->get();

    return view('admin.manfctr.manufacturing_stock_movement', compact('rows', 'raws', 'start', 'end', 'filterRaw'));
}

// ========================= CONSUMPTION =========================
public function consumptionIndex()
{
    $user = auth()->user();

    $consumptions = ManufacturingMaterialConsumption::with('rawMaterial')
        ->where('company_id', $user->company_id)
        ->where('work_point_id', $user->work_point_id)
        ->orderByDesc('id')
        ->get();

    $stocks = ManufacturingMaterialStock::with('rawMaterial')
        ->where('company_id', $user->company_id)
        ->where('work_point_id', $user->work_point_id)
        ->where('balance', '>', 0)
        ->orderBy('id')
        ->get();

    return view('admin.manfctr.manufacturing_consumption', compact('consumptions', 'stocks'));
}

public function consumptionStore(Request $request)
{
    $user = auth()->user();

    $validator = Validator::make($request->all(), [
        'consumption_date' => ['required', 'date'],
        'raw_material_id' => ['required', 'integer'],
        'consumed_qty' => ['required', 'numeric', 'gt:0'],
        'unit_name' => ['nullable', 'string', 'max:100'],
        'remarks' => ['nullable', 'string'],
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    try {
        DB::transaction(function () use ($request, $user) {
            $stock = ManufacturingMaterialStock::lockForUpdate()
                ->where('company_id', $user->company_id)
                ->where('comp_unit_id', $user->comp_unit_id)
                ->where('work_point_id', $user->work_point_id)
                ->where('raw_material_id', $request->raw_material_id)
                ->firstOrFail();

            if ((float) $request->consumed_qty > (float) $stock->balance) {
                throw new \Exception('Consumed qty cannot exceed stock balance.');
            }

            $cons = ManufacturingMaterialConsumption::create([
                'consumption_date' => $request->consumption_date,
                'company_id' => $user->company_id,
                'comp_unit_id' => $user->comp_unit_id,
                'work_point_id' => $user->work_point_id,
                'raw_material_id' => $request->raw_material_id,
                'consumed_qty' => $request->consumed_qty,
                'unit_name' => $request->unit_name,
                'remarks' => $request->remarks,
                'created_by' => $user->id,
                'status' => 'Consumed',
            ]);

            $stock->qty_out += $request->consumed_qty;
            $stock->balance = $stock->qty_in - $stock->qty_out;
            $stock->save();

            ManufacturingStockMovement::create([
                'movement_date' => $request->consumption_date,
                'company_id' => $user->company_id,
                'comp_unit_id' => $user->comp_unit_id,
                'work_point_id' => $user->work_point_id,
                'raw_material_id' => $request->raw_material_id,
                'reference_type' => 'Consumption',
                'reference_id' => $cons->id,
                'qty_in' => 0,
                'qty_out' => $request->consumed_qty,
                'balance_after' => $stock->balance,
                'remarks' => $request->remarks,
                'created_by' => $user->id,
            ]);
        });

        Alert::success('Success', 'Manufacturing consumption saved successfully.');
        return redirect()->route('manfctr.consumption.index');
    } catch (\Throwable $e) {
        Alert::error('Error', $e->getMessage() ?: 'Failed to save consumption.');
        return back()->withInput();
    }
}

public function consumptionUpdate(Request $request, $id)
{
    $user = auth()->user();

    $validator = Validator::make($request->all(), [
        'consumption_date' => ['required', 'date'],
        'consumed_qty' => ['required', 'numeric', 'gt:0'],
        'unit_name' => ['nullable', 'string', 'max:100'],
        'remarks' => ['nullable', 'string'],
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    try {
        DB::transaction(function () use ($request, $id, $user) {
            $cons = ManufacturingMaterialConsumption::lockForUpdate()->findOrFail(decrypt($id));

            if ($cons->company_id != $user->company_id || $cons->work_point_id != $user->work_point_id) {
                throw new \Exception('Unauthorized consumption update.');
            }

            $stock = ManufacturingMaterialStock::lockForUpdate()
                ->where('company_id', $cons->company_id)
                ->where('comp_unit_id', $cons->comp_unit_id)
                ->where('work_point_id', $cons->work_point_id)
                ->where('raw_material_id', $cons->raw_material_id)
                ->firstOrFail();

            // reverse old consumption
            $stock->qty_out -= (float) $cons->consumed_qty;
            if ($stock->qty_out < 0) {
                $stock->qty_out = 0;
            }

            // check new consumption against available after reverse
            $availableAfterReverse = (float) $stock->qty_in - (float) $stock->qty_out;

            if ((float) $request->consumed_qty > $availableAfterReverse) {
                throw new \Exception('Updated consumed qty cannot exceed available stock.');
            }

            // apply new consumption
            $stock->qty_out += (float) $request->consumed_qty;
            $stock->balance = $stock->qty_in - $stock->qty_out;
            if ($stock->balance < 0) {
                throw new \Exception('Consumption update would make stock balance invalid.');
            }
            $stock->save();

            $cons->update([
                'consumption_date' => $request->consumption_date,
                'consumed_qty' => $request->consumed_qty,
                'unit_name' => $request->unit_name,
                'remarks' => $request->remarks,
            ]);

            $movement = ManufacturingStockMovement::where('reference_type', 'Consumption')
                ->where('reference_id', $cons->id)
                ->first();

            if ($movement) {
                $movement->update([
                    'movement_date' => $request->consumption_date,
                    'qty_in' => 0,
                    'qty_out' => $request->consumed_qty,
                    'balance_after' => $stock->balance,
                    'remarks' => $request->remarks,
                ]);
            }
        });
        Alert::success('Success', 'Manufacturing consumption updated successfully.');
        return redirect()->route('manfctr.consumption.index');
    } catch (\Throwable $e) {
        Alert::error('Error', $e->getMessage() ?: 'Failed to update consumption.');
        return back()->withInput();
    }
}
public function consumptionDestroy($id)
{
    Alert::error('Error', 'For stock consistency, consumption delete is disabled.');
    return back();
}
}
