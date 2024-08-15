<?php
namespace hehe\core\hlogger\base;

/**
 * 日志上下文
 *<B>说明：</B>
 *<pre>
 * 略
 *</pre>
 */
class LogContext
{

    public function __construct(array $attrs = [])
    {
        if (!empty($attrs)) {
            foreach ($attrs as $name => $value) {
                $this->{$name} = $value;
            }
        }
    }

    protected function formatBytes($bytes)
    {
        $bytes = (int) $bytes;

        if ($bytes > 1024 * 1024) {
            return round($bytes / 1024 / 1024, 2).' MB';
        } elseif ($bytes > 1024) {
            return round($bytes / 1024, 2).' KB';
        }

        return $bytes . ' B';
    }

    /**
     * 获取上下文方法
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @return array
     */
    public function handle():array
    {
        return [];
    }

}


