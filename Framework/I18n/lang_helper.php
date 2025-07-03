<?php

use Framework\I18n\LangManager;

function __(string $key, string $default = ''): string
{
    return LangManager::get($key, $default);
}
