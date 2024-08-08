<?php
namespace hehe\core\hlogger;

use hehe\core\hlogger\contexts\TraceContext;

class Utils
{
    public static function newInstance(string $class, array $params = [])
    {
        $parameters = static::getConstructor($class);
        $my_args = [];
        $propertys = [];
        $hasPropertys = false;

        // 分离属性
        foreach ($params as $key => $value) {
            if (is_numeric($key)) {
                $my_args[$key] = $value;
            } else if (is_string($key)) {
                $propertys[$key] = $value;
            }
        }

        $args = [];
        foreach ($parameters as $index => $param) {
            list($name, $defval) = $param;
            if ($name === 'propertys') {
                $args[$index] = $propertys;
                $hasPropertys = true;
            } else {
                if (isset($my_args[$index])) {
                    $args[$index] = $my_args[$index];
                } else {
                    $args[$index] = $defval;
                }
            }
        }

        $object = new $class(...$args);
        if (!$hasPropertys && !empty($propertys)) {
            // 通过设置的方式注入
            foreach ($propertys as $name => $value) {
                $func = 'set' . ucfirst($name);
                if (method_exists($object, $func)) {
                    $object->{$func}($value);
                }
            }
        }

        return $object;
    }

    public static function getConstructor(string $class)
    {
        $parameters = (new \ReflectionClass($class))->getConstructor()->getParameters();
        $args = [];
        foreach ($parameters as $index => $param) {
            $name = $param->getName();
            $defval = null;
            if ($param->isDefaultValueAvailable()) {
                $defval = $param->getDefaultValue();
            }

            $args[$index] = [$name, $defval];
        }

        return $args;
    }

    public static function buildCategoryExpression(array $categorys): array
    {
        $categoryList = [];
        foreach ($categorys as $category) {
            // 判断是否正则
            if (substr($category, 0, 1) == '/' && substr($category, -1) == '/') {
                $categoryList[] = $category;
            } else {
                $replaceParams = [
                    '\\' => '\\\\',
                ];

                $category = strtr($category, $replaceParams);
                if (strpos($category, '*') !== false) {
                    $category = '/^' . str_replace('*', '(.*)', $category) . '$/';
                } else {
                    $category = '/^' . $category . '(.*)' . '$/';
                }

                $categoryList[] = $category;
            }
        }

        return $categoryList;
    }

}
