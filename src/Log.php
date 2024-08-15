<?php
namespace hehe\core\hlogger;

use hehe\core\hlogger\base\LogContext;
use hehe\core\hlogger\base\LogFilter;
use hehe\core\hlogger\base\LogFormatter;
use hehe\core\hlogger\base\Logger;
use hehe\core\hlogger\base\LogHandler;

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
 * @method static self setFormatter(string $name = '',array $attrs = [],bool $append = true)
 * @method static self setFilter(string $name = '',array $attrs = [],bool $append = true)
 * @method static self setHandler(string $name = '',array $attrs = [],bool $append = true)
 * @method static self setContext(string $name = '',array $attrs = [],bool $append = true)
 * @method static self setLogger(string $name = '',array $attrs = [],bool $append = true)
 *
 */
class Log
{

    const EMERGENCY = 'emergency';// 系统不可用
    const ALERT     = 'alert';// 记录紧急日志
    const CRITICAL  = 'critical';// 严重错误
    const ERROR     = 'error';// 运行时错误，但是不需要立刻处理。
    const WARNING   = 'warning';// 出现非错误的异常
    const NOTICE    = 'notice';// 普通但是重要的事件。
    const INFO      = 'info'; // 关键事件
    const DEBUG     = 'debug';// 详细的debug信息

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
