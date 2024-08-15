<?php
namespace hehe\core\hlogger\base;

use hehe\core\hlogger\LogManager;
use hehe\core\hlogger\Utils;

/**
 * 日志处理器基类
 */
class LogHandler
{

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
     * 消息过滤器
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @var LogFilter[]
     */
    protected $filters = [];

    /**
     * 日志级别
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

        $this->setLevel($this->levels);
        $this->setCategory($this->categorys);
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

    public function setLogManager(LogManager $logManager): self
    {
        $this->logManager = $logManager;

        return $this;
    }

    /**
     * 默认日志消息格式
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @param Message[] $messages
     * @return string
     */
    protected function messageToString(array $messages):string
    {
        $logs = [];
        foreach ($messages as $message) {
            $logs[] = $message->getMessage();
        }

        return implode("",$logs);
    }

    /**
     * 默认日志消息格式
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @param Message[] $messages
     * @return Message[]
     */
    protected function filterMessage(array $messages):array
    {
        if (empty($messages)) {
            return [];
        }

        if (empty($this->filters) && empty($this->categorys) && empty($this->levels)) {
            return $messages;
        }

        $messageList = [];
        foreach ($messages as $message) {
            // 检查日志级别和类路径
            if (!$this->checkLevelAndCategory($message)) {
                continue;
            }

            // 检查过滤器
            $filterResult = true;
            foreach ($this->filters as $filter) {
                if (!$filter->check($message)) {
                    $filterResult = false;
                    break;
                }
            }

            if (!$filterResult) {
                continue;
            }

            $messageList[] = $message;
        }

        return $messageList;
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

    public function addFilter($filter = ''):LogFilter
    {
        if (is_string($filter) ||  is_array($filter)) {
            $filter = $this->logManager->newFilter($filter);
        }

        $this->filters[] = $filter;

        return $filter;
    }

    public function setFormatter($formatter = ''):LogFormatter
    {
        if (is_string($formatter) ||  is_array($formatter)) {
            $formatter = $this->logManager->newFormatter($formatter);
        }

        $this->formatter = $formatter;

        return $formatter;
    }

    /**
     * 消息列表
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @param Message[] $messages
     * @return void
     */
    public function dispatch(array $messages):void
    {
        $messageList = $this->filterMessage($messages);

        // 设置消息格式
        if (!empty($this->formatter)) {
            foreach ($messageList as $message) {
                if (!$message->hasFormatter()) {
                    $message->setFormatter($this->formatter);
                }
            }
        }

        $this->handleMessages($messageList);
    }


    /**
     * 处理消息列表
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @param Message[] $messages
     * @return void
     */
    public function handleMessages(array $messages):void
    {
        foreach ($messages as $message) {
            $this->handleMessage($message);
        }
    }

    public function handleMessage(Message $messages):void
    {

    }


}
