<?php
namespace hehe\core\hlogger\filters;

use hehe\core\hlogger\base\LogFilter;
use hehe\core\hlogger\base\Message;
use hehe\core\hlogger\Utils;

class LevelFilter extends LogFilter
{
    /**
     * 日志级别
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
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

    public function __construct($levels = '',$categorys = '',array $propertys = [])
    {
        $this->levels = $levels;
        $this->categorys = $categorys;

        parent::__construct($propertys);

        $this->setLevel($this->levels);
        $this->setCategory($this->categorys);
    }

    public function setLevel($levels):self
    {
        if (is_string($levels)) {
            $levels = explode(',',$levels);
        }

        $this->levels = $levels;

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

    /**
     * 检查日志消息级别
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param Message $message
     * @return boolean
     */
    public function checkLevel(Message $message):bool
    {
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
    public function checkCategory(Message $message):bool
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

    /**
     * 日志是否通过过滤器
     *<B>说明：</B>
     *<pre>
     *  略
     *</pre>
     * @param Message $message
     * @return boolean
     */
    public function check(Message $message):bool
    {
        if ($this->checkLevel($message) && $this->checkCategory($message)) {
            return true;
        } else {
            return false;
        }
    }
}
