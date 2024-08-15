<?php
namespace hlogger\tests\units;
use hehe\core\hlogger\contexts\SysContext;
use hehe\core\hlogger\handlers\FileHandler;
use hehe\core\hlogger\Log;
use hlogger\tests\TestCase;

class HandlerTest extends TestCase
{

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

    protected function getFiles(string $file)
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
                continue;
            } else {
                $file_res[] = $file;
            }
        }

        return $file_res;
    }

    protected function totalLogFilesNum(string $file)
    {
        $file_res = $this->getFiles($file);
        return count($file_res);
    }

    public function testFileHandler()
    {
        $logger = $this->logManager->getLogger('admin');
        $byteRotatingFileHandler = $logger->byteRotatingFileHandler($this->file,10);
        $logger->addHandler($byteRotatingFileHandler);
        $logger->setFormatter($logger->lineFormatter('{date:Y-m-d:H:i},{level},{msg} ,file:{file}, line:{line} {n}'));

        for ($i = 0;$i < 300;$i++) {
            $logger->error("default logger error message");
        }

        // 验证文件数量
        $this->assertTrue($this->totalLogFilesNum($this->file) == 4);
    }

    public function testFileHandler1()
    {
        $logger = $this->logManager->getLogger('admin');
        $byteRotatingFileHandler = $logger->byteRotatingFileHandler($this->file,10);
        $byteRotatingFileHandler->setBackupCount(2);
        $logger->addHandler($byteRotatingFileHandler);
        $logger->setFormatter($logger->lineFormatter('{date:Y-m-d:H:i},{level},{msg} ,file:{file}, line:{line} {n}'));

        for ($i = 0;$i < 300;$i++) {
            $logger->error("default logger error message");
        }

        // 验证文件数量
        $this->assertTrue($this->totalLogFilesNum($this->file) == 3);
    }

    public function testTimedRotatingFileHandler()
    {
        $logger = $this->logManager->getLogger('admin');
        $timedRotatingFileHandler = $logger->timedRotatingFileHandler($this->file,'s');
        $timedRotatingFileHandler->setRotatefmt('{filename}_{date:YmdHi}');
        $logger->addHandler($timedRotatingFileHandler);
        $logger->setFormatter($logger->lineFormatter('{date:Y-m-d:H:i},{level},{msg} ,file:{file}, line:{line} {n}'));
        $logger->error("default logger error message");
        // 验证文件数量
        $this->assertTrue($this->totalLogFilesNum($this->file) == 1);
    }

    public function testTimedRotatingFileHandler1()
    {
        $logger = $this->logManager->getLogger('admin');
        $timedRotatingFileHandler = $logger->timedRotatingFileHandler($this->file,'s');
        $timedRotatingFileHandler->setMaxFiles(2);
        $timedRotatingFileHandler->setRotatefmt('{filename}_{date:YmdHi}');
        $logger->addHandler($timedRotatingFileHandler);
        $logger->setFormatter($logger->lineFormatter('{date:Y-m-d:H:i},{level},{msg} ,file:{file}, line:{line} {n}'));
        $logger->error("default logger error message");
        // 验证文件数量
        $this->assertTrue($this->totalLogFilesNum($this->file) == 1);
    }

    public function testTimedRotatingFileHandler2()
    {
        $logger = $this->logManager->getLogger('admin');
        $timedRotatingFileHandler = $logger->timedRotatingFileHandler($this->file,'s');
        $timedRotatingFileHandler->setMaxFiles(2);
        $timedRotatingFileHandler->setRotatefmt('{filename}_{date:YmdHi}_hehe');
        $timedRotatingFileHandler->setMaxByte(10);
        $timedRotatingFileHandler->setBackupCount(4);
        $logger->addHandler($timedRotatingFileHandler);
        $logger->setFormatter($logger->lineFormatter('{date:Y-m-d:H:i},{level},{msg} ,file:{file}, line:{line} {n}'));
        for ($i = 0;$i < 300;$i++) {
            $logger->error("default logger error message");
        }
        // 验证文件数量
        $this->assertTrue($this->totalLogFilesNum($this->file) == 4);
    }

    public function testTimedRotatingFileHandler3()
    {
        $logger = $this->logManager->getLogger('admin');
        $timedRotatingFileHandler = $logger->timedRotatingFileHandler($this->file,'s');
        $timedRotatingFileHandler->setMaxFiles(2);
        $timedRotatingFileHandler->setRotatefmt('{date:Ym/d/H/}{filename}_{date:YmdHi}_hehe');
        $timedRotatingFileHandler->setMaxByte(10);
        $timedRotatingFileHandler->setBackupCount(4);
        $logger->addHandler($timedRotatingFileHandler);
        $logger->setFormatter($logger->lineFormatter('{date:Y-m-d:H:i},{level},{msg} ,file:{file}, line:{line} {n}'));
        for ($i = 0;$i < 300;$i++) {
            $logger->error("default logger error message");
        }
        // 验证文件数量
        $this->assertTrue($this->totalLogFilesNum($this->file) == 4);
    }

    public function testTimedRotatingFileHandlerDay()
    {
        $logger = $this->logManager->getLogger('admin');
        $timedRotatingFileHandler = $logger->timedRotatingFileHandler($this->file,'d');
        $logger->addHandler($timedRotatingFileHandler);
        $logger->setFormatter($logger->lineFormatter('{date:Y-m-d:H:i},{level},{msg} ,file:{file}, line:{line} {n}'));
        $logger->error("default logger error message");
        $content = file_get_contents(($this->getFiles($this->file))[0]);
        $this->assertRegExp('/error/',$content);
    }

    public function testTimedRotatingFileHandlerHour()
    {
        $logger = $this->logManager->getLogger('admin');
        $timedRotatingFileHandler = $logger->timedRotatingFileHandler($this->file,'h');
        $logger->addHandler($timedRotatingFileHandler);
        $logger->setFormatter($logger->lineFormatter('{date:Y-m-d:H:i},{level},{msg} ,file:{file}, line:{line} {n}'));
        $logger->error("default logger error message");
        $content = file_get_contents(($this->getFiles($this->file))[0]);
        $this->assertRegExp('/error/',$content);
    }

    public function testTimedRotatingFileHandlerSec()
    {
        $logger = $this->logManager->getLogger('admin');
        $timedRotatingFileHandler = $logger->timedRotatingFileHandler($this->file,'s');
        $logger->addHandler($timedRotatingFileHandler);
        $logger->setFormatter($logger->lineFormatter('{date:Y-m-d:H:i},{level},{msg} ,file:{file}, line:{line} {n}'));
        $logger->error("default logger error message");
        $content = file_get_contents(($this->getFiles($this->file))[0]);
        $this->assertRegExp('/error/',$content);
    }

    public function testTimedRotatingFileHandlerW()
    {
        $logger = $this->logManager->getLogger('admin');
        $timedRotatingFileHandler = $logger->timedRotatingFileHandler($this->file,'w');
        $logger->addHandler($timedRotatingFileHandler);
        $logger->setFormatter($logger->lineFormatter('{date:Y-m-d:H:i},{level},{msg} ,file:{file}, line:{line} {n}'));
        $logger->error("default logger error message");
        $content = file_get_contents(($this->getFiles($this->file))[0]);
        $this->assertRegExp('/error/',$content);
    }

    public function testTimedRotatingFileHandlerM()
    {
        $logger = $this->logManager->getLogger('admin');
        $timedRotatingFileHandler = $logger->timedRotatingFileHandler($this->file,'M');
        $logger->addHandler($timedRotatingFileHandler);
        $logger->setFormatter($logger->lineFormatter('{date:Y-m-d:H:i},{level},{msg} ,file:{file}, line:{line} {n}'));
        $logger->error("default logger error message");
        $content = file_get_contents(($this->getFiles($this->file))[0]);
        $this->assertRegExp('/error/',$content);
    }

    public function testTimedRotatingFileHandlerY()
    {
        $logger = $this->logManager->getLogger('admin');
        $timedRotatingFileHandler = $logger->timedRotatingFileHandler($this->file,'Y');
        $logger->addHandler($timedRotatingFileHandler);
        $logger->setFormatter($logger->lineFormatter('{date:Y-m-d:H:i},{level},{msg} ,file:{file}, line:{line} {n}'));
        $logger->error("default logger error message");
        $content = file_get_contents(($this->getFiles($this->file))[0]);
        $this->assertRegExp('/error/',$content);
    }


}
