<?php
namespace hehe\core\hlogger\handlers;

use hehe\core\hlogger\base\LogHandler;
use hehe\core\hlogger\base\Message;

/**
 * 文件日志处理器
 * 文件名称规则:超过指定的文件大小,重新生成新的日志文件
 */
class FileHandler extends LogHandler
{
    /**
     * 文件名
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     * @var string
     */
    protected $logFile = '';

    /**
     * 是否使用锁
     * @var bool
     */
    protected $useLock = false;

    /**
     * 已经创建的目录
     * @var bool
     */
    protected $dirCreated;

    public function __construct(string $logFile = '',array $propertys = [])
    {
        $this->logFile = $logFile;

        parent::__construct($propertys);

    }

    /**
     * @param string $logFile
     */
    public function setLogFile(string $logFile): void
    {
        $this->logFile = $logFile;
    }

    protected function createLogDir(string $logFile)
    {
        if (!empty($this->dirCreated)) {
            return;
        }

        $fileDir = dirname($logFile);
        if (!is_dir($fileDir)) {
            mkdir($fileDir,0755,true);
        }

        $this->dirCreated = true;
    }

    /**
     * 日志写入文件
     * @param string $file
     */
    protected function writeFile(Message $message,string $file):void
    {
        // 消息写入文件
        $file = fopen($file, "a");
        if ($this->useLock) {
            flock($file, LOCK_EX);
        }

        fwrite($file,$message->getMessage());

        if ($this->useLock) {
            flock($file, LOCK_UN);
        }

        fclose($file);
    }

    public function handleMessage(Message $message):void
    {
        // 创建目录
        $this->createLogDir($this->logFile);

        // 日志写入文件
        $this->writeFile($message,$this->logFile);
    }

}
