<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class GeneralSupplyItemsSeeder extends Seeder
{
    public function run()
    {
        $items = [
            [
                'item_name' => 'Water',
                'item_code' => 'GS-WATER',
                'descriptions' => [
                    ['description_name' => 'Afya', 'units' => ['Bottle', 'Carton']],
                    ['description_name' => 'Kilimanjaro', 'units' => ['Bottle', 'Carton']],
                    ['description_name' => 'Dewdrop', 'units' => ['Bottle', 'Carton']],
                ],
            ],
            [
                'item_name' => 'Envelope',
                'item_code' => 'GS-ENVELOPE',
                'descriptions' => [
                    ['description_name' => 'A4', 'units' => ['PC', 'Bundle']],
                    ['description_name' => 'A3', 'units' => ['PC', 'Bundle']],
                    ['description_name' => 'A6', 'units' => ['PC', 'Bundle']],
                ],
            ],
            [
                'item_name' => 'Bolt',
                'item_code' => 'GS-BOLT',
                'descriptions' => [
                    ['description_name' => '20mm', 'units' => ['PC']],
                    ['description_name' => '10 inch', 'units' => ['PC']],
                    ['description_name' => '12mm', 'units' => ['PC']],
                    ['description_name' => '16mm', 'units' => ['PC']],
                ],
            ],
            [
                'item_name' => 'Fuel',
                'item_code' => 'GS-FUEL',
                'descriptions' => [
                    ['description_name' => 'Kerosene', 'units' => ['Liter']],
                    ['description_name' => 'Diesel', 'units' => ['Liter']],
                    ['description_name' => 'Petrol', 'units' => ['Liter']],
                ],
            ],
            [
                'item_name' => 'Sugar',
                'item_code' => 'GS-SUGAR',
                'descriptions' => [
                    ['description_name' => 'White Sugar', 'units' => ['Kg', 'Bag']],
                    ['description_name' => 'Brown Sugar', 'units' => ['Kg', 'Bag']],
                ],
            ],
            [
                'item_name' => 'Tea Leaves',
                'item_code' => 'GS-TEA',
                'descriptions' => [
                    ['description_name' => 'Chai Bora', 'units' => ['Packet', 'Box']],
                    ['description_name' => 'Africafe Tea', 'units' => ['Packet', 'Box']],
                    ['description_name' => 'Green Tea', 'units' => ['Packet', 'Box']],
                ],
            ],
            [
                'item_name' => 'Coffee',
                'item_code' => 'GS-COFFEE',
                'descriptions' => [
                    ['description_name' => 'Instant Coffee', 'units' => ['Tin', 'Packet']],
                    ['description_name' => 'Ground Coffee', 'units' => ['Packet']],
                ],
            ],
            [
                'item_name' => 'Milk',
                'item_code' => 'GS-MILK',
                'descriptions' => [
                    ['description_name' => 'Fresh Milk', 'units' => ['Liter', 'Carton']],
                    ['description_name' => 'Powder Milk', 'units' => ['Tin', 'Packet']],
                ],
            ],
            [
                'item_name' => 'Biscuit',
                'item_code' => 'GS-BISCUIT',
                'descriptions' => [
                    ['description_name' => 'Glucose', 'units' => ['Packet', 'Carton']],
                    ['description_name' => 'Digestive', 'units' => ['Packet', 'Carton']],
                    ['description_name' => 'Cream Biscuit', 'units' => ['Packet', 'Carton']],
                ],
            ],
            [
                'item_name' => 'Soft Drink',
                'item_code' => 'GS-SOFT-DRINK',
                'descriptions' => [
                    ['description_name' => 'Coca Cola', 'units' => ['Bottle', 'Crate']],
                    ['description_name' => 'Fanta', 'units' => ['Bottle', 'Crate']],
                    ['description_name' => 'Sprite', 'units' => ['Bottle', 'Crate']],
                ],
            ],
            [
                'item_name' => 'Pen',
                'item_code' => 'GS-PEN',
                'descriptions' => [
                    ['description_name' => 'Blue Pen', 'units' => ['PC', 'Box']],
                    ['description_name' => 'Black Pen', 'units' => ['PC', 'Box']],
                    ['description_name' => 'Red Pen', 'units' => ['PC', 'Box']],
                ],
            ],
            [
                'item_name' => 'Pencil',
                'item_code' => 'GS-PENCIL',
                'descriptions' => [
                    ['description_name' => 'HB Pencil', 'units' => ['PC', 'Box']],
                    ['description_name' => '2B Pencil', 'units' => ['PC', 'Box']],
                ],
            ],
            [
                'item_name' => 'Marker Pen',
                'item_code' => 'GS-MARKER',
                'descriptions' => [
                    ['description_name' => 'Permanent Marker', 'units' => ['PC', 'Box']],
                    ['description_name' => 'Whiteboard Marker', 'units' => ['PC', 'Box']],
                ],
            ],
            [
                'item_name' => 'Exercise Book',
                'item_code' => 'GS-EXERCISE-BOOK',
                'descriptions' => [
                    ['description_name' => 'Counter Book', 'units' => ['PC', 'Bundle']],
                    ['description_name' => 'A4 Exercise Book', 'units' => ['PC', 'Bundle']],
                    ['description_name' => 'A5 Exercise Book', 'units' => ['PC', 'Bundle']],
                ],
            ],
            [
                'item_name' => 'Printing Paper',
                'item_code' => 'GS-PRINTING-PAPER',
                'descriptions' => [
                    ['description_name' => 'A4 80gsm', 'units' => ['Ream', 'Box']],
                    ['description_name' => 'A3 80gsm', 'units' => ['Ream', 'Box']],
                    ['description_name' => 'Colored Paper', 'units' => ['Ream']],
                ],
            ],
            [
                'item_name' => 'File Folder',
                'item_code' => 'GS-FILE-FOLDER',
                'descriptions' => [
                    ['description_name' => 'Spring File', 'units' => ['PC', 'Box']],
                    ['description_name' => 'Box File', 'units' => ['PC', 'Box']],
                    ['description_name' => 'Flat File', 'units' => ['PC', 'Box']],
                ],
            ],
            [
                'item_name' => 'Stapler',
                'item_code' => 'GS-STAPLER',
                'descriptions' => [
                    ['description_name' => 'Small Stapler', 'units' => ['PC']],
                    ['description_name' => 'Heavy Duty Stapler', 'units' => ['PC']],
                ],
            ],
            [
                'item_name' => 'Staple Pins',
                'item_code' => 'GS-STAPLE-PINS',
                'descriptions' => [
                    ['description_name' => 'No. 10', 'units' => ['Box']],
                    ['description_name' => '24/6', 'units' => ['Box']],
                    ['description_name' => '23/13', 'units' => ['Box']],
                ],
            ],
            [
                'item_name' => 'Paper Clip',
                'item_code' => 'GS-PAPER-CLIP',
                'descriptions' => [
                    ['description_name' => 'Small Paper Clip', 'units' => ['Box']],
                    ['description_name' => 'Large Paper Clip', 'units' => ['Box']],
                ],
            ],
            [
                'item_name' => 'Rubber Stamp',
                'item_code' => 'GS-RUBBER-STAMP',
                'descriptions' => [
                    ['description_name' => 'Paid Stamp', 'units' => ['PC']],
                    ['description_name' => 'Received Stamp', 'units' => ['PC']],
                    ['description_name' => 'Approved Stamp', 'units' => ['PC']],
                ],
            ],
            [
                'item_name' => 'Ink',
                'item_code' => 'GS-INK',
                'descriptions' => [
                    ['description_name' => 'Printer Ink Black', 'units' => ['Bottle', 'Cartridge']],
                    ['description_name' => 'Printer Ink Colored', 'units' => ['Bottle', 'Cartridge']],
                    ['description_name' => 'Stamp Ink', 'units' => ['Bottle']],
                ],
            ],
            [
                'item_name' => 'Toner',
                'item_code' => 'GS-TONER',
                'descriptions' => [
                    ['description_name' => 'HP Toner', 'units' => ['Cartridge']],
                    ['description_name' => 'Canon Toner', 'units' => ['Cartridge']],
                    ['description_name' => 'Kyocera Toner', 'units' => ['Cartridge']],
                ],
            ],
            [
                'item_name' => 'Computer Mouse',
                'item_code' => 'GS-MOUSE',
                'descriptions' => [
                    ['description_name' => 'Wired Mouse', 'units' => ['PC']],
                    ['description_name' => 'Wireless Mouse', 'units' => ['PC']],
                ],
            ],
            [
                'item_name' => 'Keyboard',
                'item_code' => 'GS-KEYBOARD',
                'descriptions' => [
                    ['description_name' => 'Wired Keyboard', 'units' => ['PC']],
                    ['description_name' => 'Wireless Keyboard', 'units' => ['PC']],
                ],
            ],
            [
                'item_name' => 'Flash Disk',
                'item_code' => 'GS-FLASH-DISK',
                'descriptions' => [
                    ['description_name' => '16GB', 'units' => ['PC']],
                    ['description_name' => '32GB', 'units' => ['PC']],
                    ['description_name' => '64GB', 'units' => ['PC']],
                    ['description_name' => '128GB', 'units' => ['PC']],
                ],
            ],
            [
                'item_name' => 'External Hard Drive',
                'item_code' => 'GS-HDD',
                'descriptions' => [
                    ['description_name' => '500GB', 'units' => ['PC']],
                    ['description_name' => '1TB', 'units' => ['PC']],
                    ['description_name' => '2TB', 'units' => ['PC']],
                ],
            ],
            [
                'item_name' => 'Soap',
                'item_code' => 'GS-SOAP',
                'descriptions' => [
                    ['description_name' => 'Liquid Soap', 'units' => ['Liter', 'Bottle']],
                    ['description_name' => 'Bar Soap', 'units' => ['PC', 'Carton']],
                    ['description_name' => 'Hand Wash Soap', 'units' => ['Bottle', 'Carton']],
                ],
            ],
            [
                'item_name' => 'Detergent',
                'item_code' => 'GS-DETERGENT',
                'descriptions' => [
                    ['description_name' => 'Powder Detergent', 'units' => ['Kg', 'Packet']],
                    ['description_name' => 'Liquid Detergent', 'units' => ['Liter', 'Bottle']],
                ],
            ],
            [
                'item_name' => 'Toilet Paper',
                'item_code' => 'GS-TOILET-PAPER',
                'descriptions' => [
                    ['description_name' => 'Soft Tissue', 'units' => ['Roll', 'Carton']],
                    ['description_name' => 'Hard Tissue', 'units' => ['Roll', 'Carton']],
                ],
            ],
            [
                'item_name' => 'Paper Towel',
                'item_code' => 'GS-PAPER-TOWEL',
                'descriptions' => [
                    ['description_name' => 'Kitchen Towel', 'units' => ['Roll', 'Carton']],
                    ['description_name' => 'Hand Towel', 'units' => ['Packet', 'Carton']],
                ],
            ],
            [
                'item_name' => 'Disinfectant',
                'item_code' => 'GS-DISINFECTANT',
                'descriptions' => [
                    ['description_name' => 'Jik', 'units' => ['Liter', 'Bottle', 'Carton']],
                    ['description_name' => 'Dettol', 'units' => ['Liter', 'Bottle', 'Carton']],
                    ['description_name' => 'Sanitizer', 'units' => ['Liter', 'Bottle']],
                ],
            ],
            [
                'item_name' => 'Broom',
                'item_code' => 'GS-BROOM',
                'descriptions' => [
                    ['description_name' => 'Soft Broom', 'units' => ['PC']],
                    ['description_name' => 'Hard Broom', 'units' => ['PC']],
                    ['description_name' => 'Local Broom', 'units' => ['PC']],
                ],
            ],
            [
                'item_name' => 'Mop',
                'item_code' => 'GS-MOP',
                'descriptions' => [
                    ['description_name' => 'Cotton Mop', 'units' => ['PC']],
                    ['description_name' => 'Sponge Mop', 'units' => ['PC']],
                    ['description_name' => 'Mop Bucket', 'units' => ['PC']],
                ],
            ],
            [
                'item_name' => 'Dustbin',
                'item_code' => 'GS-DUSTBIN',
                'descriptions' => [
                    ['description_name' => 'Small Dustbin', 'units' => ['PC']],
                    ['description_name' => 'Medium Dustbin', 'units' => ['PC']],
                    ['description_name' => 'Large Dustbin', 'units' => ['PC']],
                ],
            ],
            [
                'item_name' => 'Garbage Bag',
                'item_code' => 'GS-GARBAGE-BAG',
                'descriptions' => [
                    ['description_name' => 'Small Garbage Bag', 'units' => ['Roll', 'Packet']],
                    ['description_name' => 'Large Garbage Bag', 'units' => ['Roll', 'Packet']],
                ],
            ],
            [
                'item_name' => 'Cement',
                'item_code' => 'GS-CEMENT',
                'descriptions' => [
                    ['description_name' => 'Twiga Cement', 'units' => ['Bag']],
                    ['description_name' => 'Dangote Cement', 'units' => ['Bag']],
                    ['description_name' => 'Simba Cement', 'units' => ['Bag']],
                ],
            ],
            [
                'item_name' => 'Paint',
                'item_code' => 'GS-PAINT',
                'descriptions' => [
                    ['description_name' => 'White Paint', 'units' => ['Liter', 'Bucket']],
                    ['description_name' => 'Black Paint', 'units' => ['Liter', 'Bucket']],
                    ['description_name' => 'Oil Paint', 'units' => ['Liter', 'Bucket']],
                    ['description_name' => 'Emulsion Paint', 'units' => ['Liter', 'Bucket']],
                ],
            ],
            [
                'item_name' => 'Nails',
                'item_code' => 'GS-NAILS',
                'descriptions' => [
                    ['description_name' => '1 inch', 'units' => ['Kg']],
                    ['description_name' => '2 inch', 'units' => ['Kg']],
                    ['description_name' => '3 inch', 'units' => ['Kg']],
                    ['description_name' => '4 inch', 'units' => ['Kg']],
                ],
            ],
            [
                'item_name' => 'Screws',
                'item_code' => 'GS-SCREWS',
                'descriptions' => [
                    ['description_name' => 'Wood Screw', 'units' => ['PC', 'Box']],
                    ['description_name' => 'Metal Screw', 'units' => ['PC', 'Box']],
                    ['description_name' => 'Drywall Screw', 'units' => ['PC', 'Box']],
                ],
            ],
            [
                'item_name' => 'PVC Pipe',
                'item_code' => 'GS-PVC-PIPE',
                'descriptions' => [
                    ['description_name' => '1 inch', 'units' => ['Length']],
                    ['description_name' => '2 inch', 'units' => ['Length']],
                    ['description_name' => '4 inch', 'units' => ['Length']],
                ],
            ],
            [
                'item_name' => 'Pipe Fittings',
                'item_code' => 'GS-PIPE-FITTINGS',
                'descriptions' => [
                    ['description_name' => 'Elbow', 'units' => ['PC']],
                    ['description_name' => 'Tee', 'units' => ['PC']],
                    ['description_name' => 'Socket', 'units' => ['PC']],
                    ['description_name' => 'Reducer', 'units' => ['PC']],
                ],
            ],
            [
                'item_name' => 'Battery',
                'item_code' => 'GS-BATTERY',
                'descriptions' => [
                    ['description_name' => 'AA Battery', 'units' => ['PC', 'Packet']],
                    ['description_name' => 'AAA Battery', 'units' => ['PC', 'Packet']],
                    ['description_name' => '9V Battery', 'units' => ['PC']],
                ],
            ],
            [
                'item_name' => 'Bulb',
                'item_code' => 'GS-BULB',
                'descriptions' => [
                    ['description_name' => 'LED Bulb 9W', 'units' => ['PC']],
                    ['description_name' => 'LED Bulb 12W', 'units' => ['PC']],
                    ['description_name' => 'Tube Light', 'units' => ['PC']],
                ],
            ],
            [
                'item_name' => 'Socket',
                'item_code' => 'GS-SOCKET',
                'descriptions' => [
                    ['description_name' => 'Single Socket', 'units' => ['PC']],
                    ['description_name' => 'Double Socket', 'units' => ['PC']],
                    ['description_name' => 'Extension Socket', 'units' => ['PC']],
                ],
            ],
            [
                'item_name' => 'Cable',
                'item_code' => 'GS-CABLE',
                'descriptions' => [
                    ['description_name' => 'Electrical Cable 1.5mm', 'units' => ['Meter', 'Roll']],
                    ['description_name' => 'Electrical Cable 2.5mm', 'units' => ['Meter', 'Roll']],
                    ['description_name' => 'Network Cable', 'units' => ['Meter', 'Roll']],
                ],
            ],
            [
                'item_name' => 'Switch',
                'item_code' => 'GS-SWITCH',
                'descriptions' => [
                    ['description_name' => 'Single Switch', 'units' => ['PC']],
                    ['description_name' => 'Double Switch', 'units' => ['PC']],
                ],
            ],
            [
                'item_name' => 'Uniform',
                'item_code' => 'GS-UNIFORM',
                'descriptions' => [
                    ['description_name' => 'Shirt', 'units' => ['PC']],
                    ['description_name' => 'Trouser', 'units' => ['PC']],
                    ['description_name' => 'Skirt', 'units' => ['PC']],
                    ['description_name' => 'Overall', 'units' => ['PC']],
                ],
            ],
            [
                'item_name' => 'Safety Boots',
                'item_code' => 'GS-SAFETY-BOOTS',
                'descriptions' => [
                    ['description_name' => 'Size 40', 'units' => ['Pair']],
                    ['description_name' => 'Size 41', 'units' => ['Pair']],
                    ['description_name' => 'Size 42', 'units' => ['Pair']],
                    ['description_name' => 'Size 43', 'units' => ['Pair']],
                ],
            ],
            [
                'item_name' => 'Gloves',
                'item_code' => 'GS-GLOVES',
                'descriptions' => [
                    ['description_name' => 'Latex Gloves', 'units' => ['Pair', 'Box']],
                    ['description_name' => 'Rubber Gloves', 'units' => ['Pair']],
                    ['description_name' => 'Safety Gloves', 'units' => ['Pair']],
                ],
            ],
            [
                'item_name' => 'Helmet',
                'item_code' => 'GS-HELMET',
                'descriptions' => [
                    ['description_name' => 'Safety Helmet', 'units' => ['PC']],
                    ['description_name' => 'Motorcycle Helmet', 'units' => ['PC']],
                ],
            ],
            [
                'item_name' => 'Chair',
                'item_code' => 'GS-CHAIR',
                'descriptions' => [
                    ['description_name' => 'Office Chair', 'units' => ['PC']],
                    ['description_name' => 'Plastic Chair', 'units' => ['PC']],
                    ['description_name' => 'Visitor Chair', 'units' => ['PC']],
                ],
            ],
            [
                'item_name' => 'Table',
                'item_code' => 'GS-TABLE',
                'descriptions' => [
                    ['description_name' => 'Office Table', 'units' => ['PC']],
                    ['description_name' => 'Conference Table', 'units' => ['PC']],
                    ['description_name' => 'Plastic Table', 'units' => ['PC']],
                ],
            ],
            [
                'item_name' => 'Cabinet',
                'item_code' => 'GS-CABINET',
                'descriptions' => [
                    ['description_name' => 'Filing Cabinet', 'units' => ['PC']],
                    ['description_name' => 'Metal Cabinet', 'units' => ['PC']],
                    ['description_name' => 'Wooden Cabinet', 'units' => ['PC']],
                ],
            ],
            [
                'item_name' => 'Curtain',
                'item_code' => 'GS-CURTAIN',
                'descriptions' => [
                    ['description_name' => 'Window Curtain', 'units' => ['Pair']],
                    ['description_name' => 'Office Curtain', 'units' => ['Pair']],
                ],
            ],
            [
                'item_name' => 'Air Freshener',
                'item_code' => 'GS-AIR-FRESHENER',
                'descriptions' => [
                    ['description_name' => 'Spray Air Freshener', 'units' => ['Bottle', 'Carton']],
                    ['description_name' => 'Automatic Refill', 'units' => ['Bottle', 'Carton']],
                ],
            ],
            [
                'item_name' => 'Key Holder',
                'item_code' => 'GS-KEY-HOLDER',
                'descriptions' => [
                    ['description_name' => 'Key Holder', 'units' => ['PC']],
                ],
            ],
            [
                'item_name' => 'Padlock',
                'item_code' => 'GS-PADLOCK',
                'descriptions' => [
                    ['description_name' => 'Small Padlock', 'units' => ['PC']],
                    ['description_name' => 'Medium Padlock', 'units' => ['PC']],
                    ['description_name' => 'Large Padlock', 'units' => ['PC']],
                ],
            ],
            [
                'item_name' => 'Rope',
                'item_code' => 'GS-ROPE',
                'descriptions' => [
                    ['description_name' => 'Nylon Rope', 'units' => ['Meter', 'Roll']],
                    ['description_name' => 'Cotton Rope', 'units' => ['Meter', 'Roll']],
                ],
            ],
            [
                'item_name' => 'Bucket',
                'item_code' => 'GS-BUCKET',
                'descriptions' => [
                    ['description_name' => '10 Liter Bucket', 'units' => ['PC']],
                    ['description_name' => '20 Liter Bucket', 'units' => ['PC']],
                ],
            ],
            [
                'item_name' => 'Cup',
                'item_code' => 'GS-CUP',
                'descriptions' => [
                    ['description_name' => 'Plastic Cup', 'units' => ['PC', 'Packet']],
                    ['description_name' => 'Glass Cup', 'units' => ['PC', 'Box']],
                ],
            ],
            [
                'item_name' => 'Plate',
                'item_code' => 'GS-PLATE',
                'descriptions' => [
                    ['description_name' => 'Plastic Plate', 'units' => ['PC', 'Packet']],
                    ['description_name' => 'Ceramic Plate', 'units' => ['PC', 'Box']],
                ],
            ],
            [
                'item_name' => 'Spoon',
                'item_code' => 'GS-SPOON',
                'descriptions' => [
                    ['description_name' => 'Tea Spoon', 'units' => ['PC', 'Dozen']],
                    ['description_name' => 'Table Spoon', 'units' => ['PC', 'Dozen']],
                ],
            ],
        ];
        foreach ($items as $item) {
            $itemId = DB::table('general_supply_items')->updateOrInsert(
                ['item_name' => $item['item_name']],
                [
                    'item_code' => $item['item_code'],
                    'status' => 'Active',
                    'created_by' => 1,
                    'updated_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            $generalSupplyItem = DB::table('general_supply_items')->where('item_name', $item['item_name'])->first();
            foreach ($item['descriptions'] as $description) {
                foreach ($description['units'] as $unit) {
                    DB::table('general_supply_item_descriptions')->updateOrInsert(
                        [
                            'item_id' => $generalSupplyItem->id,
                            'description_name' => $description['description_name'],
                            'unit_name' => $unit,
                        ],
                        [
                            'status' => 'Active',
                            'created_by' => 1,
                            'updated_by' => 1,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }
    }
}