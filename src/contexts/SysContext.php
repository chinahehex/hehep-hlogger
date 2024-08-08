<?php
namespace hehe\core\hlogger\contexts;

use hehe\core\hlogger\base\LogContext;

/**
 * 系统相关参数
 */
class SysContext extends LogContext
{

    protected $fields = [

    ];

    public function __construct(array $attrs = [])
    {
        parent::__construct($attrs);

        $this->fields = [
            'pid' => [$this, 'getPid'],
            'maxMemory'  => [$this, 'getMaxMemory'],
            'useMemory' => [$this, 'getUseMemory'],
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
