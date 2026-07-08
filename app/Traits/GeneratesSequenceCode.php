<?php

namespace App\Traits;

use App\Services\SequenceService;
use Illuminate\Support\Facades\Log;

trait GeneratesSequenceCode
{
    /**
     * Boot del trait. Laravel llama automáticamente a los métodos boot[NombreDelTrait]
     * cuando se inicializa el modelo.
     */
    public static function bootGeneratesSequenceCode()
    {
        static::creating(function ($model) {
            $codeField = $model->getSequenceField();

            // Si el código ya fue asignado manualmente, respetarlo
            if (!empty($model->$codeField)) {
                // Asegurarse de que esté sanitizado para códigos de barra (solo mayúsculas, números y guión)
                $model->$codeField = self::sanitizeBarcodeString($model->$codeField);
                return;
            }

            try {
                $rawPrefix = $model->getSequencePrefix();
                $prefix = self::sanitizeBarcodeString($rawPrefix);
                
                // Si por alguna razón el prefijo queda vacío, usar fallback
                if (empty($prefix)) {
                    $prefix = 'DOC';
                }

                $key = $model->getSequenceKey($prefix);

                $sequenceService = app(SequenceService::class);
                $nextValue = $sequenceService->getNextValue($key);

                $padding = $model->getSequencePadding();
                $separator = $model->getSequenceSeparator();

                $yearPart = '';
                if ($model->isSequenceYearly()) {
                    $yearPart = date('Y') . $separator;
                }

                $model->$codeField = $prefix . $separator . $yearPart . str_pad($nextValue, $padding, '0', STR_PAD_LEFT);
            } catch (\Exception $e) {
                Log::error("Error generating sequence code for model " . get_class($model) . ": " . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Obtiene el campo del modelo donde se guarda el código.
     */
    public function getSequenceField(): string
    {
        return $this->sequenceField ?? 'code';
    }

    /**
     * Obtiene la longitud del relleno con ceros.
     */
    public function getSequencePadding(): int
    {
        return $this->sequencePadding ?? 4;
    }

    /**
     * Obtiene el separador a usar.
     */
    public function getSequenceSeparator(): string
    {
        return '-';
    }

    /**
     * Determina si la secuencia se reinicia anualmente.
     */
    public function isSequenceYearly(): bool
    {
        return $this->sequenceYearly ?? false;
    }

    /**
     * Genera la clave de secuencia única para almacenar en la base de datos.
     */
    public function getSequenceKey(string $prefix): string
    {
        if ($this->isSequenceYearly()) {
            return strtolower($prefix) . ':' . date('Y');
        }
        return strtolower($prefix);
    }

    /**
     * Sanitiza una cadena para que sea 100% compatible con lectores de códigos de barras (Code 128).
     * Solo conserva mayúsculas, números y guiones medios.
     */
    public static function sanitizeBarcodeString(string $value): string
    {
        $unwanted_array = [
            'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C',
            'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
            'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a',
            'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i',
            'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u',
            'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y'
        ];
        
        $value = strtr($value, $unwanted_array);
        
        // Reemplazar espacios o caracteres especiales no deseados con guión medio
        $value = preg_replace('/[\s_\/\\\]+/', '-', $value);
        
        // Eliminar cualquier cosa que no sea letra, número o guión
        $value = preg_replace('/[^A-Za-z0-9\-]/', '', $value);
        
        // Convertir a mayúsculas
        $value = strtoupper($value);
        
        // Asegurarse de que no queden múltiples guiones seguidos o guiones al inicio/final
        $value = preg_replace('/-+/', '-', $value);
        return trim($value, '-');
    }

    /**
     * El modelo debe definir este método para retornar el prefijo base del código.
     */
    abstract public function getSequencePrefix(): string;
}
