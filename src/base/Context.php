<?php
namespace hehe\core\hlogger\base;

/**
 * 上下文类
 */
class Context
{
    protected $ctx = [];

    public function __construct(array $ctx = [])
    {
        $this->ctx = $ctx;
    }

    /**
     * 获取上下文值
     * @param string $name
     * @param array $funcParams
     * @return array|string
     */
    public function getValue(string $name,array $funcParams = [])
    {
        if (isset($this->ctx[$name])) {
            $ctx_value =  $this->ctx[$name];
        } else if (isset($this->ctx['extra'][$name])) {
            $ctx_value =  $this->ctx['extra'][$name];
        } else {
            $ctx_value = '';
        }

        if (is_array($ctx_value) || $ctx_value instanceof \Closure) {
            if (!empty($funcParams)) {
                $value = call_user_func_array($ctx_value,$funcParams);
            } else {
                $value = call_user_func($ctx_value);
            }
        } else {
            $value = $ctx_value;
        }

        return $value;
    }

    public function getAll()
    {
        $ctx = [];
        foreach ($this->ctx as $name=>$value) {
            $ctx[$name] = $this->getValue($name);
        }

        return $ctx;
    }

}
