<?php

namespace hehe\core\hlogger;

use hehe\core\hlogger\base\LogContext;
use hehe\core\hlogger\base\LogFilter;
use hehe\core\hlogger\base\LogFormatter;
use hehe\core\hlogger\base\Logger;
use hehe\core\hlogger\base\LogHandler;
use Psr\Log\LogLevel;

/**
 * @method static Logger getDefaultLogger()
 * @method static Logger getLogger(string $name)
 * @method static Logger newLogger(string|array $name)
 * @method static LogHandler newHandler(string|array $name)
 * @method static LogFormatter newFormatter(string|array $name)
 * @method static LogFilter newFilter(string|array $name)
 * @method static LogContext newContext(string|array $name)
 * @method static void emergency(string $message, array $context = [])
 * @method static void alert(string $message, array $context = [])
 * @method static void critical(string $message, array $context = [])
 * @method static void info(string $message, array $context = [])
 * @method static void error(string $message, array $context = [])
 * @method static void warning(string $message, array $context = [])
 * @method static void debug(string $message, array $context = [])
 * @method static void notice(string $message, array $context = [])
 * @method static void log(string $level,string $message,array $context = [])
 * @method static self setFormatter(array $attrs = [],string $name = 'default',bool $append = true)
 * @method static self setFilter(array $attrs = [],string $name = 'default',bool $append = true)
 * @method static self setHandler(array $attrs = [],string $name = 'default',bool $append = true)
 * @method static self setContext(array $attrs = [],string $name = 'default',bool $append = true)
 *
 */
class Log
{

    const EMERGENCY = LogLevel::EMERGENCY;// 系统不可用
    const ALERT     = LogLevel::ALERT;
    const CRITICAL  = LogLevel::CRITICAL;// 严重错误
    const ERROR     = LogLevel::ERROR;// 运行时错误，但是不需要立刻处理。
    const WARNING   = LogLevel::WARNING;// 出现非错误的异常
    const NOTICE    = LogLevel::NOTICE;// 普通但是重要的事件。
    const INFO      = LogLevel::INFO; // 关键事件
    const DEBUG     = LogLevel::DEBUG;// 详细的debug信息
    const EXCEPTION = 'exception';

    /**
     * 默认日志记录器
     * @var string
     */
    public static $logger;

    /**
     * @var LogManager
     */
    public static $logManager;

    public static function init()
    {
        if (is_null(static::$logManager)) {
            static::$logManager = static::getLogManager();
        }
    }

    public static function reset()
    {
        static::$logManager = null;
        static::$logger = null;
    }

    public static function getLogManager(array $attrs = []):LogManager
    {
        return LogManager::make([
            'defaultLogger'=>static::$logger
        ]);
    }

    public static function __callStatic($method, $params)
    {
        if (is_null(static::$logManager)) {
            static::init();
        }

        return call_user_func_array([static::$logManager,$method],$params);
    }


}
