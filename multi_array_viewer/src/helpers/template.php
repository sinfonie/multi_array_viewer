<?php

/**
 * Class responsible for finding template variables and substituting the appropriate values.
 */

namespace multiArrayViewer\src\helpers;

class template
{
    public static function create(string $template, array $data)
    {
        $matches = self::getMatchAll($template);
        $template = str_replace('%', '%%', $template);
        $template = preg_replace('/{.*?}/', '%s', $template);
        $array = [];
        foreach ($matches[1] as $key) {
            $array[] = $data[$key];
        }
        return vsprintf($template, $array);
    }

    private static function getMatchAll($template)
    {
        preg_match_all('/{(.*?)}/', $template, $matches);
        return $matches;
    }
}
