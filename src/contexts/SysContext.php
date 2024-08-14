<?php
namespace hehe\core\hlogger\contexts;

use hehe\core\hlogger\base\LogContext;

/**
 * 系统相关参数
 */
class SysContext extends LogContext
{

    protected $fields = [];

    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);

        $this->fields = [
            'pid' => [$this, 'getPid'],
            'date' => [$this, 'getDate'],
            'rand' => [$this, 'getRand'],
            'time' => [$this, 'getTime'],
            'maxMemory'  => [$this, 'getMaxMemory'],
            'useMemory' => [$this, 'getUseMemory'],
            'n'=>"\n",
        ];
    }

    protected function getCtxData():array
    {
        $ctx = [];
        foreach ($this->fields as $alias => $func) {
            if (is_string($func)) {
                $ctx[$alias] = $func;
            } else {
                $ctx[$alias] = $func;
            }
        }

        return $ctx;
    }

    public function getPid():int
    {
        return getmypid();
    }

    public function getRand(int $num = 6):int
    {
        $num = !empty($num) ? (int)$num : 6 ;
        $min = pow(10, $num - 1);
        $max = pow(10, $num) - 1;

        return mt_rand($min,$max);
    }

    public function getDate(string $dateFormat = 'Y-m-d H:i:s'):string
    {
        return date($dateFormat,time());
    }

    public function getTime():int
    {
        return time();
    }

    public function getMaxMemory():string
    {
        return $this->formatBytes(memory_get_peak_usage(true));
    }

    public function getUseMemory():string
    {
        return $this->formatBytes(memory_get_usage(true));
    }

    public function handle():array
    {
        return $this->getCtxData();
    }
}
