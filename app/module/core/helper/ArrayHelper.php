<?php

// phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

namespace Tymy\Module\Core\Helper;

use Exception;
use Nette\Database\Table\ActiveRow;
use SplObjectStorage;
use Tymy\Module\Core\Model\BaseModel;

/**
 * Description of ArrayHelper
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 14. 1. 2021
 */
class ArrayHelper
{
    /** used by equal() for comparing floats */
    private const EPSILON = 1e-10;

    /**
     * Get array / value from two-dimensional array, based on property of children array.
     * This function expects that all sub-arrays have the same keys
     *
     * @param array $inputArray E.g. [[id => 1, value => foo],[id => 2, value => bar]]
     * @param mixed $where E.g. id
     * @param string $equals E.g. 1
     * @param string $outputField E.g. value
     * @return array|mixed If output field is specified, returns exact output field from sub-array, found by $key and $value. E.g. would return "foo" or [id => 1, value => foo] if $outputField is not specified. Return null if nothing has been found
     */
    public static function subValue(array $inputArray, $where, string $equals = null, string $outputField = null)
    {
        $foundIndex = array_search($equals, array_column($inputArray, $where));
        if ($foundIndex === false) {
            return null;
        }
        $foundKey = array_keys($inputArray)[$foundIndex];
        return $outputField ? $inputArray[$foundKey][$outputField] : $inputArray[$foundKey];
    }

    /**
     * Get sub-arrrays frorm multidimensional array, where property equals field or is in array of fields
     *
     * @param mixed $where
     * @param string|string[]|int|int[]|bool $equals
     * @return array
     */
    public static function filter(array $inputArray, $where, mixed $equals)
    {
        return array_filter($inputArray, function ($elm) use ($where, $equals) {
            if (is_string($equals) || is_int($equals) || is_bool($equals)) {
                return $elm[$where] === $equals;
            } elseif (is_array($equals)) {
                return in_array($elm[$where], $equals);
            }
        });
    }

    /**
     * Sum specified $sumKey field in all sub-arrays, filtered by $whereKey and $whereValue conditions.
     *
     * @param string|int $whereKey
     */
    public static function sum(array $inputArray, $whereKey, string $whereValue, string $sumKey): float
    {
        $sum = 0.0;

        array_walk($inputArray, function ($elm) use (&$sum, $whereKey, $whereValue, $sumKey) {
            if ($elm[$whereKey] == $whereValue) {
                $sum += (float) $elm[$sumKey];
            }
        });

        return $sum;
    }

    /**
     *
     * Compares two structures and checks expectations. The identity of objects, the order of keys
     * in the arrays and marginally different floats are ignored.
     *
     * Copyright (c) 2009 David Grudl (https://davidgrudl.com)
     *
     * @param mixed $expected
     * @param mixed $actual
     * @param int $level = 0 (internal usage to avoid recursion overflow)
     * @param type $objects
     * @throws Exception
     */
    public static function isEqual($expected, $actual, int $level = 0, $objects = null): bool
    {
        switch (true) {
            case $level > 10:
                throw new Exception('Nesting level too deep or recursive dependency.');

            case is_float($expected) && is_float($actual) && is_finite($expected) && is_finite($actual):
                $diff = abs($expected - $actual);
                return ($diff < self::EPSILON) || ($diff / max(abs($expected), abs($actual)) < self::EPSILON);

            case is_object($expected) && is_object($actual) && get_class($expected) === get_class($actual):
                $objects = $objects !== null ? clone $objects : new SplObjectStorage();
                if (isset($objects[$expected])) {
                    return $objects[$expected] === $actual;
                } elseif ($expected === $actual) {
                    return true;
                }
                $objects[$expected] = $actual;
                $objects[$actual] = $expected;
                $expected = (array) $expected;
                $actual = (array) $actual;
            // break omitted

            case is_array($expected) && is_array($actual):
                ksort($expected, SORT_STRING);
                ksort($actual, SORT_STRING);
                if (array_keys($expected) !== array_keys($actual)) {
                    return false;
                }

                foreach ($expected as $value) {
                    if (!self::isEqual($value, current($actual), $level + 1, $objects)) {
                        return false;
                    }
                    next($actual);
                }
                return true;

            default:
                return $expected === $actual;
        }
    }

    /**
     * Sort entities array according another array specifying the actual sort order
     */
    public static function idSort(array &$entities, array $idArray, string $column = "id"): void
    {
        usort($entities, function ($a, $b) use ($column, $idArray) {
            $posA = array_search($a[$column], $idArray);
            $posB = array_search($b[$column], $idArray);
            return $posA - $posB;
        });
    }

