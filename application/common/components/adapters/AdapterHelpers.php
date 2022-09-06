<?php

namespace Sil\Idp\IdSync\common\components\adapters;

class AdapterHelpers
{
    /**
     * Modifies array in place by adding a $property entry with a blank value if the $property key is not present.
     * @param string $property the key of the entry to look for
     * @param array $items (nested array)
     */
    public static function addBlankProperty(string $property, array &$items)
    {
        foreach ($items as &$next) {
            if (!isset($next[$property])) {
                $next[$property] = '';
            }
        }
    }
}
