<?php
namespace hehe\core\hlogger\formatters;

use hehe\core\hlogger\base\LogFormatter;
use hehe\core\hlogger\base\Message;
use hehe\core\hlogger\Utils;

class LineFormatter extends LogFormatter
{

    /**
     * 消息模板
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @var string
     */
    protected $tpl = '{date:Y-m-d:H:i:s},{level},{msg},file:{file}, line:{line},{cate},{n}';

    /**
     * 替换模板
     *<B>说明：</B>
     *<pre>
     * 原始模板:{msg} {date:Y-m-d:H:i:s> {n}
     *</pre>
     * @var string
     */
    protected $replaceTpl;

    /**
     * 模板变量
     *<B>说明：</B>
     *<pre>
     * 从模板分离出来的标签,比如<msg>
     *</pre>
     * @var array
     */
    protected $tplVars = [];

    protected $_init = false;

    public function __construct(string $tpl = '',array $propertys = [])
    {
        $this->tpl = $tpl;

        parent::__construct($propertys);
    }

    protected function init()
    {
        if ($this->_init) {
            return ;
        }

        $this->_init = true;

        if (!empty($this->tpl)) {
            $this->parseTemplate();
        }
    }

    public function setTpl(string $tpl):self
    {
        $this->tpl = $tpl;

        return $this;
    }

    /**
     * 编译消息格式模板
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     */
    protected function parseTemplate():void
    {
        list($this->replaceTpl,$this->tplVars) = Utils::parseTemplate($this->tpl);
    }

    /**
     * 格式化消息
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @param Message $message
     * @return string
     */
    protected function formatMessage(Message $message):string
    {
        $replaceParams = [];
        $ctx = $message->getContext();
        foreach ($this->tplVars as $tag) {
            list($name,$key,$func_params) = $tag;
            $replaceParams[$key] = $ctx->getValue($name,$func_params);
        }

        return strtr($this->replaceTpl, $replaceParams);
    }

    public function parse(Message $message)
    {
        $this->init();

        return $this->formatMessage($message);
    }
}
