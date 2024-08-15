<?php
namespace hehe\core\hlogger;

use hehe\core\hlogger\base\LogContext;
use hehe\core\hlogger\base\LogFilter;
use hehe\core\hlogger\base\LogFormatter;
use hehe\core\hlogger\base\Logger;
use hehe\core\hlogger\base\LogHandler;
use hehe\core\hlogger\contexts\TraceContext;
use hehe\core\hlogger\filters\LevelFilter;
use hehe\core\hlogger\formatters\LineFormatter;
use hehe\core\hlogger\handlers\ByteRotatingFileHandler;
use hehe\core\hlogger\handlers\FileHandler;
use hehe\core\hlogger\handlers\TimedRotatingFileHandler;


/**
 * 日志管理器
 *<B>说明：</B>
 *<pre>
 * 略
 *</pre>
 * @method FileHandler fileHandler(string $logFile = '')
 * @method ByteRotatingFileHandler byteRotatingFileHandler(string $logFile = '',int $maxByte = 0)
 * @method TimedRotatingFileHandler timedRotatingFileHandler(string $logFile = '',string $rotateMode = '',int $interval = 0)
 * @method LineFormatter lineFormatter(string $tpl = '')
 * @method LevelFilter  levelFilter(string $levels  = '')
 */
class LogManager
{
    const DEFAULT_NMAE = 'default';

    protected $bind = true;


    /**
     * 日志格式器列表
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var array
     */
    public $formatters = [
        'default'=>['class'=>'LineFormatter']
    ];

    /**
     * 日志过滤器列表
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var array
     */
    public $filters = [
        self::DEFAULT_NMAE=>['class'=>'LevelFilter','levels'=>'*']
    ];

    /**
     * 日志处理器列表
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var array
     */
    public $handlers = [
        self::DEFAULT_NMAE =>['class'=>'FileHandler']
    ];

    /**
     * 上下文预定义
     * @var array
     */
    public $contexts = [
        self::DEFAULT_NMAE =>[
            'class'=>'TraceContext'
        ],

        'sys' =>[
            'class'=>'SysContext'
        ],
    ];

    /**
     * 默认logger 名称
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var string
     */
    public $defaultLogger = self::DEFAULT_NMAE;

    /**
     * logger 列表
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var array
     */
    public $loggers = [
        self::DEFAULT_NMAE=>[
            'bufferLimit'=>0,
            'handlers'=>['default'],
            'filters'=>['default'],
            'formatter'=>'default',
            'contexts'=>['default','sys'],
        ]
    ];

    /**
     * logger 对象列表
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var Logger[]
     */
    protected $_loggers = [];

    public function __construct(array $attrs = [])
    {
        if (!empty($attrs)) {
            foreach ($attrs as $name=>$value) {
                $this->{$name} = $value;
            }
        }

        // 是否关联日志快捷操作类
        if ($this->bind) {
            Log::$logManager = $this;
        }

    }

    public static function make(array $attrs = [])
    {
        return new static($attrs);
    }

    public function emergency(string $message, array $context = []):void
    {
        $this->log(Log::EMERGENCY,$message,$context);
    }

    public function alert(string $message, array $context = [])
    {
        $this->log(Log::ALERT,$message,$context);
    }

    public function critical(string $message, array $context = [])
    {
        $this->log(Log::CRITICAL,$message,$context);
    }

    public function info(string $message, array $context = []):void
    {

        $this->log(Log::INFO,$message,$context);
    }

    public function error(string $message, array $context = []):void
    {

        $this->log(Log::ERROR,$message,$context);
    }

    public function warning(string $message, array $context = []):void
    {
        $this->log(Log::WARNING,$message,$context);
    }

    public function exception(string $message, array $context = []):void
    {
        $this->log(Log::DEBUG,$message,$context);
    }

    public function debug(string $message, array $context = []):void
    {
        $this->log(Log::DEBUG,$message,$context);
    }

    public function notice(string $message, array $context = []):void
    {
        $this->log(Log::NOTICE,$message,$context);
    }


    public function log(string $level,string $message,array $context = []):void
    {
        $this->getDefaultLogger()->log($level,$message,$context);
    }


    public function getLogger(string $name):Logger
    {
        if (isset($this->_loggers[$name])) {
            return $this->_loggers[$name];
        }

        $logger = $this->newLogger($name);
        $this->_loggers[$name] = $logger;

        return $this->_loggers[$name];
    }

