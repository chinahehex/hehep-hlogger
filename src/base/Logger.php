<?php
namespace hehe\core\hlogger\base;

use hehe\core\hlogger\contexts\TraceContext;
use hehe\core\hlogger\filters\LevelFilter;
use hehe\core\hlogger\formatters\LineFormatter;
use hehe\core\hlogger\handlers\ByteRotatingFileHandler;
use hehe\core\hlogger\handlers\FileHandler;
use hehe\core\hlogger\Log;
use hehe\core\hlogger\LogManager;
use hehe\core\hlogger\Utils;

/**
 * 日志记录器
 *<B>说明：</B>
 *<pre>
 *</pre>
 * @method FileHandler fileHandler(string $logFile = '')
 * @method ByteRotatingFileHandler byteRotatingFileHandler(string $logFile = '',int $maxByte = 0)
 * @method LineFormatter lineFormatter(string $tpl = '')
 * @method LevelFilter  levelFilter(string $levels  = '')
 */
class Logger
{
    /**
     * 日志处理器对象集合
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var LogHandler[]
     */
    protected $handlers = [];

    /**
     * 缓冲队列大小
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var int
     */
    protected $bufferLimit = 0;

    /**
     * 日志过滤器对象集合
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @var LogFilter[]
     */
    protected $filters = [];

    /**
     * 消息格式器
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @var LogFormatter
     */
    protected $formatter;

    /**
     * 允许的日志级别集合
     * @var array
     */
    protected $levels = [];

    /**
     * 日志分类
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @var array
     */
    protected $categorys = [];

    /**
     * php日志刷新开启状态
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @var bool
     */
    protected $onphp = true;

    /**
     * 消息对象集合
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @var Message[]
     */
    protected $messages = [];

    /**
     * 日志上下文集合
     * @var array
     */
    protected $contexts = [];

    /**
     * @var LogManager
     */
    protected $logManager;

    public function __construct(array $attrs = [])
    {
        if (!empty($attrs)) {
            foreach ($attrs as $name => $value) {
                $this->{$name} = $value;
            }
        }

        if ($this->onphp) {
            // 注册程序关闭之前执行方法
            register_shutdown_function(function () {
                $this->writeMessage();
                // 确保最后执行的注册方法writeMessage，writeMessage 最后一个执行，注册方法不允许exit
                register_shutdown_function([$this, 'writeMessage'], true);
            });
        }

        $this->setLevel($this->levels);
        $this->setCategory($this->categorys);

        // 格式化日志上下文
//        $this->contexts = Utils::buildContexts($this->contexts);
    }

    /**
     * @param array $levels
     */
    public function setLevel($levels): self
    {
        if (is_string($levels)) {
            $this->levels = explode(',',$levels);
        }

        return $this;
    }

    public function setCategory($categorys):self
    {
        if (is_string($categorys)) {
            $categorys = explode(',',$categorys);
        }

        $this->categorys = Utils::buildCategoryExpression($categorys);

        return $this;
    }



    /**
     * 记录系统不可用日志
     * @param string $message
     * @param array $context
     */
    public function emergency(string $message, array $context = [])
    {
        $this->log(Log::EMERGENCY,$message,$context);
    }

    /**
     * 记录服务不可用日志
     * @param string $message
     * @param array $context
     */
    public function alert(string $message, array $context = [])
    {
        $this->log(Log::ALERT,$message,$context);
    }

    /**
     * 记录严重的日志
     * 比如：应用程序组件不可用，意外异常。
     * @param string $message
     * @param array $context
     */
    public function critical(string $message,array $context = [])
    {
        $this->log(Log::CRITICAL,$message,$context);
    }

    /**
     * 运行时错误不需要立即采取行动，但通常应该
     * @param string $message
     * @param array $context
     */
    public function error(string $message, array $context = []):void
    {
        $this->log(Log::ERROR,$message,$context);
    }

    /**
     * 非错误的异常事件
     * @param string $message
     * @param array $context
     */
    public function warning(string $message, array $context = []):void
    {
        $this->log(Log::WARNING,$message,$context);
    }

    /**
     * 非错误的异常事件
     * @param string $message
     * @param array $context
     */
    public function notice(string $message, array $context = []):void
    {
        $this->log(Log::NOTICE,$message,$context);
    }


    public function info(string $message, array $context = array()):void
    {
        $this->log(Log::INFO,$message,$context);
    }

    public function debug(string $message, array $context = []):void
    {
        $this->log(Log::DEBUG,$message,$context);
    }

    public function exception(string $message, array $context = array()):void
    {
        $this->log(Log::EXCEPTION,$message,$context);
    }

