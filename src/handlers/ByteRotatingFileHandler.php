<?php
namespace hehe\core\hlogger\handlers;

use hehe\core\hlogger\base\Message;

/**
 * 按文件字节轮转日志处理器
 */
class ByteRotatingFileHandler extends RotatingFileHandler
{

    /**
     * 轮转文件格式
     * @var string
     */
    protected $rotatefmt = '{filename}';

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

    public function rotate(Message $message):void
    {
        if ($this->rotateFile === '') {
            $this->rotateFile($message);
        }

        // 清除缓存，防止统计文件大小错误
        clearstatcache();
        if (@filesize($this->rotateFile) > ($this->maxByte * 1024)) {
            // 备份文件
            $this->backupFile($message);
            $this->rotateFile($message);
        }

        return ;
    }
}
