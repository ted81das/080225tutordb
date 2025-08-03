<?php

namespace BitApps\BTCBI_PRO\Triggers\ActionHook;

use ReflectionClass;
use ReflectionProperty;

class ActionHookHelper
{
    public static function convertToSimpleArray($value)
    {
        if (\is_object($value) && class_exists(\get_class($value))) {
            $value = json_decode(wp_json_encode($value), true);
        }

        if (\is_array($value)) {
            foreach ($value as $key => $subValue) {
                $value[$key] = static::convertToSimpleArray($subValue);
            }
        }

        return $value;
    }

    // private static function convertObjectToArray($object)
    // {
    //     $reflection = new ReflectionClass($object);
    //     $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);

    //     $array = [];
    //     foreach ($properties as $property) {
    //         $property->setAccessible(true);
    //         $name = $property->getName();
    //         $value = $property->getValue($object);

    //         $name = preg_replace('/^\x00(?:\*\x00|\w+\x00)/', '', $name);

    //         $array[$name] = static::convertToSimpleArray($value);
    //     }

    //     return $array;
    // }
}