    /**
     * Transform array of entities to array of its json representation
     * @param BaseModel[]|null $entities
     */
    public static function arrayToJson(?array $entities = null): array
    {
        if (empty($entities)) {
            return [];
        }

        return array_map(function ($entity) {
            /* @var $entity BaseModel */
            return $entity->jsonSerialize();
        }, $entities);
    }

    /**
     * Return list of fields from array of BaseModels
     *
     * @param BaseModel[] $entities
     * @return int[]
     */
    public static function entityFields(string $fieldName, array $entities): array
    {
        $values = [];

        $getter = "get" . ucfirst($fieldName);

        foreach ($entities as $entity) {
            if (!$entity instanceof BaseModel) {
                continue;
            }
            $values[] = $entity->$getter();
        }

        return $values;
    }

    /**
     * Return list of ids from array of BaseModels
     *
     * @param BaseModel[] $entities
     * @return int[]
     */
    public static function entityIds(array $entities): array
    {
        return self::entityFields("id", $entities);
    }

    /**
     * Checks whether this array is actually an associative array with numeric indexes
     */
    public static function isAssoc(array $array): bool
    {
        if (array() === $array) {
            return false;
        }

        return array_keys($array) !== range(0, count($array) - 1);
    }

    /**
     * Compare array values - check if values in each array are equal, nevermind order, nevermind keys
     */
    public static function valuesEqual(array $array1, array $array2): bool
    {
        return !empty(self::diffAbs($array1, $array2));
    }

    /**
     * Function to process absolute array diff - will return array of items which are not in BOTH arrays
     */
    public static function diffAbs(array $array1, array $array2): array
    {
        return array_merge(
            array_diff($array1, $array2),
            array_diff($array2, $array1),
        );
    }

    /**
     * Cast all entities in array to int
     */
    public static function ints(array $array): array
    {
        return array_map('intval', $array);
    }

    /**
     * Explode string by separator and cast all explded values to int
     */
    public static function explodeInts(string $separator, string $string): array
    {
        return $string !== '' && $string !== '0' ? array_map('intval', explode($separator, $string)) : [];
    }



    /**
     * Merge one or more arrays and return their unique values
     * @param array $arrays Input arrays
     * @return array|null on failure
     */
    public static function array_merge_unique(array $arrays): ?array
    {
        if (!is_array($arrays)) {
            return null;
        }

        return array_unique(array_merge(...array_values($arrays)));
    }

    /**
     * Convert array of ActiveRow objects into two-dimensional array.
     * Rows of type array are skipped, but outputed.
     * Rows of type ActiveRow are transformed into array and outputed.
     * Any other types of rows are skipped and not outputed.
     * Array keys and order are maintained.
     */
    public static function rowsToArrays(array $rows): array
    {
        $arrays = [];
        foreach ($rows as $key => $row) {
            if (is_array($row)) {
                $arrays[$key] = $row;
            } elseif ($row instanceof ActiveRow) {
                $arrays[$key] = $row->toArray();
            }
        }

        return $arrays;
    }

    /**
     * Transform two-dimensional array to key-pair array, using array index to be taken as key and array index to be treated as value.
     * If both keyIndex and valueIndex are null, returns the input array unchanged
     *
     * @param string|null $keyIndex - If null, output array is numerically indexed
     * @param string|null $valueIndex - If null, returns full item specified by its key
     */
    public static function pairs(array $inputArray, ?string $keyIndex = null, ?string $valueIndex = null): array
    {
        if (is_null($keyIndex) && is_null($valueIndex)) {
            return $inputArray;
        }

        $pairs = [];
        foreach ($inputArray as $value) {
            $extractedValue = is_null($valueIndex) ? $value : $value[$valueIndex];

            if (is_null($keyIndex)) {
                $pairs[] = $extractedValue;
            } else {
                $pairs[$value[$keyIndex]] = $extractedValue;
            }
        }
        return $pairs;
    }

    /**
     * Transform two-dimensional array to key->value pair array, getting key from object getter, formed from property name
     * If no value is specified, returns complete model
     * @param BaseModel[] $inputArray
     * @return array|BaseModel[]
     */
    public static function pairsEntity(array $inputArray, string $keyProperty = 'id', ?string $valueProperty = null): array
    {
        $pairs = [];
        $keyGetter = "get" . ucfirst($keyProperty);
        if ($valueProperty) {
            $valueGetter = "get" . ucfirst($valueProperty);
        }
        foreach ($inputArray as $baseEntity) {
            $pairs[$baseEntity->$keyGetter()] = isset($valueGetter) ? $baseEntity->$valueGetter() : $baseEntity;
        }
        return $pairs;
    }
}
