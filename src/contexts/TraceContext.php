<?php
namespace hehe\core\hlogger\contexts;

use hehe\core\hlogger\base\LogContext;
use hehe\core\hlogger\Log;

/**
 * 系统相关参数
 */
class TraceContext extends LogContext
{

    /**
     * 忽略类
     * @var string[]
     */
    protected $skipClasses = [];

    /**
     * 忽略函数
     * @var string[]
     */
    protected $skipFuns = array(
        'call_user_func',
        'call_user_func_array',
    );

    public function __construct(array $skipClasses = [],array $skipFuns = [],array $propertys = [])
    {
        $this->skipClasses = array_merge([(new \ReflectionClass(Log::class))->getNamespaceName()],$skipClasses);
        $this->skipFuns = array_merge($this->skipFuns,$skipFuns);

        parent::__construct($propertys);
    }

    private function isTraceClassOrSkippedFunction(array $trace, $index)
    {
        if (!isset($trace[$index])) {
            return false;
        }

        return isset($trace[$index]['class']) || in_array($trace[$index]['function'], $this->skipFuns);
    }

    public function handle():array
    {
        $trace = debug_backtrace((PHP_VERSION_ID < 50306) ? 2 : DEBUG_BACKTRACE_IGNORE_ARGS);
        array_shift($trace);
        array_shift($trace);
        $i = 0;
        while ($this->isTraceClassOrSkippedFunction($trace, $i)) {
            if (isset($trace[$i]['class'])) {
                foreach ($this->skipClasses as $part) {
                    if (strpos($trace[$i]['class'], $part) !== false) {
                        $i++;
                        continue 2;
                    }
                }
            } elseif (in_array($trace[$i]['function'], $this->skipClasses)) {
                $i++;
                continue;
            }

            break;
        }

        $ctx = [
            'file' => isset($trace[$i - 1]['file']) ? $trace[$i - 1]['file'] : '',
            'line' => isset($trace[$i - 1]['line']) ? $trace[$i - 1]['line'] : '',
            'class' => isset($trace[$i]['class']) ? $trace[$i]['class'] : '',
            'fn' => isset($trace[$i]['function']) ? $trace[$i]['function'] : '',
        ];

        return $ctx;
    }
}
