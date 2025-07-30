<?php

/**
 * Balero CMS 
 * @author Anibal Gomez <balerocms@gmail.com>
 * @license GNU General Public License
 */

namespace Framework\Http;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Post {
    public string $sr;

    public function __construct(string $sr = '') {
        $this->sr = trim($sr, '/');
    }
}