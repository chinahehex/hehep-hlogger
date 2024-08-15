<?php
namespace hehe\core\hlogger\handlers;

use hehe\core\hlogger\base\Message;
use hehe\core\hlogger\Utils;

/**
 * 文件轮转文件处理器基类
 */
abstract class RotatingFileHandler extends FileHandler
{
    /**
     * 轮转文件$rotatFile
     * @var string
     */
    protected $rotateFile = '';

    /**
     * 是否开启轮转文件
     * @var bool
     */
    protected $onRotate = true;

    /*
     * 正在轮转中
     */
    protected $rotating;

    /**
     * 轮转文件格式
     * @var string
     */
    protected $rotatefmt = '{filename}_{date:YmdHis}';

    /**
     * 轮转文件格式变量
     * @var array
     */
    protected $rotatefmtVars = [];

    /**
     * 轮转文件格式变量
     * @var array
     */
    protected $rotatefmtParams = [
        'filename'=>'\w+',
        'date'=>'.*'
    ];

    /**
     * 轮转文件格式模版
     * @var array
     */
    protected $rotatefmtTemplate = '';

    protected $rotatefmtPattern = '';

    /**
     * 文件轮转最大数量
     * @var int
     */
    protected $maxFiles = 0;

    protected $nextIndex = 1;

    /**
     * 备份文件最大数量
     * @var int
     */
    protected $backupCount = 0;

    /**
     * 备份文件格式
     * @var string
     */
    protected $backupfmt = '{filename}_up{index}';

    protected $backupfmtParams = [
        'filename'=>'\w+',
        'index'=>'\d+'
    ];

    /**
     * 备份文件格式模板
     * 由 backupfmt 生成
     * @var string
     */
    protected $backupfmtTemplate = '';

    /**
     * 备份文件格式变量
     * @var array
     */
    protected $backupfmtVars = [];



    /**
     * 备份文件格式正则
     * @var string
     */
    protected $backupfmtPattern = '';

    public function __construct(string $logFile = '',array $propertys = [])
    {
        parent::__construct($logFile,$propertys);
    }

    public function setRotatefmt(string $rotatefmt,array $params = []):void
    {
        $this->rotatefmt = $rotatefmt;
        $this->rotatefmtParams = $params;
    }

    public function setBackupfmt(string $backupfmt,array $params = []):void
    {
        $this->backupfmt = $backupfmt;
        $this->backupfmtParams = $params;
    }

    public function setMaxFiles(int $maxFiles):void
    {
        $this->maxFiles = $maxFiles;
    }

    public function setBackupCount(int $backupCount):void
    {
        $this->backupCount = $backupCount;
    }

    public function setRotatefmtParams(array $rotatefmtParams):void
    {
        $this->rotatefmtParams = $rotatefmtParams;
    }

    public function setBackupfmtParams(array $backupfmtParams):void
    {
        $this->backupfmtParams = $backupfmtParams;
    }

    /**
     * 重命名移动日志文件
     * @param string $logFile
     * @param string $newFile
     */
    protected function renameFile(string $logFile,string $newFile):void
    {
        $newPath = dirname($newFile);
        if (!is_dir($newPath)) {
            mkdir($newPath,0755,true);
        }

        // 重命名
        @rename($logFile, $newFile);
    }

    /**
     * 获取备份文件路径
     * @param Message $message
     * @return string
     */
    protected function buildBaukupFile(Message $message):string
    {
        if ($this->backupfmtTemplate === '') {
            list($this->backupfmtTemplate,$this->backupfmtVars) = Utils::parseTemplate($this->backupfmt);
        }

        $fileInfo = pathinfo($this->rotateFile);

        $fileExt = '';
        if (!empty($fileInfo['extension'])) {
            $fileExt = '.' . $fileInfo['extension'];
        }

        $backupRegParams = [];
        foreach ($this->backupfmtVars as $var) {
            list($name,$key) = $var;
            $pattern = $this->backupfmtParams[$name];
            if ($name === 'filename') {
                $backupRegParams[$key] = $fileInfo['filename'];
            } else {
                if (isset($this->backupfmtParams[$name])) {
                    $backupRegParams[$key] = "(?P<$name>$pattern)";
                } else {
                    $backupRegParams[$key] = "(?P<$name>.*)";
                }
            }
        }

        $this->backupfmtPattern = '#^' . strtr($this->backupfmtTemplate,$backupRegParams) . $fileExt . '$#';

        $ctx = $message->getContext();
        $ctx->addValue('index',[$this,'getNextIndex']);
        $ctx->addValue('filename',$fileInfo['filename']);

        $replaceParams = [];
        foreach ($this->backupfmtVars as $tag) {
            list($name,$key,$func_params) = $tag;
            $replaceParams[$key] = $ctx->getValue($name,$func_params);
        }

        $baukupFilepath = $fileInfo['dirname'] . '/' .  strtr($this->backupfmtTemplate, $replaceParams) . $fileExt;

        return  $baukupFilepath;
    }

    public function getNextIndex():int
    {
        $backFiles = Utils::getFiles(dirname($this->rotateFile),$this->backupfmtPattern);
        if (count($backFiles) === 0) {
            return $this->nextIndex;
        }

        if (count($backFiles) === 1) {
            $lastFile = basename($backFiles[0]);
        } else {
            $backupfmtPattern = $this->backupfmtPattern;
            usort($backFiles, function ($filea, $fileb) use($backupfmtPattern) {
                preg_match($backupfmtPattern, basename($filea), $fileaMatches);
                preg_match($backupfmtPattern, basename($fileb), $filebMatches);
                return (int)$fileaMatches['index'] < (int)$filebMatches['index'];
            });

            $lastFile = basename($backFiles[0]);
        }

        // 匹配最大文件索引
        if (!preg_match($this->backupfmtPattern, $lastFile, $matches)) {
            return $this->nextIndex;
        }

        if (isset($matches["index"])) {
            return (int)$matches["index"] + 1;
        } else {
            return $this->nextIndex;
        }
    }

