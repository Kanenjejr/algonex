<?php

namespace App\Http\Controllers;

use App\Models\CompanySite;
use App\Models\Company_unit;
use App\Models\Customer;
use App\Models\LogisticDriver;
use App\Models\LogisticEscort;
use App\Models\LogisticFleetVehicle;
use App\Models\LogisticTransportCost;
use App\Models\LogisticTransportOrder;
use App\Models\WorkPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class LogisticsController extends Controller
{
    private array $superRoles = ['Admin', 'Super Admin', 'CEO', 'Managing Director', 'Managing Director (MD)'];

    private function isSuperUser(): bool
    {
        return in_array(optional(auth()->user())->role, $this->superRoles, true);
    }

    private function scopeCompany($query)
    {
        if (!$this->isSuperUser()) {
            $query->where('company_id', auth()->user()->company_id);
        }
        return $query;
    }

    private function commonData(): array
    {
        $companies = CompanySite::query()->where('status', 'Active')->orderBy('company_name')->get();
        $selectedCompanyId = request('company_id');

        if ($selectedCompanyId) {
            $currentCompany = CompanySite::find($selectedCompanyId);
            $currentUnit = Company_unit::where('company_id', $selectedCompanyId)->orderBy('unit_name')->first();
            $currentWorkPoint = WorkPoint::where('company_id', $selectedCompanyId)->orderBy('work_name')->first();
        } else {
            $currentCompany = $this->isSuperUser() ? $companies->first() : CompanySite::find(auth()->user()->company_id);
            $currentUnit = $currentCompany ? Company_unit::where('company_id', $currentCompany->id)->orderBy('unit_name')->first() : null;
            $currentWorkPoint = $currentUnit ? WorkPoint::where('company_id', $currentCompany->id)->where('comp_unit_id', $currentUnit->id)->orderBy('work_name')->first() : null;
        }

        return compact('companies', 'currentCompany', 'currentUnit', 'currentWorkPoint');
    }

    private function normalize(array $data): array
    {
        foreach ($data as $key => $value) {
            if ($value === '') {
                $data[$key] = null;
            }
        }
        return $data;
    }

    private function decryptId(string $id): int
    {
        return (int) Crypt::decryptString($id);
    }

    private function syncCosting(LogisticTransportOrder $order, array $data): LogisticTransportCost
    {
        $hire = (float) ($data['hired_vehicle_cost'] ?? 0);
        $fuel = ((float) ($data['expected_fuel_liters'] ?? 0)) * ((float) ($data['fuel_rate'] ?? 0));
        $driver = (float) ($data['driver_allowance'] ?? 0);
        $escort = (float) ($data['escort_allowance'] ?? 0);
        $loading = (float) ($data['loading_cost'] ?? 0);
        $other = (float) ($data['other_cost'] ?? 0);
        $revenue = (float) ($data['revenue_amount'] ?? 0);

        $total = $hire + $fuel + $driver + $escort + $loading + $other;
        $profit = $revenue - $total;

        return LogisticTransportCost::updateOrCreate(
            ['transport_order_id' => $order->id],
            [
                'cost_no' => 'CST-' . $order->order_no,
                'cost_date' => $data['order_date'] ?? now()->toDateString(),
                'vehicle_source' => $data['vehicle_source'] ?? 'company',
                'hire_cost' => $hire,
                'fuel_cost' => $fuel,
                'driver_allowance' => $driver,
                'escort_allowance' => $escort,
                'loading_cost' => $loading,
                'other_cost' => $other,
                'total_cost' => $total,
                'profit' => $profit,
                'company_id' => $data['company_id'] ?? $order->company_id,
                'comp_unit_id' => $data['comp_unit_id'] ?? $order->comp_unit_id,
                'work_point_id' => $data['work_point_id'] ?? $order->work_point_id,
                'status' => $data['status'] ?? 'Active',
                'remarks' => $data['remarks'] ?? null,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]
        );
    }

    public function dashboard()
    {
        extract($this->commonData());

        $totals = [
            'orders' => $this->scopeCompany(LogisticTransportOrder::query())->count(),
            'vehicles' => $this->scopeCompany(LogisticFleetVehicle::query())->count(),
            'drivers' => $this->scopeCompany(LogisticDriver::query())->count(),
            'escorts' => $this->scopeCompany(LogisticEscort::query())->count(),
            'costs' => $this->scopeCompany(LogisticTransportCost::query())->count(),
        ];

        $latestOrders = $this->scopeCompany(LogisticTransportOrder::query())->latest()->take(5)->get();

        return view('admin.logistics.dashboard', compact(
            'companies','currentCompany','currentUnit','currentWorkPoint','totals','latestOrders'
        ) + [
            'isSuper' => $this->isSuperUser(),
            'pageTitle' => 'Logistics Dashboard',
        ]);
    }

    public function ordersIndex()
    {
        extract($this->commonData());

        $records = $this->scopeCompany(LogisticTransportOrder::query())
            ->with(['company','compUnit','workPoint','customer','vehicle','driver','costing'])
            ->latest()
            ->get();

        $customers = Customer::query()
            ->where('status', 'Active')
            ->when(!$this->isSuperUser(), fn($q) => $q->where('company_id', auth()->user()->company_id))
            ->orderBy('customer_name')
            ->get();

        $vehicles = $this->scopeCompany(LogisticFleetVehicle::query())
            ->where('status', 'Active')
            ->orderBy('plate_number')
            ->get();

        $drivers = $this->scopeCompany(LogisticDriver::query())
            ->where('status', 'Active')
            ->orderBy('first_name')
            ->get();

        $escorts = $this->scopeCompany(LogisticEscort::query())
            ->where('status', 'Active')
            ->orderBy('full_name')
            ->get();

        return view('admin.logistics.orders', compact(
            'companies','currentCompany','currentUnit','currentWorkPoint',
            'records','customers','vehicles','drivers','escorts'
        ) + [
            'isSuper' => $this->isSuperUser(),
            'pageTitle' => 'Transport Orders',
            'record' => null,
        ]);
    }

    public function ordersCreate()
    {
        return $this->ordersIndex();
    }

    public function ordersStore(Request $request)
    {
        $data = $request->validate([
            'order_no' => 'required|string|max:255|unique:logistic_transport_orders,order_no',
            'order_date' => 'required|date',
            'company_id' => 'required|exists:company_sites,id',
            'comp_unit_id' => 'required|exists:company_units,id',
            'work_point_id' => 'required|exists:work_points,id',
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'required|string|max:255',
            'cargo_description' => 'required|string|max:255',
            'origin' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'vehicle_source' => 'required|in:company,hired',
            'company_vehicle_id' => 'nullable|exists:logistic_fleet_vehicles,id',
            'hired_vehicle_name' => 'nullable|string|max:255',
            'hired_vehicle_plate' => 'nullable|string|max:255',
            'hired_vehicle_cost' => 'nullable|numeric|min:0',
            'driver_id' => 'nullable|exists:logistic_drivers,id',
            'escort_name' => 'nullable|string|max:255',
            'escort_allowance' => 'nullable|numeric|min:0',
            'driver_allowance' => 'nullable|numeric|min:0',
            'expected_fuel_liters' => 'nullable|numeric|min:0',
            'fuel_rate' => 'nullable|numeric|min:0',
            'revenue_amount' => 'nullable|numeric|min:0',
            'status' => 'required|string|max:20',
            'remarks' => 'nullable|string',
        ]);

        $data = $this->normalize($data);
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        DB::transaction(function () use (&$order, $data) {
            $order = LogisticTransportOrder::create($data);
            $this->syncCosting($order, $data);
        });

        Alert::success('Success', 'Transport order saved successfully.');
        return redirect()->route('logistics.orders');
    }

    public function ordersShow(string $id)
    {
        $record = LogisticTransportOrder::with(['company','compUnit','workPoint','customer','vehicle','driver','costing'])
            ->findOrFail($this->decryptId($id));

        return view('admin.logistics.orders_show', [
            'record' => $record,
            'pageTitle' => 'Transport Order Details',
        ]);
    }

    public function ordersEdit(string $id)
    {
        $record = LogisticTransportOrder::findOrFail($this->decryptId($id));

        extract($this->commonData());

        $records = $this->scopeCompany(LogisticTransportOrder::query())
            ->with(['company','compUnit','workPoint','customer','vehicle','driver','costing'])
            ->latest()
            ->get();

        $customers = Customer::query()
            ->where('status', 'Active')
            ->when(!$this->isSuperUser(), fn($q) => $q->where('company_id', auth()->user()->company_id))
            ->orderBy('customer_name')
            ->get();

        $vehicles = $this->scopeCompany(LogisticFleetVehicle::query())
            ->where('status', 'Active')
            ->orderBy('plate_number')
            ->get();

        $drivers = $this->scopeCompany(LogisticDriver::query())
            ->where('status', 'Active')
            ->orderBy('first_name')
            ->get();

        $escorts = $this->scopeCompany(LogisticEscort::query())
            ->where('status', 'Active')
            ->orderBy('full_name')
            ->get();

        return view('admin.logistics.orders', compact(
            'companies','currentCompany','currentUnit','currentWorkPoint',
            'records','customers','vehicles','drivers','escorts','record'
        ) + [
            'isSuper' => $this->isSuperUser(),
            'pageTitle' => 'Edit Transport Order',
        ]);
    }

    public function ordersUpdate(Request $request, string $id)
    {
        $record = LogisticTransportOrder::findOrFail($this->decryptId($id));

        $data = $request->validate([
            'order_no' => 'required|string|max:255|unique:logistic_transport_orders,order_no,' . $record->id,
            'order_date' => 'required|date',
            'company_id' => 'required|exists:company_sites,id',
            'comp_unit_id' => 'required|exists:company_units,id',
            'work_point_id' => 'required|exists:work_points,id',
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'required|string|max:255',
            'cargo_description' => 'required|string|max:255',
            'origin' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'vehicle_source' => 'required|in:company,hired',
            'company_vehicle_id' => 'nullable|exists:logistic_fleet_vehicles,id',
            'hired_vehicle_name' => 'nullable|string|max:255',
            'hired_vehicle_plate' => 'nullable|string|max:255',
            'hired_vehicle_cost' => 'nullable|numeric|min:0',
            'driver_id' => 'nullable|exists:logistic_drivers,id',
            'escort_name' => 'nullable|string|max:255',
            'escort_allowance' => 'nullable|numeric|min:0',
            'driver_allowance' => 'nullable|numeric|min:0',
            'expected_fuel_liters' => 'nullable|numeric|min:0',
            'fuel_rate' => 'nullable|numeric|min:0',
            'revenue_amount' => 'nullable|numeric|min:0',
            'status' => 'required|string|max:20',
            'remarks' => 'nullable|string',
        ]);

        $data = $this->normalize($data);
        $data['updated_by'] = auth()->id();

        DB::transaction(function () use ($record, $data) {
            $record->update($data);
            $this->syncCosting($record, $data);
        });

        Alert::success('Success', 'Transport order updated successfully.');
        return redirect()->route('logistics.orders');
    }

    public function ordersDestroy(string $id)
    {
        LogisticTransportOrder::findOrFail($this->decryptId($id))->delete();
        Alert::success('Success', 'Transport order deleted successfully.');
        return redirect()->route('logistics.orders');
    }

    public function fleetIndex()
    {
        extract($this->commonData());

        $vehicles = $this->scopeCompany(LogisticFleetVehicle::query())->latest()->get();
        $drivers = $this->scopeCompany(LogisticDriver::query())->latest()->get();
        $escorts = $this->scopeCompany(LogisticEscort::query())->latest()->get();

        return view('admin.logistics.fleet', compact(
            'companies','currentCompany','currentUnit','currentWorkPoint','vehicles','drivers','escorts'
        ) + [
            'isSuper' => $this->isSuperUser(),
            'pageTitle' => 'Fleet Management',
        ]);
    }

    public function fleetVehicleStore(Request $request)
    {
        $data = $request->validate([
            'vehicle_code' => 'required|string|max:255|unique:logistic_fleet_vehicles,vehicle_code',
            'plate_number' => 'required|string|max:255|unique:logistic_fleet_vehicles,plate_number',
            'vehicle_type' => 'required|string|max:255',
            'ownership' => 'required|in:company,hired',
            'make' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'manufacture_year' => 'nullable|integer',
            'fuel_type' => 'nullable|string|max:255',
            'fuel_rate_per_liter' => 'nullable|numeric|min:0',
            'hire_rate_per_day' => 'nullable|numeric|min:0',
            'capacity' => 'nullable|numeric|min:0',
            'company_id' => 'required|exists:company_sites,id',
            'comp_unit_id' => 'required|exists:company_units,id',
            'work_point_id' => 'required|exists:work_points,id',
            'status' => 'required|string|max:20',
            'remarks' => 'nullable|string',
        ]);

        LogisticFleetVehicle::create($this->normalize($data + [
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]));

        Alert::success('Success', 'Vehicle saved successfully.');
        return back();
    }

    public function fleetVehicleUpdate(Request $request, string $id)
    {
        $record = LogisticFleetVehicle::findOrFail($this->decryptId($id));

        $data = $request->validate([
            'vehicle_code' => 'required|string|max:255|unique:logistic_fleet_vehicles,vehicle_code,' . $record->id,
            'plate_number' => 'required|string|max:255|unique:logistic_fleet_vehicles,plate_number,' . $record->id,
            'vehicle_type' => 'required|string|max:255',
            'ownership' => 'required|in:company,hired',
            'make' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'manufacture_year' => 'nullable|integer',
            'fuel_type' => 'nullable|string|max:255',
            'fuel_rate_per_liter' => 'nullable|numeric|min:0',
            'hire_rate_per_day' => 'nullable|numeric|min:0',
            'capacity' => 'nullable|numeric|min:0',
            'company_id' => 'required|exists:company_sites,id',
            'comp_unit_id' => 'required|exists:company_units,id',
            'work_point_id' => 'required|exists:work_points,id',
            'status' => 'required|string|max:20',
            'remarks' => 'nullable|string',
        ]);

        $record->update($this->normalize($data + [
            'updated_by' => auth()->id(),
        ]));

        Alert::success('Success', 'Vehicle updated successfully.');
        return back();
    }

    public function fleetVehicleDestroy(string $id)
    {
        LogisticFleetVehicle::findOrFail($this->decryptId($id))->delete();
        Alert::success('Success', 'Vehicle deleted successfully.');
        return back();
    }

    public function driverStore(Request $request)
    {
        $data = $request->validate([
            'driver_code' => 'required|string|max:255|unique:logistic_drivers,driver_code',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'license_no' => 'nullable|string|max:255',
            'allowance_rate' => 'nullable|numeric|min:0',
            'company_id' => 'required|exists:company_sites,id',
            'comp_unit_id' => 'required|exists:company_units,id',
            'work_point_id' => 'required|exists:work_points,id',
            'status' => 'required|string|max:20',
            'remarks' => 'nullable|string',
        ]);

        LogisticDriver::create($this->normalize($data + [
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]));

        Alert::success('Success', 'Driver saved successfully.');
        return back();
    }

    public function driverUpdate(Request $request, string $id)
    {
        $record = LogisticDriver::findOrFail($this->decryptId($id));

        $data = $request->validate([
            'driver_code' => 'required|string|max:255|unique:logistic_drivers,driver_code,' . $record->id,
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'license_no' => 'nullable|string|max:255',
            'allowance_rate' => 'nullable|numeric|min:0',
            'company_id' => 'required|exists:company_sites,id',
            'comp_unit_id' => 'required|exists:company_units,id',
            'work_point_id' => 'required|exists:work_points,id',
            'status' => 'required|string|max:20',
            'remarks' => 'nullable|string',
        ]);

        $record->update($this->normalize($data + [
            'updated_by' => auth()->id(),
        ]));

        Alert::success('Success', 'Driver updated successfully.');
        return back();
    }

    public function driverDestroy(string $id)
    {
        LogisticDriver::findOrFail($this->decryptId($id))->delete();
        Alert::success('Success', 'Driver deleted successfully.');
        return back();
    }

    public function escortStore(Request $request)
    {
        $data = $request->validate([
            'escort_code' => 'required|string|max:255|unique:logistic_escorts,escort_code',
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'allowance_rate' => 'nullable|numeric|min:0',
            'company_id' => 'required|exists:company_sites,id',
            'comp_unit_id' => 'required|exists:company_units,id',
            'work_point_id' => 'required|exists:work_points,id',
            'status' => 'required|string|max:20',
            'remarks' => 'nullable|string',
        ]);

        LogisticEscort::create($this->normalize($data + [
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]));

        Alert::success('Success', 'Escort saved successfully.');
        return back();
    }

    public function escortUpdate(Request $request, string $id)
    {
        $record = LogisticEscort::findOrFail($this->decryptId($id));

        $data = $request->validate([
            'escort_code' => 'required|string|max:255|unique:logistic_escorts,escort_code,' . $record->id,
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'allowance_rate' => 'nullable|numeric|min:0',
            'company_id' => 'required|exists:company_sites,id',
            'comp_unit_id' => 'required|exists:company_units,id',
            'work_point_id' => 'required|exists:work_points,id',
            'status' => 'required|string|max:20',
            'remarks' => 'nullable|string',
        ]);

        $record->update($this->normalize($data + [
            'updated_by' => auth()->id(),
        ]));

        Alert::success('Success', 'Escort updated successfully.');
        return back();
    }

    public function escortDestroy(string $id)
    {
        LogisticEscort::findOrFail($this->decryptId($id))->delete();
        Alert::success('Success', 'Escort deleted successfully.');
        return back();
    }

    public function costingIndex()
    {
        extract($this->commonData());

        $records = $this->scopeCompany(LogisticTransportCost::query())
            ->with(['order'])
            ->latest()
            ->get();

        return view('admin.logistics.costing', compact(
            'companies','currentCompany','currentUnit','currentWorkPoint','records'
        ) + [
            'isSuper' => $this->isSuperUser(),
            'pageTitle' => 'Transport Costing & Analysis',
        ]);
    }

    public function costingRecalculate(string $orderId)
    {
        $order = LogisticTransportOrder::findOrFail($this->decryptId($orderId));
        $this->syncCosting($order, $order->toArray());
        Alert::success('Success', 'Costing recalculated successfully.');
        return back();
    }

    public function getCompanyUnits($company_id)
    {
        return response()->json(
            Company_unit::where('company_id', $company_id)->orderBy('unit_name')->get(['id', 'unit_name'])
        );
    }

    public function getWorkPoints($company_id, $unit_id)
    {
        return response()->json(
            WorkPoint::where('company_id', $company_id)
                ->where('comp_unit_id', $unit_id)
                ->orderBy('work_name')
                ->get(['id', 'work_name', 'location'])
        );
    }
}
