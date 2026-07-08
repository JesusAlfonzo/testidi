<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SequenceService
{
    /**
     * Obtiene el siguiente valor para una secuencia específica de forma segura bajo concurrencia.
     *
     * @param string $key La clave única de la secuencia (ej. 'doc:sdc:2026', 'prod:cat:reac').
     * @return int El siguiente número secuencial.
     */
    public function getNextValue(string $key): int
    {
        return DB::transaction(function () use ($key) {
            $sequence = DB::table('sequences')
                ->where('key', $key)
                ->lockForUpdate()
                ->first();

            if (!$sequence) {
                DB::table('sequences')->insert([
                    'key' => $key,
                    'current_value' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                return 1;
            }

            $newValue = $sequence->current_value + 1;

            DB::table('sequences')
                ->where('key', $key)
                ->update([
                    'current_value' => $newValue,
                    'updated_at' => now(),
                ]);

            return $newValue;
        });
    }
}
