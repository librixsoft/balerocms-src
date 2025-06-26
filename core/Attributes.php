<?php

#[Attribute(Attribute::TARGET_METHOD)]
class Post {
    public string $sr;

    public function __construct(string $sr = '') {
        $this->sr = $sr;
    }
}

#[Attribute(Attribute::TARGET_METHOD)]
class Get {
    public string $sr;

    public function __construct(string $sr = '') {
        $this->sr = $sr;
    }
}
