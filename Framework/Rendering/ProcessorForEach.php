<?php

/**
 * @author Anibal Gomez <balerocms@gmail.com>
 * @copyright Copyright (c) 2025 Anibal Gomez
 * @license GNU General Public License
 */


namespace Framework\Rendering;


class ProcessorForEach
{

    private ProcessorFlattenParams $processFlattenParams;

    public function __construct(
        ProcessorFlattenParams $processFlattenParams) {
        $this->processFlattenParams = $processFlattenParams;
    }

    /**
     * Procesa bloques @foreach var as item ... @endforeach
     */
    public function process(string $content, array $params): string
    {
        return preg_replace_callback(
            '/<!--\s*@foreach\s+(\w+)\s+as\s+(\w+)\s*-->(.*?)<!--\s*@endforeach\s*-->/is',
            function ($matches) use ($params) {
                $arrayKey = $matches[1];    // ej: 'virtual_pages' o 'themes'
                $itemKey  = $matches[2];    // ej: 'page' o 't'
                $block    = $matches[3];    // contenido dentro del foreach

                if (!isset($params[$arrayKey]) || !is_array($params[$arrayKey])) {
                    return ''; // Si no existe o no es array, no imprime nada
                }

                $result = '';
                foreach ($params[$arrayKey] as $item) {
                    $flatItem = $this->processFlattenParams->process([$itemKey => $item]);

                    $blockCopy = $block;
                    foreach ($flatItem as $k => $v) {
                        $safeValue = htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                        $blockCopy = str_replace('{' . $k . '}', $safeValue, $blockCopy);
                    }

                    // FIX corregido con clave completa
                    $blockCopy = preg_replace_callback(
                        '/<!--\s*@if\s+defaultTheme\s*==\s*t\.value\s*-->/i',
                        function() use ($flatItem, $itemKey) {
                            $val = $flatItem[$itemKey . '.value'] ?? '';
                            $val = htmlspecialchars($val, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                            return "<!-- @if defaultTheme == '{$val}' -->";
                        },
                        $blockCopy
                    );

                    $result .= $blockCopy;
                }


                return $result;
            },
            $content
        );
    }

}