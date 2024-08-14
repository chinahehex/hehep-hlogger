<?php
namespace hehe\core\hlogger\base;


/**
 * 消息日志类
 *<B>说明：</B>
 *<pre>
 *  略
 *</pre>
 */
class Message
{

    /**
     * 日志内容
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var string
     */
    protected $msg;

    /**
     * 日志级别
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var string
     */
    protected $level;

    /**
     * 日志时间
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var string
     */
    protected $time;

    /**
     * 日志上下文
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var Context
     */
    protected $context;

    /**
     * 消息上下文
     * @var Context
     */
    protected $msgContext;

    /**
     * @var LogFormatter
     */
    protected $formatter;

    /**
     * 日志分类
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @var string
     */
    protected $category;

    public function __construct($attrs = [])
    {
        if (!empty($attrs)) {
            foreach ($attrs as $name => $value) {
                $this->{$name} = $value;
            }
        }

        $this->time = microtime(true);

        $class = $this->getClass();
        $func = $this->getFun();
        if (empty($class)) {
            $this->category = $func;
        } else {
            if (!empty($func)) {
                $this->category = $class . ':' . $func;
            } else {
                $this->category = $class;
            }
        }

        $this->context->addValue('level',$this->level);
        $this->context->addValue('cate',$this->category);
        $this->context->addValue('msg',[$this,'getMsg']);
        $this->context->addValue('mdate',[$this,'getDate']);
        $this->context->addValue('mtime',[$this,'getTime']);

    }

    public function getLevel():string
    {
        return $this->level;
    }

    public function getMessage():string
    {
        if (!empty($this->formatter)) {
            return $this->formatter->parse($this);
        } else {
            return $this->msg;
        }
    }

    public function getDate(string $dateFormat = 'Y-m-d H:i:s')
    {
        return date($dateFormat,$this->time);
    }

    public function getTime()
    {
        return $this->time;
    }

    public function getDataTime():\DateTime
    {
        $dateTime = new \DateTime();
        $dateTime->setTimestamp($this->time);

        return $dateTime;
    }

    public function getClass():string
    {
        return $this->context->getValue('class');
    }

    public function getFun():string
    {
        return $this->context->getValue('fn');
    }

    public function getCategory():string
    {
        return $this->category;
    }

    public function getContext():Context
    {
        return $this->context;
    }

    public function getFormatter():LogFormatter
    {
        return $this->formatter;
    }

    public function setFormatter(LogFormatter $formatter):self
    {
        $this->formatter = $formatter;

        return $this;
    }

    public function hasFormatter():bool
    {
        return !empty($this->formatter);
    }

    public function getMsg()
    {
        return $this->msg;
    }

}
