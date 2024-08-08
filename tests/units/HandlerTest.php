<?php
namespace hlogger\tests\units;
use hehe\core\hlogger\contexts\LineContext;
use hehe\core\hlogger\handlers\FileHandler;
use hehe\core\hlogger\Log;
use hlogger\tests\TestCase;

class HandlerTest extends TestCase
{
    protected $file = 'D:\work\logs\logger\hehex.log';

    protected function tearDown()
    {
        //parent::tearDown();

        $this->dellogs();
    }

    protected function dellogs()
    {
        $path = dirname($this->file);
        // 递归删除目录中的文件
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
    }

    protected function totalLogFilesNum(string $file)
    {
        if (empty($file)) {
            $path = dirname($this->file);
        } else {
            $path = dirname($file);
        }

        // 递归删除目录中的文件
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        $file_res = [];
        foreach ($files as $file) {
            if ($file->isDir()) {
            } else {
                $file_res[] = $file;
            }
        }

        return count($file_res);
    }

    public function testFileHandler()
    {

        $logger = $this->logManager->getLogger('admin');
        $byteRotatingFileHandler = $logger->byteRotatingFileHandler($this->file,10);
        $byteRotatingFileHandler->setFilefmt('{date:Ym/d/H}/{filename}_{date:YmdHis}_{rand:6}');
        $logger->addHandler($byteRotatingFileHandler);
        $logger->setFormatter($logger->lineFormatter('{date:Y-m-d:H:i},{level},{msg} ,file:{file}, line:{line} {n}'));

        for ($i = 0;$i < 200;$i++) {
            $logger->error("default logger error message");
        }

        // 验证文件数量
        $this->assertTrue($this->totalLogFilesNum($this->file) == 3);
    }


}
