<?php

namespace App\Http\Controllers;

use App\Models\CompanySite;
use App\Models\Company_unit;

use App\Models\DrillingBlasting;
use App\Models\WorkPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use RealRashid\SweetAlert\Facades\Alert;

class DrillingBlastingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function isSuperRole(): bool
    {
        return in_array(optional(auth()->user())->role, [
            'Admin',
            'CEO',
            'Admin-Developer',
            'Managing Director (MD)',
        ], true);
    }

    protected function allowedCompanies()
    {
        $user = auth()->user();

        if ($this->isSuperRole()) {
            return CompanySite::where('status', '!=', 'Deleted')
                ->orderBy('company_name')
                ->get();
        }

        return CompanySite::where('id', $user->company_id)
            ->where('status', '!=', 'Deleted')
            ->orderBy('company_name')
            ->get();
    }

    protected function allowedCompanyUnits($companyId = null)
    {
        $user = auth()->user();

        if ($this->isSuperRole()) {
            return Company_unit::where('status', '!=', 'Deleted')
                ->when($companyId, function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })
                ->orderBy('unit_name')
                ->get();
        }

        return Company_unit::where('company_id', $user->company_id)
            ->where('status', '!=', 'Deleted')
            ->orderBy('unit_name')
            ->get();
    }

    protected function allowedWorkPoints($companyId = null, $unitId = null)
    {
        $user = auth()->user();

        if ($this->isSuperRole()) {
            return WorkPoint::where('status', '!=', 'Deleted')
                ->when($companyId, function ($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })
                ->when($unitId, function ($q) use ($unitId) {
                    $q->where('comp_unit_id', $unitId);
                })
                ->orderBy('work_name')
                ->get();
        }

        return WorkPoint::where('company_id', $user->company_id)
            ->where('comp_unit_id', $user->comp_unit_id)
            ->where('status', '!=', 'Deleted')
            ->orderBy('work_name')
            ->get();
    }

        protected function scopedQuery($user)
        {
            $query = DrillingBlasting::with([
                'company',
                'companyUnit',
                'workPoint',
                'creator',
                'updater',
            ])->where('status', '!=', 'Deleted');

            if ($this->isSuperRole()) {
                return $query;
            }

            return $query->where('company_id', $user->company_id);
        }

    protected function resolveCompanyId(Request $request): int
    {
        $user = auth()->user();

        return $this->isSuperRole()
            ? (int) $request->company_id
            : (int) $user->company_id;
    }

    protected function resolveUnitId(Request $request): int
    {
        $user = auth()->user();

        return $this->isSuperRole()
            ? (int) $request->comp_unit_id
            : (int) $user->comp_unit_id;
    }

    protected function resolveWorkPointId(Request $request): int
    {
        $user = auth()->user();

        return $this->isSuperRole()
            ? (int) $request->work_point_id
            : (int) $user->work_point_id;
    }

    public function index()
    {
        $user = auth()->user();

        $records = DrillingBlasting::with([
            'company',
            'companyUnit',
            'workPoint'
        ])
        ->where('status', '!=', 'Deleted')
        ->latest('id')
        ->get();

        $totals = [
            'records' => $records->count(),
            'blasts' => (int) $records->sum('blasts_conducted'),
            'holes' => (int) $records->sum('total_holes_charged'),
            'explosive_qty' => (float) $records->sum('explosive_qty'),
            'detonators_qty' => (float) $records->sum('detonators_qty'),
            'cord_m' => (float) $records->sum('detonating_cord_m'),
            'booster_qty' => (float) $records->sum('booster_qty'),
            'rock' => (float) $records->sum('total_rock_blasted'),
        ];

        $companies = $this->allowedCompanies();
        $companyUnits = $this->allowedCompanyUnits();
        $workPoints = $this->allowedWorkPoints();

        return view('admin.products.blasting', compact(
            'records',
            'totals',
            'companies',
            'companyUnits',
            'workPoints'
        ));
    }

 public function show($id)
        {
        $user = auth()->user();

            try {
                $id = Crypt::decryptString($id);

            } catch (\Throwable $e) {

                \Log::error('DrillingBlasting Update Decryption Failed', [
                    'function' => 'update',
                    'encrypted_id' => $id,
                    'error' => $e->getMessage(),
                    'user_id' => auth()->id(),
                ]);

                Alert::error(
                    'Error',
                    'Invalid or corrupted record identifier.'
                );

                return back();
            }
            $record = DrillingBlasting::with([
                'company',
                'companyUnit',
                'workPoint',
                'creator',
                'updater'
            ])->findOrFail($id);

            if (!$this->isSuperRole() && $record->company_id != $user->company_id) {
                Alert::error('Unauthorized', 'You cannot view this record.');
                return redirect()->route('production.drilling-blasting.index');
            }

            return view('admin.products.show_blasting', compact('record'));
         $records = $this->scopedQuery($user)
            ->latest('record_date')
            ->latest('id')
            ->get();

        $totals = [
            'records' => $records->count(),
            'blasts' => (int) $records->sum('blasts_conducted'),
            'holes' => (int) $records->sum('total_holes_charged'),
            'explosive_qty' => (float) $records->sum('explosive_qty'),
            'detonators_qty' => (float) $records->sum('detonators_qty'),
            'cord_m' => (float) $records->sum('detonating_cord_m'),
            'booster_qty' => (float) $records->sum('booster_qty'),
            'rock' => (float) $records->sum('total_rock_blasted'),
        ];

        $companies = $this->allowedCompanies();
        $companyUnits = $this->allowedCompanyUnits($record->company_id);
        $workPoints = $this->allowedWorkPoints($record->company_id, $record->comp_unit_id);

        return view('admin.products.blasting', compact(
            'records',
            'totals',
            'companies',
            'companyUnits',
            'workPoints',
            'record'
        ));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $companyId = $this->resolveCompanyId($request);
        $unitId = $this->resolveUnitId($request);
        $workPointId = $this->resolveWorkPointId($request);

        $rules = [
            'company_id' => $this->isSuperRole()
                ? ['required', 'integer', Rule::exists('company_sites', 'id')->where(function ($q) {
                    $q->where('status', '!=', 'Deleted');
                })]
                : ['nullable'],

            'comp_unit_id' => $this->isSuperRole()
                ? [
                    'required',
                    'integer',
                    Rule::exists('company_units', 'id')->where(function ($q) use ($companyId) {
                        $q->where('company_id', $companyId)
                            ->where('status', '!=', 'Deleted');
                    }),
                ]
                : ['nullable'],

            'work_point_id' => $this->isSuperRole()
                ? [
                    'required',
                    'integer',
                    Rule::exists('work_points', 'id')->where(function ($q) use ($companyId, $unitId) {
                        $q->where('company_id', $companyId)
                            ->where('comp_unit_id', $unitId)
                            ->where('status', '!=', 'Deleted');
                    }),
                ]
                : ['nullable'],

            'record_date' => ['required', 'date'],
            'customer_name' => ['required', 'string', 'max:255'],

            // Activity location written by user
            'project_site' => ['required', 'string', 'max:255'],

            'period_from' => ['nullable', 'date'],
            'period_to' => ['nullable', 'date', 'after_or_equal:period_from'],
            'blasts_conducted' => ['nullable', 'integer', 'min:0'],
            'total_holes_charged' => ['nullable', 'integer', 'min:0'],
            'explosive_type' => ['nullable', 'string', 'max:255'],
            'explosive_qty' => ['nullable', 'numeric', 'min:0'],
            'detonators_qty' => ['nullable', 'numeric', 'min:0'],
            'detonating_cord_m' => ['nullable', 'numeric', 'min:0'],
            'booster_qty' => ['nullable', 'numeric', 'min:0'],
            'total_rock_blasted' => ['nullable', 'numeric', 'min:0'],
            'rock_unit' => ['nullable', 'string', 'max:20'],
            'authorized_blaster' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
        ];

        $validated = $request->validate($rules);

        $company = $this->isSuperRole()
            ? CompanySite::findOrFail($validated['company_id'])
            : CompanySite::findOrFail($user->company_id);

        $companyUnit = $this->isSuperRole()
            ? Company_unit::where('id', $validated['comp_unit_id'])
                ->where('company_id', $company->id)
                ->where('status', '!=', 'Deleted')
                ->firstOrFail()
            : Company_unit::where('company_id', $user->company_id)
                ->where('id', $user->comp_unit_id)
                ->where('status', '!=', 'Deleted')
                ->firstOrFail();

        $workPoint = $this->isSuperRole()
            ? WorkPoint::where('id', $validated['work_point_id'])
                ->where('company_id', $company->id)
                ->where('comp_unit_id', $companyUnit->id)
                ->where('status', '!=', 'Deleted')
                ->firstOrFail()
            : WorkPoint::where('company_id', $user->company_id)
                ->where('comp_unit_id', $user->comp_unit_id)
                ->where('id', $user->work_point_id)
                ->where('status', '!=', 'Deleted')
                ->firstOrFail();

        DB::beginTransaction();

        try {
            DrillingBlasting::create([
                'record_no' => 'DBL-' . now()->format('YmdHis'),
                'record_date' => $validated['record_date'],
                'company_id' => $company->id,
                'comp_unit_id' => $companyUnit->id,
                'work_point_id' => $workPoint->id,
                'customer_name' => $validated['customer_name'],
                'project_site' => $validated['project_site'],
                'period_from' => $validated['period_from'] ?? null,
                'period_to' => $validated['period_to'] ?? null,
                'blasts_conducted' => $validated['blasts_conducted'] ?? 0,
                'total_holes_charged' => $validated['total_holes_charged'] ?? 0,
                'explosive_type' => $validated['explosive_type'] ?? null,
                'explosive_qty' => $validated['explosive_qty'] ?? 0,
                'detonators_qty' => $validated['detonators_qty'] ?? 0,
                'detonating_cord_m' => $validated['detonating_cord_m'] ?? 0,
                'booster_qty' => $validated['booster_qty'] ?? 0,
                'total_rock_blasted' => $validated['total_rock_blasted'] ?? 0,
                'rock_unit' => $validated['rock_unit'] ?? 'BCM',
                'authorized_blaster' => $validated['authorized_blaster'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
                'status' => $validated['status'],
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            DB::commit();

            Alert::success('Success', 'Blasting record created successfully.');
            return redirect()->route('production.drilling-blasting.index');
        } catch (\Throwable $e) {
            DB::rollBack();
            Alert::error('Error', $e->getMessage());
            return back()->withInput();
        }
    }

    public function update(Request $request, $id)
    {
       $user = auth()->user();

            try {
                $id = Crypt::decryptString($id);

            } catch (\Throwable $e) {

                \Log::error('DrillingBlasting Update Decryption Failed', [
                    'function' => 'update',
                    'encrypted_id' => $id,
                    'error' => $e->getMessage(),
                    'user_id' => auth()->id(),
                ]);

                Alert::error(
                    'Error',
                    'Invalid or corrupted record identifier.'
                );

                return back();
            }
        
        $record = DrillingBlasting::findOrFail($id);

        if ($record->company_id != $user->company_id && !$this->isSuperRole()) {
            Alert::error('Unauthorized', 'You cannot edit this record.');
            return back();
        }

        $companyId = $this->isSuperRole() ? (int) $request->company_id : (int) $record->company_id;
        $unitId = $this->isSuperRole() ? (int) $request->comp_unit_id : (int) $record->comp_unit_id;

        $rules = [
            'company_id' => $this->isSuperRole()
                ? ['required', 'integer', Rule::exists('company_sites', 'id')->where(function ($q) {
                    $q->where('status', '!=', 'Deleted');
                })]
                : ['nullable'],

            'comp_unit_id' => $this->isSuperRole()
                ? [
                    'required',
                    'integer',
                    Rule::exists('company_units', 'id')->where(function ($q) use ($companyId) {
                        $q->where('company_id', $companyId)
                            ->where('status', '!=', 'Deleted');
                    }),
                ]
                : ['nullable'],

            'work_point_id' => $this->isSuperRole()
                ? [
                    'required',
                    'integer',
                    Rule::exists('work_points', 'id')->where(function ($q) use ($companyId, $unitId) {
                        $q->where('company_id', $companyId)
                            ->where('comp_unit_id', $unitId)
                            ->where('status', '!=', 'Deleted');
                    }),
                ]
                : ['nullable'],

            'record_date' => ['required', 'date'],
            'customer_name' => ['required', 'string', 'max:255'],

            // Activity location written by user
            'project_site' => ['required', 'string', 'max:255'],

            'period_from' => ['nullable', 'date'],
            'period_to' => ['nullable', 'date', 'after_or_equal:period_from'],
            'blasts_conducted' => ['nullable', 'integer', 'min:0'],
            'total_holes_charged' => ['nullable', 'integer', 'min:0'],
            'explosive_type' => ['nullable', 'string', 'max:255'],
            'explosive_qty' => ['nullable', 'numeric', 'min:0'],
            'detonators_qty' => ['nullable', 'numeric', 'min:0'],
            'detonating_cord_m' => ['nullable', 'numeric', 'min:0'],
            'booster_qty' => ['nullable', 'numeric', 'min:0'],
            'total_rock_blasted' => ['nullable', 'numeric', 'min:0'],
            'rock_unit' => ['nullable', 'string', 'max:20'],
            'authorized_blaster' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['Active', 'Inactive', 'Deleted'])],
        ];

        $validated = $request->validate($rules);

        $company = $this->isSuperRole()
            ? CompanySite::findOrFail($validated['company_id'])
            : CompanySite::findOrFail($record->company_id);

        $companyUnit = $this->isSuperRole()
            ? Company_unit::where('id', $validated['comp_unit_id'])
                ->where('company_id', $company->id)
                ->where('status', '!=', 'Deleted')
                ->firstOrFail()
            : Company_unit::where('company_id', $record->company_id)
                ->where('id', $record->comp_unit_id)
                ->where('status', '!=', 'Deleted')
                ->firstOrFail();

        $workPoint = $this->isSuperRole()
            ? WorkPoint::where('id', $validated['work_point_id'])
                ->where('company_id', $company->id)
                ->where('comp_unit_id', $companyUnit->id)
                ->where('status', '!=', 'Deleted')
                ->firstOrFail()
            : WorkPoint::where('company_id', $record->company_id)
                ->where('comp_unit_id', $record->comp_unit_id)
                ->where('id', $record->work_point_id)
                ->where('status', '!=', 'Deleted')
                ->firstOrFail();

        DB::beginTransaction();

        try {
            $record->update([
                'record_date' => $validated['record_date'],
                'company_id' => $company->id,
                'comp_unit_id' => $companyUnit->id,
                'work_point_id' => $workPoint->id,
                'customer_name' => $validated['customer_name'],
                'project_site' => $validated['project_site'],
                'period_from' => $validated['period_from'] ?? null,
                'period_to' => $validated['period_to'] ?? null,
                'blasts_conducted' => $validated['blasts_conducted'] ?? 0,
                'total_holes_charged' => $validated['total_holes_charged'] ?? 0,
                'explosive_type' => $validated['explosive_type'] ?? null,
                'explosive_qty' => $validated['explosive_qty'] ?? 0,
                'detonators_qty' => $validated['detonators_qty'] ?? 0,
                'detonating_cord_m' => $validated['detonating_cord_m'] ?? 0,
                'booster_qty' => $validated['booster_qty'] ?? 0,
                'total_rock_blasted' => $validated['total_rock_blasted'] ?? 0,
                'rock_unit' => $validated['rock_unit'] ?? 'BCM',
                'authorized_blaster' => $validated['authorized_blaster'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
                'status' => $validated['status'],
                'updated_by' => auth()->id(),
            ]);

            DB::commit();

            Alert::success('Success', 'Blasting record updated successfully.');
            return redirect()->route('production.drilling-blasting.index');
        } catch (\Throwable $e) {
            DB::rollBack();
            Alert::error('Error', $e->getMessage());
            return back()->withInput();
        }
    }

    public function destroy($id)
    {
       try {
       $id = Crypt::decryptString($id);
            } catch (\Throwable $e) {

                \Log::error('Failed to decrypt DrillingBlasting ID', [
                    'function' => 'update',
                    'encrypted_id' => $id,
                    'error' => $e->getMessage(),
                    'user_id' => auth()->id(),
                ]);

                Alert::error(
                    'Error',
                    'Invalid or corrupted record identifier.'
                );

                return back();
            }

        $record = DrillingBlasting::findOrFail($id);

        $record->update([
            'status' => 'Deleted'
        ]);

        Alert::success('Success', 'Record deleted successfully');

        return redirect()->route('production.drilling-blasting.index');
    }

    public function getCompanyUnits($companyId)
    {
        $units = Company_unit::where('company_id', $companyId)
            ->where('status', '!=', 'Deleted')
            ->orderBy('unit_name')
            ->get(['id', 'unit_name']);

        return response()->json($units);
    }

    public function getWorkPoints($companyId, $unitId)
    {
        $workPoints = WorkPoint::where('company_id', $companyId)
            ->where('comp_unit_id', $unitId)
            ->where('status', '!=', 'Deleted')
            ->orderBy('work_name')
            ->get(['id', 'work_name']);

        return response()->json($workPoints);
    }
}