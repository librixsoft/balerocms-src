<?php

namespace Framework\Http;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Route
{
    public string $sr;

    public function __construct(string $sr = '')
    {
        $this->sr = $sr; // NO hagas trim aquí si quieres rutas con slash
    }
}