    /**
     * 备份文件
     * @param Message $message
     */
    protected function backupFile(Message $message):void
    {

        $newBaukupFile = $this->buildBaukupFile($message);

        // 备份文件
        $this->renameFile($this->rotateFile, $newBaukupFile);

        // 检测文件是否大小超过限制
        $this->checkBackupFileLimit();
    }

    protected function checkBackupFileLimit():void
    {
        if ($this->backupCount === 0) {
            return;
        }

        // 读取当前目录下所有文件
        $path = dirname($this->rotateFile);
        if (!is_dir($path)) {
            return;
        }

        $logFiles = Utils::getFiles($path,$this->backupfmtPattern);
        if (count($logFiles) <= $this->backupCount) {
            return;
        }

        // 按创建时间降序排序
        $backupfmtPattern = $this->backupfmtPattern;
        usort($logFiles, function ($filea, $fileb) use($backupfmtPattern) {
            preg_match($backupfmtPattern, basename($filea), $fileaMatches);
            preg_match($backupfmtPattern, basename($fileb), $fileMmatches);
            return (int)$fileaMatches['index'] < (int)$fileMmatches['index'];
        });

        // 删除最旧的文件
        foreach (array_slice($logFiles, $this->backupCount) as $file) {
            if (is_writable($file)) {
                set_error_handler(function ($errno, $errstr, $errfile, $errline) {});
                unlink($file);
                restore_error_handler();
            }
        }
    }

    /**
     * 检测轮转文件数量限制
     */
    protected function checkRotateFileLimit():void
    {
        $maxFiles = $this->maxFiles;
        if ($this->rotating) {
            $maxFiles = $maxFiles - 1;
        }

        // 读取当前目录下所有文件
        $path = dirname($this->rotateFile);
        if (!is_dir($path)) {
            return;
        }

        $logFiles = Utils::getFiles($path,$this->rotatefmtPattern);
        if (count($logFiles) <= $maxFiles) {
            return;
        }

        // 按创建时间降序排序
        usort($logFiles, function ($filea, $fileb) {
            return strcmp($fileb, $filea);
        });

        // 删除最旧的文件
        foreach (array_slice($logFiles, $maxFiles) as $file) {
            if (is_writable($file)) {
                set_error_handler(function ($errno, $errstr, $errfile, $errline) {});
                unlink($file);

                $backupFiles = glob($this->getBackupFilesPattern($file));
                foreach ($backupFiles as $backupFile) {
                    if (is_writable($backupFile)) {
                        unlink($backupFile);
                    }
                }

                restore_error_handler();
            }
        }
    }

    protected function getBackupFilesPattern(string $filename)
    {
        $fileInfo = pathinfo($filename);
        $glob = $fileInfo['dirname'] . '/' . $fileInfo['filename'] . '*';

        if (!empty($fileInfo['extension'])) {
            $glob .= '.'.$fileInfo['extension'];
        }

        return $glob;
    }

    /***
     * 轮转文件
     * @param Message $message
     */
    protected function rotateFile(Message $message)
    {
        $this->rotateFile = $this->buildRotateFile($message);
        $this->dirCreated = false;

        if ($this->rotating === null) {
            $this->rotating = !file_exists($this->rotateFile);
        }

        // 删除过期文件
        if ($this->maxFiles > 0 && dirname($this->logFile) === dirname($this->rotateFile)) {
            $this->checkRotateFileLimit();
        }
    }

    /**
     * 根据文件模版生成新文件路径
     * @param Message $message
     * @return string
     */
    protected function buildRotateFile(Message $message):string
    {
        if ($this->rotatefmtTemplate === '') {
            list($this->rotatefmtTemplate,$this->rotatefmtVars) = Utils::parseTemplate($this->rotatefmt);
        }

        $fileInfo = pathinfo($this->logFile);
        $fileExt = '';
        if (!empty($fileInfo['extension'])) {
            $fileExt = '.' . $fileInfo['extension'];
        }

        $rotateRegParams = [];
        foreach ($this->rotatefmtVars as $var) {
            list($name,$key) = $var;
            $pattern = $this->rotatefmtParams[$name];
            if (isset($this->rotatefmtParams[$name])) {
                $rotateRegParams[$key] = "(?P<$name>$pattern)";
            } else {
                $rotateRegParams[$key] = "(?P<$name>.*)";
            }
        }

        $this->rotatefmtPattern = '#^' . strtr($this->rotatefmtTemplate,$rotateRegParams) . $fileExt . '$#';


        $ctx = $message->getContext();
        $ctx->addValue('filename',$fileInfo['filename']);

        $replaceParams = [];
        foreach ($this->rotatefmtVars as $tag) {
            list($name,$key,$func_params) = $tag;
            $replaceParams[$key] = $ctx->getValue($name,$func_params);
        }

        $newFilePath = $fileInfo['dirname'] . '/' . strtr($this->rotatefmtTemplate, $replaceParams) . $fileExt;

        return $newFilePath;
    }


    public function handleMessage(Message $message):void
    {
        if ($this->onRotate) {
            $this->rotate($message);
            // 轮转结束
            $this->rotating = false;
        }

        $this->createLogDir($this->rotateFile);

        $this->writeFile($message,$this->rotateFile);
    }

    abstract public function rotate(Message $message):void;

}
