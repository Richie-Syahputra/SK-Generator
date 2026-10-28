<?php

namespace Database\Seeders;

use App\Models\JenisSk;
use Illuminate\Database\Seeder;

class Tahap15PatchSeeder extends Seeder
{
    public function run(): void
    {
        $jenis = JenisSk::where('kode', 'PEMBERHENTIAN')->first();
        if (! $jenis) {
            return;
        }

        $skema = $jenis->field_schema;
        foreach ($skema as $i => $field) {
            if (($field['key'] ?? '') === 'alasan' && isset($field['options'])) {
                $skema[$i]['options'] = array_values(array_filter(
                    $field['options'],
                    fn ($opsi) => $opsi !== 'Lainnya'
                ));
            }
        }

        $jenis->field_schema = $skema;
        $jenis->save();
    }
}
