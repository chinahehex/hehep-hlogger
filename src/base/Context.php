<?php
namespace hehe\core\hlogger\base;

/**
 * 日志上下文类
 */
class Context
{
    protected $ctx = [];

    public function __construct(array $ctx = [])
    {
        $this->ctx = $ctx;
    }

    /**
     * @param string $name 名称
     * @param string|array|\Closure $value 值
     * @return $this
     */
    public function addValue(string $name,$value = ''):self
    {
        $this->ctx[$name] = $value;

        return $this;
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

    public function getExtra():array
    {
        if (isset($this->ctx['extra'])) {
            return $this->ctx['extra'];
        } else {
            return [];
        }
    }

}
