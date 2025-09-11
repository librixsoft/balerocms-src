<?php

namespace Framework\Rendering;

class ProcessorForEach
{
    private ProcessorFlattenParams $processFlattenParams;
    private ProcessorIfBlocks $processorIfBlocks;

    public function __construct(
        ProcessorFlattenParams $processFlattenParams,
        ProcessorIfBlocks $processorIfBlocks
    ) {
        $this->processFlattenParams = $processFlattenParams;
        $this->processorIfBlocks = $processorIfBlocks;
    }

    /**
     * Procesa bloques @foreach var as item ... @endforeach
     */
    public function process(string $content, array $params): string
    {
        return preg_replace_callback(
            '/<!--\s*@foreach\s+(\w+)\s+as\s+(\w+)\s*-->(.*?)<!--\s*@endforeach\s*-->/is',
            function ($matches) use ($params) {
                $arrayKey = $matches[1]; // ej: 'themes'
                $itemKey  = $matches[2]; // ej: 't'
                $block    = $matches[3]; // contenido del foreach

                if (!isset($params[$arrayKey]) || !is_array($params[$arrayKey])) {
                    return ''; // No existe o no es array
                }

                $result = '';
                foreach ($params[$arrayKey] as $item) {
                    // Aplanar parámetros del item, ej: ['t.name'=>'...', 't.value'=>'...']
                    $flatItem = $this->processFlattenParams->process([$itemKey => $item]);

                    // Reemplazar placeholders en el bloque
                    $blockCopy = $block;
                    foreach ($flatItem as $k => $v) {
                        $blockCopy = str_replace('{' . $k . '}', (string)$v, $blockCopy);
                    }

                    // Evaluar cualquier @if dentro del bloque con los parámetros combinados
                    $result .= $this->processorIfBlocks->process($blockCopy, $params + $flatItem);
                }

                return $result;
            },
            $content
        );
    }
}
