<?php
namespace hehe\core\hlogger\handlers;

use hehe\core\hlogger\base\Message;
use hehe\core\hlogger\Utils;

/**
 * 按日期轮转文件处理器
 * 略
 */
class TimedRotatingFileHandler extends RotatingFileHandler
{

    /**
     * 轮转格式
     * @var string
     */
    protected $rotateFormat = 'M';

    protected $interval = 0;

    protected $rotateDateFormat = 'Y-m-d';

    protected $nowRotateDate = null;

    protected $nextRotateDate = null;


    public function __construct(string $logFile = '',string $rotateFormat = '',int $interval = 0,array $propertys = [])
    {
        $this->rotateFormat = $rotateFormat;
        $this->interval = $interval;

        parent::__construct($logFile,$propertys);
    }

    protected function reRotateFile(Message $message)
    {
        if (is_null($nowRotateDate)) {
            $this->nowRotateDate = new \DateTime('now');
            $this->nextRotateDate = $this->buildNextRotateDate($this->nowRotateDate);
        }

        $msgDatetime = $message->getDateTime();
        if ($msgDatetime > $this->nextRotateDate) {
            $this->nowRotateDate = $msgDatetime;
        }

        // 生成
        $fileDir = dirname($this->logFile);

        // 生成旋转日期目录
        $rotateDateArr = explode(' ',date($this->rotateDateFormat,microtime(true));

        $rotateDir = '';
        $rotateDir = str_replace('-','/',$rotateDateArr[0]);
        if (isset($rotateDateArr[1])) {
            $rotateDir = $rotateDir . '/' . str_replace(':','/',$rotateDateArr[1]);
        }

        $this->logFile = $fileDir . '/' . $rotateDir . '/' . basename($this->logFile);
    }

    protected function getNextRotateFile(Message $message):DateTime
    {
        //  ‘S’、‘M’、‘H’、‘D’、‘W’ 和 ‘midnight’
        // 转大写
        $dateType = strtoupper($this->rotateFormat);
        $mdatetime = $message->getDateTime();
        
        if ($dateType === 'D') {
            // 天
            $nowRotateDate->modify('+1 day');
        } else if ($dateType === 'H') {
            // 小时
            $nowRotateDate->modify('+1 hour');
        } else if ($dateType === 'W') {
            // 周
            $nowRotateDate->modify('+1 hour');
        } else if ($dateType === 'M') {
            // 月
            $nowRotateDate->modify('+1 month');
        } else if ($dateType === 'Y') {
            // 年
            $nowRotateDate->modify('+1 year');
        }

        return $nextDateTime;
    }

    public function rotate(Message $message):void
    {

        if ($this->rotatFile === '') {
            $this->rotatFile = $this->getNextRotateFile($this->logFile,$message);
        }

        // 换算字节单位B
        $maxByte = $this->maxByte * 1024;
        // 清除缓存，防止统计文件大小错误
        clearstatcache();
        if (@filesize($this->rotatFile) > $maxByte) {
            $this->rotatFile = $this->getNextRotateFile($this->logFile);
            $this->dirCreated = false;
        }

        return ;
    }


}
