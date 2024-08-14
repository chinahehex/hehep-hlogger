<?php
namespace hehe\core\hlogger\handlers;

use hehe\core\hlogger\base\LogHandler;
use hehe\core\hlogger\base\Message;
use hehe\core\hlogger\Log;

/**
 * syslog日志处理器
 */
class SyslogHandler extends LogHandler
{
    protected $ident;
    protected $logopts;
    protected $facility;

    protected $syslogLevels = array(
        Log::DEBUG     => LOG_DEBUG,
        Log::INFO      => LOG_INFO,
        Log::NOTICE    => LOG_NOTICE,
        Log::WARNING   => LOG_WARNING,
        Log::ERROR     => LOG_ERR,
        Log::CRITICAL  => LOG_CRIT,
        Log::ALERT     => LOG_ALERT,
        Log::EMERGENCY => LOG_EMERG,
    );

    protected $_init = false;

    public function __construct(string $ident = '',int $logopts = LOG_PID,int $facility = LOG_USER,array $propertys = [])
    {
        $this->ident = $ident;
        $this->logopts = $logopts;
        $this->facility = $facility;

        parent::__construct($propertys);
    }

    public function close()
    {
        closelog();
    }

    protected function init()
    {
        if ($this->_init) {
            return ;
        }

        if (!openlog($this->ident, $this->logopts, $this->facility)) {
            throw new \LogicException('syslog ident "'.$this->ident.'" and facility "'.$this->facility.'" error');
        }

        $this->_init = true;
    }

    public function handleMessage(Message $message):void
    {
        $this->init();

        // 日志写入文件
        syslog($this->syslogLevels[$message->getLevel()], $message->getMessage());
    }

}
