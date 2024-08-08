<?php
namespace hehe\core\hlogger\handlers;

use hehe\core\hlogger\base\Message;

/**
 * 按文件字节轮转日志处理器
 */
class ByteRotatingFileHandler extends RotatingFileHandler
{

    /**
     * 文件最大大小
     *<B>说明：</B>
     *<pre>
     * 单位kb
     *</pre>
     * @var int
     */
    protected $maxByte = 0;

    public function __construct(string $logFile = '',int $maxByte = 0,array $propertys = [])
    {
        $this->maxByte = $maxByte;

        parent::__construct($logFile,$propertys);
    }

    /**
     * @param int $maxByte
     */
    public function setMaxByte(int $maxByte): void
    {
        $this->maxByte = $maxByte;
    }

    protected function initRotate(Message $message)
    {
        $this->rotatFile = $this->getNextRotateFile($this->logFile,$message);
        $this->dirCreated = false;
    }

    public function rotate(Message $message):void
    {

        if ($this->rotatFile === '') {
            $this->initRotate($message);
        }

        // 换算字节单位B
        $maxByte = $this->maxByte * 1024;
        // 清除缓存，防止统计文件大小错误
        clearstatcache();
        if (@filesize($this->rotatFile) > $maxByte) {
            $this->initRotate($message);
        }

        return ;
    }
}