    public function newLogger($name = ''):Logger
    {

        $loggerAttrs = [];
        if (!empty($name)) {
            if (is_string($name) && isset($this->loggers[$name])) {
                $loggerAttrs = $this->loggers[$name];
            } else if (is_array($name)) {
                $loggerAttrs = $name;
            }
        }

        // 消息管道
        if (isset($loggerAttrs['handlers'])) {
            $handlers = [];
            foreach ($loggerAttrs['handlers'] as $handlerName) {
                $handlers[] = $this->newHandler($handlerName);
            }
            $loggerAttrs['handlers'] = $handlers;
        }

        // 消息过滤器
        if (!empty($loggerAttrs['filters'])) {
            $filters = [];
            foreach ($loggerAttrs['filters'] as $filter_attrs) {
                $filters[] = $this->newFilter($filter_attrs);
            }
            $loggerAttrs['filters'] = $filters;
        }

        if (!empty($loggerAttrs['formatter'])) {
            $loggerAttrs['formatter'] = $this->newFilter($loggerAttrs['formatter']);
        }

        $loggerClass = Logger::class;
        $loggerAttrs['logManager'] = $this;
        $logger = new $loggerClass($loggerAttrs);

        return $logger;
    }

    public function getDefaultLogger():Logger
    {
        return $this->getLogger($this->defaultLogger);
    }

    /**
     * 创建新的日志处理器
     * @param string $name
     * @return LogHandler
     */
    public function newHandler($name = ''):LogHandler
    {
        $attrs = [];
        if (is_array($name)) {
            $attrs = $name;
        } else if (isset($this->handlers[$name]))  {
            $attrs = $this->handlers[$name];
        } else if (strpos($name,'\\') !== false) {
            $attrs['class'] = $name;
        }

        $attrs = $this->buildHandlerPropertys($attrs);
        $class = $attrs['class'];
        unset($attrs['class']);

        $attrs['logManager'] = $this;

        /** @var LogHandler $class **/

        return Utils::newInstance($class,$attrs);
    }

    protected function buildHandlerPropertys(array $handler):array
    {
        // 消息格式器
        if (!empty($handler['formatter'])) {
            $handler['formatter'] =  $this->newFormatter($handler['formatter']);
        }

        // 消息过滤器
        if (!empty($handler['filters'])) {
            $filters = [];
            foreach ($handler['filters'] as $filter_attrs) {
                $filters[] = $this->newFilter($filter_attrs);
            }
            $handler['filters'] = $filters;
        }

        $handlerClass = FileHandler::class;
        if (!empty($handler['class'])) {
            if (strpos($handler['class'],'\\') !== false) {// 采用命名空间
                $handlerClass = $handler['class'];
            } else {
                $handlerClass = __NAMESPACE__ . '\\handlers\\' . ucfirst($handler['class']);
            }
        }

        $handler['class'] = $handlerClass;

        return $handler;
    }

    /**
     * 创建消息格式器
     * @param string|array $name 配置名称或配置信息
     * @return LogFormatter
     */
    public function newFormatter($name = ''):LogFormatter
    {
        $attrs = [];
        if (is_array($name)) {
            $attrs = $name;
        } else if (isset($this->formatters[$name]))  {
            $attrs = $this->formatters[$name];
        } else if (strpos($name,'\\') !== false) {
            $attrs['class'] = $name;
        }

        // 实例化对象
        $class = LineFormatter::class;
        if (!empty($attrs['class'])) {
            if (strpos($attrs['class'],'\\') !== false) {// 采用命名空间
                $class = $attrs['class'];
            } else {
                $class = __NAMESPACE__ . '\\formatters\\' . ucfirst($attrs['class']);
            }
            unset($attrs['class']);
        }

        //$attrs['logManager'] = $this;
        /** @var LogFormatter $class **/

        return Utils::newInstance($class,$attrs);
    }

    /**
     * 创建消息过滤器
     * @param string|array $name 配置名称或配置信息
     * @return LogFilter
     */
    public function newFilter($name = ''):LogFilter
    {
        $attrs = [];
        if (is_array($name)) {
            $attrs = $name;
        } else if (isset($this->filters[$name]))  {
            $attrs = $this->filters[$name];
        } else if (strpos($name,'\\') !== false) {
            $attrs['class'] = $name;
        }

        // 实例化对象
        $class = LevelFilter::class;
        if (!empty($attrs['class'])) {
            if (strpos($attrs['class'],'\\') !== false) {// 采用命名空间
                $class = $attrs['class'];
            } else {
                $class = __NAMESPACE__ . '\\filters\\' . ucfirst($attrs['class']);
            }
            unset($attrs['class']);
        }

        /** @var LogFilter $class **/

        return Utils::newInstance($class,$attrs);
    }

