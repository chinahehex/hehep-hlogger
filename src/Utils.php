<?php
namespace hehe\core\hlogger;

use hehe\core\hlogger\contexts\TraceContext;

class Utils
{
    const TPL_REGEX = '/\{([a-zA-Z0-9]+):?([^\}]+)?\}/';

    /**
     * 创建实例
     * 用于创建处理器 ,过滤器，格式器，上下文对象
     * @param string $class
     * @param array $params
     * @return mixed
     */
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

    /**
     * 解析模板
     * @param string $template 模版字符串
     * @return array<替换模板, 模板变量>
     */
    public static function parseTemplate(string $template):array
    {
        $matches = [];
        $tagIndex = 0;
        $replaceTemplate = preg_replace_callback(self::TPL_REGEX,function($matches) use (&$tagIndex){
            $tagname = '<'.$matches[1] . $tagIndex . '>';
            $tagIndex++;
            return $tagname;
        },$template);

        $templateVars = [];
        if (preg_match_all(self::TPL_REGEX, $template, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
            foreach ($matches as $index=>$param) {
                $name = $param[1][0];
                if (isset($param[2][0])) {
                    $func_params = explode(',',$param[2][0]);
                } else {
                    $func_params = [];
                }
                // 判断是否有参数
                $tagName = '<' .$name . $index .  '>';
                $templateVars[] = [$name,$tagName,$func_params];
            }
        }

        return [$replaceTemplate,$templateVars];
    }

    /**
     * 获取指定目录下的所有文件
     * @param string $path
     * @param string $pattern 匹配正则
     * @return array
     */
    public static function getFiles(string $path,string $pattern = ''):array
    {
        if (!is_dir($path)) {
            return [];
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        $logFiles = [];
        if ($pattern !== '') {
            foreach ($files as $file) {
                if ($file->isDir()) {
                    continue;
                }

                if (preg_match($pattern, $file->getFilename())) {
                    $logFiles[] = $file->getPathname();
                }
            }
        } else {
            foreach ($files as $file) {
                if ($file->isDir()) {
                    continue;
                }

                $logFiles[] = $file->getPathname();
            }
        }

        return $logFiles;
    }

}
