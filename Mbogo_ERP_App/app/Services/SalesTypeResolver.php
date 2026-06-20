<?php

namespace App\Services;

use App\Models\Company_unit;
use App\Models\SalesType;

class SalesTypeResolver
{
    public static function resolve($companyUnitId)
    {
        $companyUnit = Company_unit::with('salesType')
            ->find($companyUnitId);

        if (!$companyUnit) {
            return null;
        }

        return $companyUnit->salesType;
    }

    public static function resolveCode($companyUnitId)
    {
        $salesType = self::resolve($companyUnitId);

        return $salesType ? $salesType->code : null;
    }

    public static function affectStock($companyUnitId)
    {
        $salesType = self::resolve($companyUnitId);

        return $salesType
            ? (bool) $salesType->affect_stock
            : false;
    }

    public static function affectInventory($companyUnitId)
    {
        $salesType = self::resolve($companyUnitId);

        return $salesType
            ? (bool) $salesType->affect_inventory
            : false;
    }

    public static function affectCOGS($companyUnitId)
    {
        $salesType = self::resolve($companyUnitId);

        return $salesType
            ? (bool) $salesType->affect_cogs
            : false;
    }

    public static function affectDelivery($companyUnitId)
    {
        $salesType = self::resolve($companyUnitId);

        return $salesType
            ? (bool) $salesType->affect_delivery
            : false;
    }

    public static function requireApproval($companyUnitId)
    {
        $salesType = self::resolve($companyUnitId);

        return $salesType
            ? (bool) $salesType->require_approval
            : false;
    }
}