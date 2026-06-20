<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            // ================= COMPANY STRUCTURE =================
            CompanySeeder::class,

            // ================= USERS / PERMISSIONS =================
            UserSeeder::class,

            // ================= ACCOUNTING =================
            AccountChartSeeder::class,
            AccountsSeeder::class,

            // ================= ORGANIZATION =================
            SectionSeeder::class,

            // ================= CUSTOMERS / SUPPLIERS =================
            CustomerSeeder::class,
            VendorSeeder::class,
            CstmSpliesSeeder::class,

            // ================= PRODUCTS / SERVICES =================
            ProductSeeder::class,
            ProductBusinessUnitSeeder::class,
            RawMaterialSeeder::class,
            ServiceSeeder::class,

            // ================= ASSETS / STOCK =================
            AssetCategorySeeder::class,

            /*
            |--------------------------------------------------------------------------
            | IMPORTANT
            |--------------------------------------------------------------------------
            | This now initializes stock to zero only.
            |--------------------------------------------------------------------------
            */
            StockOpeningSeeder::class,

            // ================= GENERAL SUPPLIES =================
            GeneralSupplyItemsSeeder::class,
        ]);

        echo "\n";
        echo "=====================================\n";
        echo " ERP DATABASE SEEDED SUCCESSFULLY \n";
        echo " PRODUCTION STOCK STARTS AT ZERO \n";
        echo "=====================================\n";
    }
}