<?php


namespace Sil\Idp\IdSync\common\components\helpers;

class Utils
{
    /**
     * Recursively collapse a multi-dimensional array into a single-dimensional array.
     * Array keys are combined using a dot to separate levels of the array. For instance:
     * ```
     * [
     *     'a' => [
     *         'x' => 'data1',
     *     ],
     *     'b' => 'data2',
     * ]
     * ```
     * will be collapsed to
     * ```
     * [
     *     'a.x' => 'data1',
     *     'b' => 'data2',
     * ]
     * ```
     *
     * @param array $childArray array to be collapsed
     * @param string $parentKey array key associated with $childArray in the parent array
     * @return array
     */
    public static function arrayCollapseRecursive($childArray, $parentKey = '')
    {
        $newArray = [];

        foreach ($childArray as $key => $value) {
            $combinedKey = (empty($parentKey) ? '' : $parentKey . '.') . $key;
            if (is_array($value)) {
                $newArray = array_merge($newArray, self::arrayCollapseRecursive($value, $combinedKey));
            } else {
                $newArray[$combinedKey] = $value;
            }
        }

        return $newArray;
    }
}