    /**
     * 创建日志上下文
     * @param string|array $name 配置名称或配置信息
     * @return LogContext
     */
    public function newContext($name = ''):LogContext
    {
        $attrs = [];
        if (is_array($name)) {
            $attrs = $name;
        } else if (isset($this->contexts[$name]))  {
            $attrs = $this->contexts[$name];
        } else if (strpos($name,'\\') !== false) {
            $attrs['class'] = $name;
        }

        // 实例化对象
        $class = TraceContext::class;
        if (!empty($attrs['class'])) {
            if (strpos($attrs['class'],'\\') !== false) {// 采用命名空间
                $class = $attrs['class'];
            } else {
                $class = __NAMESPACE__ . '\\contexts\\' . ucfirst($attrs['class']);
            }
            unset($attrs['class']);
        }

        /** @var LogContext $class **/

        return Utils::newInstance($class,$attrs);
    }

    /**
     * 注册消息格式器
     * @param array $attrs 属性配置
     * @param string $name 格式器名称
     * @param bool $append 是否追加
     */
    public function setFormatter(string $name,array $attrs = [],bool $append = true):self
    {
        if (isset($this->formatters[$name])) {
            if ($append) {
                $this->formatters[$name] = array_merge($this->formatters[$name],$attrs);
            } else {
                $this->formatters[$name] = $attrs;
            }
        } else {
            $this->formatters[$name] = $attrs;
        }

        return $this;
    }

    /**
     * 注册消息过滤器
     * @param array $attrs 属性配置
     * @param string $name 过滤器名称
     * @param bool $append 是否追加
     */
    public function setFilter(string $name,array $attrs = [],bool $append = true):self
    {
        if (isset($this->filters[$name])) {
            if ($append) {
                $this->filters[$name] = array_merge($this->filters[$name],$attrs);
            } else {
                $this->filters[$name] = $attrs;
            }
        } else {
            $this->filters[$name] = $attrs;
        }

        return $this;
    }

    /**
     * 注册处理器
     * @param array $attrs 属性配置
     * @param string $name 处理器名称
     * @param bool $append 是否追加
     */
    public function setHandler(string $name,array $attrs = [],bool $append = true):self
    {
        if (isset($this->handlers[$name])) {
            if ($append) {
                $this->handlers[$name] = array_merge($this->handlers[$name],$attrs);
            } else {
                $this->handlers[$name] = $attrs;
            }
        } else {
            $this->handlers[$name] = $attrs;
        }

        return $this;
    }

    /**
     * 注册上下文
     * @param array $attrs 属性配置
     * @param string $name 上下文名称
     * @param bool $append 是否追加
     */
    public function setContext(string $name,array $attrs = [],bool $append = true):self
    {
        if (isset($this->contexts[$name])) {
            if ($append) {
                $this->contexts[$name] = array_merge($this->contexts[$name],$attrs);
            } else {
                $this->contexts[$name] = $attrs;
            }
        } else {
            $this->contexts[$name] = $attrs;
        }

        return $this;
    }

    /**
     * 注册上下文
     * @param array $attrs 属性配置
     * @param string $name 上下文名称
     * @param bool $append 是否追加
     */
    public function setLogger(string $name,array $attrs = [],bool $append = true):self
    {
        if (isset($this->loggers[$name])) {
            if ($append) {
                $this->loggers[$name] = array_merge($this->loggers[$name],$attrs);
            } else {
                $this->loggers[$name] = $attrs;
            }
        } else {
            $this->loggers[$name] = $attrs;
        }

        return $this;
    }

    public function __get($name)
    {
        return $this->getLogger($name);
    }

    public function __call($method, $params)
    {
        if (substr($method,-7) === 'Handler') {
            // 创建新的日志处理器
            $params['class'] = $method;
            return $this->newHandler($params);
        } else if (ucfirst(substr($method,-9)) === 'Formatter') {
            // 创建新的日志格式化器
            $params['class'] = $method;
            return $this->newFormatter($params);
        } else if (ucfirst(substr($method,-6)) === 'Filter') {
            // 创建新的日志格式化器
            $params['class'] = $method;
            return $this->newFilter($params);
        } else if (ucfirst(substr($method,-7)) === 'Context') {
            // 创建新的日志上下文
            $params['class'] = $method;
            return $this->newContext($params);
        } else {
            //return call_user_func_array([static::$logManager,$method],$params);
        }
    }

}
