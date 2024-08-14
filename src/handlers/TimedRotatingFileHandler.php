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
    protected $rotateMode = 'M';

    protected $interval = 0;

    protected $startDate = null;

    protected $endDate = null;

    /**
     * 文件最大大小
     *<B>说明：</B>
     *<pre>
     * 单位kb
     *</pre>
     * @var int
     */
    protected $maxByte = 0;

    public function __construct(string $logFile = '',string $rotateMode = '',int $interval = 0,array $propertys = [])
    {
        $this->rotateMode = $rotateMode;
        $this->interval = $interval;

        parent::__construct($logFile,$propertys);
    }

    public function setRotateMode(string $rotateMode):void
    {
        $this->rotateMode = $rotateMode;
    }

    /**
     * @param int $maxByte
     */
    public function setMaxByte(int $maxByte): void
    {
        $this->maxByte = $maxByte;
    }

    /**
     * 重置轮转时间
     */
    protected function resetRouteDate()
    {
        //  ‘S’、‘M’、‘H’、‘D’、‘W’ 、‘Y’
        // 转大写
        $rotateMode = strtoupper($this->rotateMode);
        $nowRotateDate = new \DateTime('now');

        if ($rotateMode === 'D') {
            // 天
            $startDate = (new \DateTime())->setTimestamp(strtotime("today"));
            $endDate = (new \DateTime())->setTimestamp( strtotime("tomorrow -1 second"));
        } else if ($rotateMode === 'H') {
            // 小时
            $startDate = (new \DateTime())->setTimestamp(strtotime(date('Y-m-d H:00:00', $nowRotateDate->getTimestamp())));
            $endDate = (new \DateTime())->setTimestamp(strtotime(date('Y-m-d H:59:59', $nowRotateDate->getTimestamp())));
        } else if ($rotateMode === 'S') {
            // 分钟
            $startDate = (new \DateTime())->setTimestamp(strtotime(date('Y-m-d H:i:00', $nowRotateDate->getTimestamp())));
            $endDate = (new \DateTime())->setTimestamp(strtotime(date('Y-m-d H:i:59', $nowRotateDate->getTimestamp())));
        } else if ($rotateMode === 'W') {
            // 周
            $startDate = (new \DateTime())->setTimestamp(strtotime('monday this week 00:00:00', $nowRotateDate->getTimestamp()));
            $endDate = (new \DateTime())->setTimestamp(strtotime('monday next week 00:00:00', $nowRotateDate->getTimestamp()) - 1);
        } else if ($rotateMode === 'M') {
            // 月
            $startDate = (new \DateTime())->setTimestamp(strtotime(date('Y-m-01 00:00:00')));
            $endDate = (new \DateTime())->setTimestamp(strtotime(date('Y-m-t 23:59:59')));
        } else if ($rotateMode === 'Y') {
            // 年
            $currentYear = date("Y");
            // 获取当前年份的起始时间（年份开始的第一天，通常是1月1日）
            $startOfYear = strtotime("{$currentYear}-01-01 00:00:00");
            // 获取当前年份的结束时间（下一年的第一天，减去一秒得到本年的最后一秒）
            $endOfYear = strtotime("+1 year", $startOfYear) - 1;
            $startDate = (new \DateTime())->setTimestamp($startOfYear);
            $endDate = (new \DateTime())->setTimestamp($endOfYear);
        }

        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    protected function rotateFile(Message $message)
    {
        $this->resetRouteDate();
        parent::rotateFile($message);
    }

    public function rotate(Message $message):void
    {
        // 第一次轮转，初始化轮转时间，轮转文件
        if ($this->rotateFile === '') {
            $this->rotateFile($message);
        }

        if ($message->getDataTime() > $this->endDate) {
            $this->rotating = true;
            $this->rotateFile($message);
        }

        // 备份轮转文件
        if ($this->rotating === false && $this->maxByte > 0) {
            clearstatcache();
            if (@filesize($this->rotateFile) > ($this->maxByte * 1024)) {
                $this->backupFile($message);
            }
        }

        return ;
    }


}
