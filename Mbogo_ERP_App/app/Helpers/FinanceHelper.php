<?php

use Illuminate\Support\Facades\DB;

function postFinance($data)
{
    DB::table('accounting_transactions')->insert([
        'posting_date' => now(),

        'account_code' => $data['account_code'],
        'account_description' => $data['account_name'],

        'company_code' => $data['company_id'] ?? null,

        'debit' => $data['debit'] ?? 0,
        'credit' => $data['credit'] ?? 0,

        'details' => $data['details'] ?? '',
        'status' => 'posted'
    ]);
}