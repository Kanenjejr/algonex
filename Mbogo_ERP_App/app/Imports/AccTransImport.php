<?php

namespace App\Imports;

use App\Models\AccntSubchart;
use App\Models\AccntTransaction;
use App\Models\Company_unit;
use App\Models\Section;
use App\Models\WorkPoint;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class AccTransImport implements ToCollection
{
    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function collection(Collection $rows)
    {
        if ($rows->count() < 2) {
            throw new \Exception('Excel file has no data.');
        }

        $allRows = [];
        foreach ($rows as $row) {
            $allRows[] = array_values(is_array($row) ? $row : $row->toArray());
        }

        $firstRow = $allRows[0] ?? [];
        $hasHeaderRow = $this->looksLikeHeaderRow($firstRow);

        $headers = [];
        $startIndex = 0;

        if ($hasHeaderRow) {
            foreach ($firstRow as $cell) {
                $headers[] = $this->normalizeHeader($cell);
            }
            $startIndex = 1;
        }

        $grouped = [];

        for ($i = $startIndex; $i < count($allRows); $i++) {
            $values = $allRows[$i];

            if (!$this->rowHasData($values)) {
                continue;
            }

            $postingDate = $this->parseDate($this->getCell($values, $headers, ['postingdate', 'posting'], 0));
            $pvNo = $this->clean($this->getCell($values, $headers, ['pvno', 'pvn0', 'p.v.no', 'pvn'], 1));
            $checkNo = $this->clean($this->getCell($values, $headers, ['chqno', 'chqn0', 'checkno'], 2));

            $payee = trim((string) $this->getCell($values, $headers, ['payee'], 3));
            if ($payee === '') {
                $payee = trim(implode(' ', array_filter([
                    $this->getCell($values, $headers, [], 3),
                    $this->getCell($values, $headers, [], 4),
                    $this->getCell($values, $headers, [], 5),
                ])));
            }

            $subAccountCode = $this->clean($this->getCell($values, $headers, ['subaccountcode', 'subaccount', 'subcode'], 4));
            $sectionCode = $this->clean($this->getCell($values, $headers, ['sectioncode', 'section'], 5));
            $locationCode = $this->clean($this->getCell($values, $headers, ['locationcode', 'location'], 6));
            $businessCode = $this->clean($this->getCell($values, $headers, ['businesscode', 'businescode'], 7));
            $currency = strtoupper($this->clean($this->getCell($values, $headers, ['currency'], 8) ?: 'TZS'));
            $exchangeRate = $this->money($this->getCell($values, $headers, ['exchangerate'], 9));
            $details = trim((string) $this->getCell($values, $headers, ['details', 'memo'], 10));

            $sourceAmount = $this->money($this->getCell($values, $headers, ['sourceamount'], 11));
            $condition = strtoupper($this->clean($this->getCell($values, $headers, ['condition', 'condtion'], 12)));
            $convertedAmount = $this->money($this->getCell($values, $headers, ['amount'], 13));

            if ($sourceAmount <= 0 && $convertedAmount > 0 && $exchangeRate > 0) {
                $sourceAmount = round($convertedAmount / $exchangeRate, 2);
            }

            if ($convertedAmount <= 0 && $sourceAmount > 0 && $exchangeRate > 0) {
                $convertedAmount = round($sourceAmount * $exchangeRate, 2);
            }

            $groupKey = $this->makeGroupKey(
                $postingDate,
                $pvNo,
                $payee,
                $details,
                $sourceAmount,
                $locationCode,
                $businessCode,
                $exchangeRate
            );

            $grouped[$groupKey][] = [
                'posting_date' => $postingDate,
                'pv_no' => $pvNo,
                'check_no' => $checkNo,
                'payee' => $payee,
                'sub_account_code' => $subAccountCode,
                'section_code' => $sectionCode,
                'location_code' => $locationCode,
                'business_code' => $businessCode,
                'currency' => $currency,
                'exchange_rate' => $exchangeRate,
                'details' => $details,
                'source_amount' => $sourceAmount,
                'converted_amount' => $convertedAmount,
                'condition' => $condition,
            ];
        }

        DB::transaction(function () use ($grouped) {
            foreach ($grouped as $lines) {
                $first = $lines[0];

                $workPoint = $this->resolveWorkPoint($first['location_code']);
                $companyId = $workPoint->company_id;
                $companyUnitId = $workPoint->comp_unit_id;

                if (!empty($first['business_code'])) {
                    $unit = Company_unit::where('unit_code', $first['business_code'])->first();
                    if ($unit) {
                        $companyUnitId = $unit->id;
                        if (empty($companyId) && !empty($unit->company_id)) {
                            $companyId = $unit->company_id;
                        }
                    }
                }

                $sub = AccntSubchart::where('SubCode', $first['sub_account_code'])->first();
                if (!$sub) {
                    throw new \Exception('Sub account not found for code: ' . $first['sub_account_code']);
                }

                $section = Section::where('secCode', $first['section_code'])->first();
                if (!$section) {
                    throw new \Exception('Section not found for code: ' . $first['section_code']);
                }

                $pcvNo = $this->resolvePcvNo($first['pv_no'], $workPoint->id, $first['posting_date']);
                $groupUuid = (string) Str::uuid();

                foreach ($lines as $index => $line) {
                    $type = $line['condition'] === 'CR' ? 'credit' : 'debit';
                    $category = !empty($line['check_no']) && strtolower($line['check_no']) !== 'n/a' ? 'Bank' : 'Cash';

                    $sourceAmount = (float) $line['source_amount'];
                    $convertedAmount = (float) $line['converted_amount'];

                    if ($convertedAmount <= 0 && $sourceAmount > 0 && (float) $line['exchange_rate'] > 0) {
                        $convertedAmount = round($sourceAmount * (float) $line['exchange_rate'], 2);
                    }

                    AccntTransaction::create([
                        'transaction_group' => $groupUuid,
                        'pcv_no' => $pcvNo,
                        'trans_date' => $line['posting_date'],
                        'reference' => $pcvNo . '-' . strtoupper($type[0]) . ($index + 1),
                        'check_no' => in_array(strtolower($line['check_no']), ['n/a', 'na', ''], true) ? null : $line['check_no'],
                        'request_no' => null,
                        'requisition_id' => null,
                        'category' => $category,
                        'currency' => $line['currency'],
                        'exchange_rate' => $line['exchange_rate'],
                        'memo' => $line['details'],
                        'payee' => $line['payee'],
                        'user_id' => $this->user->id,
                        'company_id' => $companyId,
                        'work_point_id' => $workPoint->id,
                        'account_id' => $sub->accnt_chart_id,
                        'sub_account_id' => $sub->id,
                        'department_id' => $section->dept_id,
                        'section_id' => $section->id,
                        'type' => $type,
                        'amount' => $convertedAmount,
                        'source_amount' => $sourceAmount,
                        'imported_from_excel' => true,
                        'Status' => 'Active',
                        'verified' => 'verified',
                        'verified_by' => $this->user->id,
                        'verified_at' => now(),
                        'verification_comment' => 'Imported from Excel',
                        'approved' => 'approved',
                        'approved_by' => $this->user->id,
                        'approved_at' => now(),
                        'approval_comment' => 'Imported from Excel',
                    ]);
                }
            }
        });
    }

    protected function rowHasData(array $values): bool
    {
        foreach ($values as $v) {
            if (trim((string) $v) !== '') {
                return true;
            }
        }

        return false;
    }

    protected function normalizeHeader($value): string
    {
        $value = strtolower(trim((string) $value));
        return preg_replace('/[^a-z0-9]+/', '', $value) ?: '';
    }

    protected function looksLikeHeaderRow(array $row): bool
    {
        $rowNorm = array_map(fn ($v) => $this->normalizeHeader($v), $row);

        $needles = ['postingdate', 'pvno', 'payee', 'subaccountcode', 'sectioncode', 'locationcode'];
        $found = 0;

        foreach ($needles as $needle) {
            if (in_array($needle, $rowNorm, true)) {
                $found++;
            }
        }

        return $found >= 3;
    }

    protected function findHeaderIndex(array $headers, array $names): ?int
    {
        foreach ($names as $name) {
            $needle = $this->normalizeHeader($name);
            foreach ($headers as $i => $header) {
                if ($header === $needle) {
                    return $i;
                }
            }
        }

        return null;
    }

    protected function getCell(array $values, array $headers, array $names, ?int $fallbackIndex = null)
    {
        if (!empty($headers)) {
            $idx = $this->findHeaderIndex($headers, $names);
            if ($idx !== null && array_key_exists($idx, $values)) {
                return $values[$idx];
            }
        }

        if ($fallbackIndex !== null && array_key_exists($fallbackIndex, $values)) {
            return $values[$fallbackIndex];
        }

        return null;
    }

    protected function clean($value): string
    {
        return trim((string) $value);
    }

    protected function money($value): float
    {
        $value = trim((string) $value);
        if ($value === '' || strtolower($value) === 'n/a') {
            return 0.0;
        }

        $value = str_replace([',', ' '], '', $value);
        return (float) $value;
    }

    protected function parseDate($value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->toDateString();
        }

        if (is_numeric($value)) {
            return Carbon::instance(ExcelDate::excelToDateTimeObject($value))->toDateString();
        }

        return Carbon::parse((string) $value)->toDateString();
    }

    protected function makeGroupKey($postingDate, $pvNo, $payee, $details, $sourceAmount, $locationCode, $businessCode, $exchangeRate): string
    {
        $pv = strtolower(trim((string) $pvNo));
        if ($pv !== '' && $pv !== 'n/a' && $pv !== 'na') {
            return $pv . '|' . $postingDate;
        }

        return sha1($postingDate . '|' . $payee . '|' . $details . '|' . $sourceAmount . '|' . $locationCode . '|' . $businessCode . '|' . $exchangeRate);
    }

    protected function resolveWorkPoint(string $locationCode): WorkPoint
    {
        $locationCode = trim($locationCode);

        $workPoint = WorkPoint::where('work_code', $locationCode)->first()
            ?: WorkPoint::where('work_name', $locationCode)->first();

        if (!$workPoint) {
            throw new \Exception('Work point not found for location code: ' . $locationCode);
        }

        return $workPoint;
    }

    protected function resolvePcvNo($pvNo, int $workPointId, string $date): string
    {
        $pv = trim((string) $pvNo);
        if ($pv !== '' && strtolower($pv) !== 'n/a' && strtolower($pv) !== 'na') {
            return $pv;
        }

        $work = WorkPoint::find($workPointId);
        $workCode = strtoupper(trim(optional($work)->work_code ?: 'WRK'));
        $datePart = Carbon::parse($date)->format('dmY');
        $monthKey = Carbon::parse($date)->format('Ym');

        $count = AccntTransaction::where('work_point_id', $workPointId)
            ->whereRaw("DATE_FORMAT(trans_date, '%Y%m') = ?", [$monthKey])
            ->distinct('pcv_no')
            ->count('pcv_no');

        $next = $count + 1;

        return 'PCV' . str_pad($next, 4, '0', STR_PAD_LEFT) . '-' . $workCode . '/' . $datePart;
    }
}