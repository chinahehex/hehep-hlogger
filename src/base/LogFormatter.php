<?php
namespace hehe\core\hlogger\base;

/**
 * 日志消息格式化类
 *<B>说明：</B>
 *<pre>
 * 日志标签:
 * m:日志内容
 * cat:日志业务分类
 * l:日志级别
 * n:换行符
 * d:日期,可自定义输出格式<d:Y-m-d:H:i:s>
 * time:毫秒时间戳
 * pid:进程号
 * line:日志打点对应的行号,
 * file:文件路径
 * class:日志打点对应的类路径
 * fun:日志打点对应的方法名
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
     * 获取换号符号
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @return string
     */
    protected function formatLinebreak()
    {
        return "\n";
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