    public function addHandler($handler = ''):LogHandler
    {
        if (is_string($handler) ||  is_array($handler)) {
            $handler = $this->logManager->newHandler($handler);
        }

        $handler->setLogManager($this->logManager);
        $this->handlers[] = $handler;

        return $handler;
    }

    public function addFilter($filter = ''):LogFilter
    {
        if (is_string($filter) ||  is_array($filter)) {
            $filter = $this->logManager->newFilter($filter);
        }

        $this->filters[] = $filter;

        return $filter;
    }

    public function addContext($context = ''):self
    {
        if (is_string($context) ||  is_array($context)) {
            $context = $this->logManager->newContext($context);
        }

        $this->contexts[] = $context;

        return $this;
    }


    public function setFormatter($formatter = ''):self
    {
        if (is_string($formatter) ||  is_array($formatter)) {
            $formatter = $this->logManager->newFormatter($formatter);
        }

        $this->formatter = $formatter;

        return $this;
    }


    /**
     * 添加日志消息
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @param string $level 日志级别
     * @param string $message 日志内容
     * @param array $context 日志上下文
     */
    public function log(string $level, string $message,array $context = []):void
    {
        $context = $this->formatContext($context);
        $options = [
            'msg'=>$message,
            'level'=>$level,
            'context'=>$context,
            'formatter'=>$this->formatter
        ];

        $message = new Message($options);
        $this->addMessage($message);
    }

    public function addMessage(Message $message):void
    {
        // 检查日志级别与分类
        if (!$this->checkLevelAndCategory($message)) {
            return ;
        }

        // 过滤器
        if (!empty($this->filters)) {
            foreach ($this->filters as $filter) {
                if (!$filter->check($message)) {
                    return ;
                }
            }
        }

        $this->messages[] = $message;
        if ($this->isFlush()) {
            $this->writeMessage();
        }
    }

    /**
     * 日志是否通过过滤器
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param Message $message
     * @return boolean
     */
    protected function checkLevelAndCategory(Message $message):bool
    {
        if ($this->checkLevel($message) && $this->checkCategory($message)) {
            return true;
        } else {
            return false;
        }
    }

    protected function checkLevel(Message $message):bool
    {
        if (empty($this->levels)) {
            return true;
        }

        if (in_array('*',$this->levels)) {
            return true;
        }

        if (in_array($message->getLevel(),$this->levels)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 检查日志消息分类
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param Message $message
     * @return boolean
     */
    protected function checkCategory(Message $message):bool
    {
        if (empty($this->categorys)) {
            return true;
        }

        $result = false;
        foreach ($this->categorys as $category) {
            if (preg_match($category, $message->getCategory(), $matches)) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    public function setBufferLimit(int $bufferLimit):self
    {
        $this->bufferLimit = $bufferLimit;

        return $this;
    }

    /**
     * 是否需要写入日志
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     */
    public function isFlush():bool
    {
        $count = count($this->messages);
        if ($this->bufferLimit >= 0 && $count >= $this->bufferLimit) {
            return true;
        }

        return false;
    }

    /**
     * 写入日志
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     */
    public function writeMessage():void
    {
        if (empty($this->messages)) {
            return ;
        }

        $messages = $this->messages;
        $this->messages = [];
        foreach ($this->handlers as $handler) {
            $handler->dispatch($messages);
        }
    }

    /**
     * 格式化上下文
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     */
    protected function formatContext(array $extra = []):array
    {

        $ctx = [
            'extra'=>$extra,
        ];

        $hasTraceContext = false;

        foreach ($this->contexts as $call) {
            if ($call instanceof TraceContext) {
                $hasTraceContext = true;
            }

            if ($call instanceof LogContext) {
                $vars = $call->handle();
            } else if ($call instanceof \Closure) {
                $vars = call_user_func($call);
            } else {
                $vars = $call;
            }

            $ctx = array_merge($ctx,$vars);
        }

        if (!$hasTraceContext) {
            $ctx = array_merge($ctx,$this->logManager->newContext()->handle());
        }

        return $ctx;
    }

    public function __call($method, $params)
    {
        if (substr($method,-7) === 'Handler') {
            // 创建新的日志处理器
            $params['class'] = $method;
            return $this->logManager->newHandler($params);
        } else if (ucfirst(substr($method,-9)) === 'Formatter') {
            // 创建新的日志格式化器
            $params['class'] = $method;
            return $this->logManager->newFormatter($params);
        } else if (ucfirst(substr($method,-6)) === 'Filter') {
            // 创建新的日志格式化器
            $params['class'] = $method;
            return $this->logManager->newFilter($params);
        } else if (ucfirst(substr($method,-7)) === 'Context') {
            // 创建新的日志上下文
            $params['class'] = $method;
            return $this->logManager->newContext($params);
        } else {

        }
    }

}
