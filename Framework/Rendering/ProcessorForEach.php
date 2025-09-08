<?php

/**
 * @author Anibal Gomez <balerocms@gmail.com>
 * @copyright Copyright (c) 2025 Anibal Gomez
 * @license GNU General Public License
 */


namespace Framework\Rendering;

use Framework\Security\Security;


class ProcessorForEach
{

    private ProcessorFlattenParams $processFlattenParams;
    private Security $security;

    public function __construct(
        ProcessorFlattenParams $processFlattenParams,
        Security $security) {
        $this->processFlattenParams = $processFlattenParams;
        $this->security = $security;
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
                        $safeValue = $this->security->sanitize((string)$v);
                        $blockCopy = str_replace('{' . $k . '}', $safeValue, $blockCopy);
                    }

                    // FIX corregido con clave completa
                    $blockCopy = preg_replace_callback(
                        '/<!--\s*@if\s+defaultTheme\s*==\s*t\.value\s*-->/i',
                        function() use ($flatItem, $itemKey) {
                            $val = $flatItem[$itemKey . '.value'] ?? '';
                            $val = $this->security->sanitize($val);
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