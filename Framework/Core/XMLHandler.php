<?php

namespace Framework\Core;

use Exception;

class XMLHandler
{
    public $file;
    public $obj;
    public $children;
    public $child;
    public $array;
    public $value;
    public $node;
    public $fix;

    public function __construct($file = "")
    {
        $this->file = $file;

        if (!file_exists($file)) {
            ErrorConsole::handleException(new Exception(get_class($this) . ": No existe el archivo: " . $file));
        } else {
            $this->readXML($file);
        }
    }

    public function readXML($file = "")
    {
        $this->node = [];

        if (!file_exists($file)) {
            ErrorConsole::handleException(new Exception(_FILE_DONT_EXIST . " " . $file));
        }

        $this->obj = @simplexml_load_file($file);

        if (!$this->obj) {
            ErrorConsole::handleException(new Exception(_WARNING_LOADING_FILE . " <b>$file</b>"));
        }
    }

    public function Child($child, $subchild)
    {
        if (!$this->obj) {
            ErrorConsole::handleException(new Exception(_XML_ERROR_CHILD));
        }

        $_value = "";

        foreach ($this->obj->$child as $key => $value) {
            $_value = $value->$subchild;
            if ($_value == "_blank") {
                $_value = "";
            }
        }

        return $_value;
    }

    public function editChild($path, $value)
    {
        try {
            $this->node = $this->obj->xpath($path);

            if (!$this->node || !isset($this->node[0][0])) {
                throw new Exception("No se encontró el nodo XML en el path: $path");
            }

            $this->node[0][0] = empty($value) ? "_blank" : htmlspecialchars($value);
            $this->obj->asXML($this->file);

        } catch (Throwable $e) {
            ErrorConsole::handleException($e);
        }
    }
}
