<?php
namespace hlogger\tests\units;
use hehe\core\hlogger\Log;
use hlogger\tests\TestCase;

class LogTest extends TestCase
{

    protected function tearDown()
    {
        parent::tearDown();
    }

    public function testAddMessage1()
    {
        Log::emergency("emergency message");
        Log::alert("alert message");
        Log::critical("critical message");
        Log::error("error message");
        Log::warning("warning message");
        Log::notice("notice message");
        Log::info("info message");
        Log::debug("debug message");

        $content = file_get_contents($this->file);
        $this->assertRegExp('/emergency/',$content);
        $this->assertRegExp('/alert/',$content);
        $this->assertRegExp('/critical/',$content);
        $this->assertRegExp('/error/',$content);
        $this->assertRegExp('/warning/',$content);
        $this->assertRegExp('/notice/',$content);
        $this->assertRegExp('/info/',$content);
        $this->assertRegExp('/debug/',$content);
    }

    public function testDefaultLogger()
    {
        $logger = Log::getDefaultLogger();
        $logger->emergency("default logger emergency message");
        $logger->alert("default logger alert message");
        $logger->critical("default logger critical message");
        $logger->error("default logger error message");
        $logger->warning("default logger warning message");
        $logger->notice("default logger notice message");
        $logger->info("default logger info message");
        $logger->debug("default logger debug message");

        $content = file_get_contents($this->file);
        $this->assertRegExp('/emergency/',$content);
        $this->assertRegExp('/alert/',$content);
        $this->assertRegExp('/critical/',$content);
        $this->assertRegExp('/error/',$content);
        $this->assertRegExp('/warning/',$content);
        $this->assertRegExp('/notice/',$content);
        $this->assertRegExp('/info/',$content);
        $this->assertRegExp('/debug/',$content);
    }

    public function testGetLogger()
    {
        $logger = Log::getLogger('hehe');
        $logger->emergency("default logger emergency message");
        $logger->alert("default logger alert message");
        $logger->critical("default logger critical message");
        $logger->error("default logger error message");
        $logger->warning("default logger warning message");
        $logger->notice("default logger notice message");
        $logger->info("default logger info message");
        $logger->debug("default logger debug message");

        $content = file_get_contents($this->file);
        $this->assertNotRegExp('/emergency/',$content);
        $this->assertNotRegExp('/alert/',$content);
        $this->assertNotRegExp('/critical/',$content);
        $this->assertRegExp('/error/',$content);
        $this->assertRegExp('/warning/',$content);
        $this->assertNotRegExp('/notice/',$content);
        $this->assertRegExp('/info/',$content);
        $this->assertNotRegExp('/debug/',$content);

    }

    public function testNewLogger()
    {
        $file = 'D:\work\logs\logger2.log';

        $logger = Log::getLogger('admin');
        $logger->setBufferLimit(0);
        $logger->addFilter([
            'levels'=>['error','warning',]
        ]);

        $logger->addHandler([
            'logFile'=>$file,
            'formatter'=>'default'
        ]);

        $logger->error("default logger error message");
        $logger->warning("default logger warning message");
        $logger->notice("default logger notice message");

        $content = file_get_contents($file);
        $this->assertRegExp('/error/',$content);
        $this->assertRegExp('/warning/',$content);
        $this->assertNotRegExp('/notice/',$content);

        $this->dellog($file);
    }

    public function testNewLogger1()
    {
        $logger = Log::getLogger('admin');

        $logger->setBufferLimit(0);

        $logger->addFilter()
            ->setLevel('error,warning');

        $handler = $logger->addHandler();
        $handler->setLogFile($this->file);

        $handler->setFormatter()->setTpl('{date:Y-m-d:H:i},{level} ,file:{file}, line:{line} {n}');

        $logger->error("default logger error message");
        $logger->warning("default logger warning message");
        $logger->notice("default logger notice message");

        $content = file_get_contents($this->file);
        $this->assertRegExp('/error/',$content);
        $this->assertRegExp('/warning/',$content);
        $this->assertNotRegExp('/notice/',$content);

        //$this->dellog($file);
    }

    public function testAddFormatter()
    {
        Log::setFormatter('dev',['tpl'=>'{date:Y-m-d:H:i},{level;} ,file:{file}, line:{line} {n}']);
        Log::setFilter('dev',[
            'levels'=>['error','warning'],
        ]);

        Log::setHandler('dev',[
            'logFile'=>$this->file,
            'formatter'=>'dev',
            'filters'=>['dev']
        ]);

        $logger = Log::getLogger('dev');
        $logger->addHandler('dev');

        $logger->error("default logger error message");
        $logger->warning("default logger warning message");
        $logger->notice("default logger notice message");

        $content = file_get_contents($this->file);
        $this->assertRegExp('/error/',$content);
        $this->assertRegExp('/warning/',$content);
        $this->assertNotRegExp('/notice/',$content);

    }
}
