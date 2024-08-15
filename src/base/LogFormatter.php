<?php
namespace hehe\core\hlogger\base;

/**
 * 日志消息格式化基类
 *<B>说明：</B>
 *<pre>
 * 略
 *</pre>
 */
abstract class LogFormatter
{

    public function __construct(array $attrs = [])
    {
        if (!empty($attrs)) {
            foreach ($attrs as $name=>$value) {
                $this->{$name} = $value;
            }
        }

    }


    /**
     * 解析日志
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @param Message $message
     * @return mixed
     */
    abstract public function parse(Message $message);

}


