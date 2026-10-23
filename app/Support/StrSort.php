<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class StrSort
{
    /**
     * Mapa de sustitución de caracteres con tilde o signos diacríticos
     * por sus caracteres base equivalentes en minúsculas.
     */
    protected const CHAR_MAP = [
        'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u', 'Ü' => 'u',
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u',
        'À' => 'a', 'È' => 'e', 'Ì' => 'i', 'Ò' => 'o', 'Ù' => 'u',
        'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
        'Â' => 'a', 'Ê' => 'e', 'Î' => 'i', 'Ô' => 'o', 'Û' => 'u',
        'â' => 'a', 'ê' => 'e', 'î' => 'i', 'ô' => 'o', 'û' => 'u',
        'Ã' => 'a', 'Õ' => 'o', 'ã' => 'a', 'õ' => 'o',
        'Ä' => 'a', 'Ë' => 'e', 'Ï' => 'i', 'Ö' => 'o',
        'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o',
    ];

    /**
     * Normaliza una cadena para ordenación alfabética:
     * sustituye vocales con tildes/diéresis por vocales base en minúsculas.
     */
    public static function normalize(?string $str): string
    {
        if ($str === null || $str === '') {
            return '';
        }

        $normalized = strtr($str, self::CHAR_MAP);

        return mb_strtolower($normalized, 'UTF-8');
    }

    /**
     * Compara dos cadenas de texto de forma insensible a tildes y mayúsculas/minúsculas.
     * Si las versiones normalizadas son idénticas, desempata con la comparación original.
     */
    public static function compare(?string $a, ?string $b): int
    {
        $normA = self::normalize($a);
        $normB = self::normalize($b);

        $cmp = strcmp($normA, $normB);
        if ($cmp !== 0) {
            return $cmp;
        }

        return strcmp($a ?? '', $b ?? '');
    }

    /**
     * Aplica ordenación alfabética insensible a tildes sobre una consulta Eloquent / Query Builder.
     * En SQLite (entorno de tests) utiliza la función STR_SORT_KEY registrada en PDO;
     * en MySQL aprovecha la colación utf8mb4_unicode_ci.
     */
    public static function applyOrderBy($query, string $column, string $direction = 'asc')
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        if (DB::connection()->getDriverName() === 'sqlite') {
            return $query->orderByRaw("STR_SORT_KEY({$column}) {$direction}");
        }

        return $query->orderBy($column, $direction);
    }
}
