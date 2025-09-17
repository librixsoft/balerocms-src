<?php

namespace Framework\Core;

use Exception;

class XMLHandler
{
    private string $file;
    private \SimpleXMLElement|null $obj = null;

    public function __construct(string $xmlFile)
    {
        if (!file_exists($xmlFile)) {
            throw new Exception("Archivo no encontrado: " . $xmlFile);
        }

        $this->file = $xmlFile;
        $this->readXML();
    }

    private function readXML(): void
    {
        $this->obj = @simplexml_load_file($this->file);

        if (!$this->obj) {
            throw new Exception("Error cargando XML: " . $this->file);
        }
    }

    public function get(string $path): string
    {
        $nodes = $this->obj->xpath($path);
        if (!$nodes || !isset($nodes[0])) {
            return '';
        }

        $value = (string)$nodes[0];
        return $value === '_blank' ? '' : $value;
    }

    public function set(string $path, string $value): void
    {
        $nodes = $this->obj->xpath($path);
        if (!$nodes || !isset($nodes[0])) {
            throw new Exception("No se encontró el nodo XML en el path: $path");
        }

        $nodes[0][0] = $value === '' ? '_blank' : htmlspecialchars($value);
        $this->obj->asXML($this->file);
    }
}
