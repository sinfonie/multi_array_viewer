<?php

/**
 * Class for performing translations.
 * It will return translated phrases in the future.
 */

namespace mavLibs\src\helpers;

class translate
{
  public static function translate($translations)
  {
    foreach ($translations as $name => $translation) {
      $data['[' . $name . ']'] = $translation['translation'];
    }
    return (!is_array($data)) ? array() : $data;
  }
}
