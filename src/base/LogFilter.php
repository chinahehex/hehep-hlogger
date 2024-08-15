<?php
namespace hehe\core\hlogger\base;

/**
 * 日志过滤器记录
 *<B>说明：</B>
 *<pre>
 *  略
 *</pre>
 */
abstract class LogFilter
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
     * 日志是否通过过滤器
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param Message $message
     * @return boolean
     */
    abstract public function check(Message $message):bool;

}
