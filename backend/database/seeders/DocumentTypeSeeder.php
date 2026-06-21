<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $documentTypes = [
            ['name' => 'Invoice', 'linking_required' => false],
            ['name' => 'Challan', 'linking_required' => false],
            ['name' => 'Purchase Order', 'linking_required' => true],
            ['name' => 'Credit Note', 'linking_required' => true],
            ['name' => 'Debit Note', 'linking_required' => true],
            ['name' => 'GRN', 'linking_required' => true],
        ];

        foreach ($documentTypes as $type) {
            DocumentType::updateOrCreate(
                ['name' => $type['name']],
                $type
            );
        }
    }
}