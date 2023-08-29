<?php

namespace timanthonyalexander\BaseApi\module\ParameterTools;

class ParameterTools
{
    public static function clearText(?string $text, bool $clean = false): ?string
    {
        if (is_null($text)) {
            return null;
        }

        $text = str_replace('<div>', PHP_EOL, $text);

        $text = strip_tags($text);

        if ($clean === true) {
            $text = preg_replace('/[^a-zA-Z0-9 ]/', '', $text);
        }

        return trim($text ?? '');
    }
}
