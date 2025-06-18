<?php
namespace hehe\core\hlogger\contexts;

use hehe\core\hlogger\base\LogContext;
use hehe\core\hlogger\Log;
use Throwable;

/**
 * 异常上下文
 * 从异常中获取上下文信息
 */
class ExceptionContext extends LogContext
{
    /**
     * @var Throwable
     */
    protected $errorException;

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

    public function __construct(Throwable $e)
    {
        $this->errorException = $e;
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
        $trace = $this->errorException->getTrace();
        $class = '';
        $function = [];
        if (!empty($trace[0])) {
            $frame = $trace[0];
            $class = $frame['class'] ?? '';
            $function = $frame['function'] ?? '';
        }
        unset($trace);
        $ctx = [
            'file' => $this->errorException->getFile(),
            'line' => $this->errorException->getLine(),
            'class' => $class,
            'fn' => $function,
        ];

        return $ctx;
    }
}
