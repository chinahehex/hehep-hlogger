<?php
namespace hehe\core\hlogger\handlers;

use hehe\core\hlogger\base\Message;

/**
 * 文件轮转文件处理器基类
 */
abstract class RotatingFileHandler extends FileHandler
{
    const TPL_REGEX = '/\{([a-zA-Z]+):?([^\}]+)?\}/';

    /**
     * 轮转文件
     * @var string
     */
    protected $rotatFile = '';

    /**
     * 是否开启轮转文件
     * @var bool
     */
    protected $onRotate = true;

    /**
     * 轮转文件格式
     * @var string
     */
    protected $fileFormat = '{filename}_{date:YmdHis}_{rand:6}';

    /**
     * 轮转文件格式变量
     * @var array
     */
    protected $fileFormatVars = [];

    protected $fileReplaceTemplate = '';

    public function __construct(string $logFile = '',array $propertys = [])
    {
        parent::__construct($logFile,$propertys);

    }

    public function setFileFormat(string $fileFormat):void
    {
        $this->fileFormat = $fileFormat;
    }

    /**
     * 解析文件名模板
     *<B>说明：</B>
     *<pre>
     * 略
     *</pre>
     */
    protected function parseRotateFileTemplate():void
    {
        $matches = [];
        $tagIndex = 0;
        $this->fileReplaceTemplate = preg_replace_callback(self::TPL_REGEX,function($matches) use (&$tagIndex){
            $tagname = '<'.$matches[1] . $tagIndex . '>';
            $tagIndex++;
            return $tagname;
        },$this->fileFormat);

        if (preg_match_all(self::TPL_REGEX, $this->fileFormat, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
            foreach ($matches as $index=>$param) {
                $name = $param[1][0];
                if (isset($param[2][0])) {
                    $func_params = explode(',',$param[2][0]);
                } else {
                    $func_params = null;
                }
                // 判断是否有参数
                $tagName = '<' .$name . $index .  '>';

                $this->fileFormatVars[] = [$name,$tagName,$func_params];
            }
        }
    }

    /**
     * 根据文件模版生成新文件路径
     * @param string $filePath
     * @return string
     */
    protected function getNextRotateFile(string $filePath,Message $message):string
    {
        if ($this->fileReplaceTemplate === '') {
            $this->parseRotateFileTemplate();
        }

        $fileInfo = pathinfo($filePath);
        $dirPath = $fileInfo['dirname'];
        $filename = $fileInfo['filename'];
        $fileExt = '.' . $fileInfo['extension'];

        // 遍历新文件路径
        $newFilePath = $dirPath . '/' .  $this->replaceRotateFileTemplate($filename,$message) . $fileExt;

        return $newFilePath;
    }

    protected function replaceRotateFileTemplate(string $filename,Message $message):string
    {
        $replaceParams = [];
        foreach ($this->fileFormatVars as $tag) {
            list($name,$key,$value) = $tag;
            if ($name === 'filename') {
                $replaceParams[$key] = $filename;
            } else if ($name === 'date') {
                if (!empty($value)) {
                    $replaceParams[$key] = date($value[0], microtime(true));
                } else {
                    $replaceParams[$key] = microtime(true);;
                }
            } else if ($name === 'mdate') {
                if (!empty($value)) {
                    $replaceParams[$key] = date($value[0], $message->getDataTime()->getTimestamp());
                } else {
                    $replaceParams[$key] = $message->getDataTime()->getTimestamp();
                }
            } else if ($name === 'time'){
                $replaceParams[$key] = microtime(true);
            } else if ($name === 'rand'){
                $num = !empty($value) ? (int)$value : 6 ;
                $min = pow(10, $num - 1);
                $max = pow(10, $num) - 1;
                $replaceParams[$key] = mt_rand($min,$max);
            }
        }

        return  strtr($this->fileReplaceTemplate, $replaceParams);
    }


    public function handleMessage(Message $message):void
    {
        if ($this->onRotate) {
            $this->rotate($message);
        }

        $this->createLogDir($this->rotatFile);

        $this->writeFile($message,$this->rotatFile);
    }

    abstract public function rotate(Message $message):void;

}
