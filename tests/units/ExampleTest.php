<?php
namespace hlogger\tests\units;
use hehe\core\hlogger\base\Logger;
use hehe\core\hlogger\handlers\FileHandler;
use hehe\core\hlogger\Log;
use hlogger\tests\TestCase;

class ExampleTest extends TestCase
{

    protected function tearDown()
    {
        parent::tearDown();
    }

    public function testAddMessage1()
    {
        $this->logManager->emergency("emergency message");
        $this->logManager->alert("alert message");
        $this->logManager->critical("critical message");
        $this->logManager->error("error message");
        $this->logManager->warning("warning message");
        $this->logManager->notice("notice message");
        $this->logManager->info("info message");
        $this->logManager->debug("debug message");

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

    public function testAddMessage2()
    {
        Log::emergency("Log::emergency message");
        Log::alert("Log::alert message");
        Log::critical("Log::critical message");
        Log::error("Log::error message");
        Log::warning("Log::warning message");
        Log::notice("Log::notice message");
        Log::info("Log::info message");
        Log::debug("Log::debug message");

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
        $logger = $this->logManager->getDefaultLogger();
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
        $logger = $this->logManager->getLogger('hehe');
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
        $logger = $this->logManager->getLogger('admin');
        $logger->setBufferLimit(0);
        $logger->addHandler($this->logManager->fileHandler($this->file));
        $logger->setLevel('error,warning');
        $logger->setFormatter($this->logManager->newFormatter('default'));

        $logger->error("default logger error message");
        $logger->warning("default logger warning message");
        $logger->notice("default logger notice message");

        $content = file_get_contents($this->file);
        $this->assertRegExp('/error/',$content);
        $this->assertRegExp('/warning/',$content);
        $this->assertNotRegExp('/notice/',$content);

    }

    public function testNewLogger1()
    {
        $logger = $this->logManager->getLogger('admin');

        $logger->setBufferLimit(0);


        $fileHandler = $this->logManager->fileHandler();
        $fileHandler->setLogFile($this->file);
        $fileHandler->setLevel('error,warning');
        $logger->addHandler($fileHandler);

        $lineFormatter = $this->logManager->lineFormatter('{date:Y-m-d H:i},{level} ,file:{file}, line:{line} {n}');
        $logger->setFormatter($lineFormatter);

        $logger->error("default logger error message");
        $logger->warning("default logger warning message");
        $logger->notice("default logger notice message");

        $content = file_get_contents($this->file);
        $this->assertRegExp('/error/',$content);
        $this->assertRegExp('/warning/',$content);
        $this->assertNotRegExp('/notice/',$content);

    }

    public function testAddFormatter()
    {
        $this->logManager->setFormatter('dev',['tpl'=>'{date:Y-m-d:H:i},{level} ,file:{file}, line:{line} {n}']);

        $this->logManager->setFilter('dev',[
            'levels'=>['error','warning'],
        ]);

        $this->logManager->setHandler('dev',[
            'logFile'=>$this->file,
            'formatter'=>'dev',
            'filters'=>['dev']
        ]);

        $logger = $this->logManager->getLogger('dev');
        $logger->addHandler('dev');

        $logger->error("default logger error message");
        $logger->warning("default logger warning message");
        $logger->notice("default logger notice message");

        $content = file_get_contents($this->file);
        $this->assertRegExp('/error/',$content);
        $this->assertRegExp('/warning/',$content);
        $this->assertNotRegExp('/notice/',$content);
    }

    public function testNewFileHandler()
    {
        $logger = $this->logManager->getLogger('admin');

        $fileHandler = $logger->fileHandler($this->file);
        $logger->addHandler($fileHandler);

        $lineFormatter = $logger->lineFormatter('{date:Y-m-d:H:i},{level} ,file:{file}, line:{line} {n}');
        $fileHandler->setFormatter($lineFormatter);

        $logger->error("default logger error message");
        $logger->warning("default logger warning message");
        $logger->notice("default logger notice message");

        $content = file_get_contents($this->file);
        $this->assertRegExp('/error/',$content);
        $this->assertRegExp('/warning/',$content);
        $this->assertRegExp('/notice/',$content);

    }

    public function testNewFileHandler1()
    {
        $logger = $this->logManager->getLogger('admin');

        $fileHandler = new FileHandler($this->file);
        $logger->addHandler($fileHandler);
        $fileHandler->setFormatter($this->logManager->lineFormatter('{date:Y-m-d:H:i},{level} ,file:{file}, line:{line} {n}'));

        $logger->error("default logger error message");
        $logger->warning("default logger warning message");
        $logger->notice("default logger notice message");

        $content = file_get_contents($this->file);
        $this->assertRegExp('/error/',$content);
        $this->assertRegExp('/warning/',$content);
        $this->assertRegExp('/notice/',$content);

    }

    public function testNewLevelFilter()
    {
        $logger = $this->logManager->getLogger('admin');
        $levelFilter = $logger->levelFilter('error,warning');
        $logger->addFilter($levelFilter);
        $fileHandler = $logger->fileHandler($this->file);
        $logger->addHandler($fileHandler);

        $logger->error("default logger error message");
        $logger->warning("default logger warning message");
        $logger->notice("default logger notice message");

        $content = file_get_contents($this->file);
        $this->assertRegExp('/error/',$content);
        $this->assertRegExp('/warning/',$content);
        $this->assertNotRegExp('/notice/',$content);
    }


    public function testContext()
    {
        $logger = $this->logManager->getLogger('admin');
        $fileHandler = $logger->fileHandler($this->file);
        $lineFormatter = $logger->lineFormatter('{date:Y-m-d:H:i},{level},{user},{msg},file:{file}, line:{line} {n}');
        $fileHandler->setFormatter($lineFormatter);
        $logger->addHandler($fileHandler);

        $logger->addContext(function(){
            return [
                'userx'=>'mmm',
                'ipx'=>'127.0.22.1'
            ];
        });


        $logger->error("default logger error message");
        $logger->warning("default logger warning message");
        $logger->notice("default logger notice message");

        $content = file_get_contents($this->file);
        $this->assertRegExp('/error/',$content);
        $this->assertRegExp('/warning/',$content);
        $this->assertRegExp('/notice/',$content);
    }

    public function testCategory()
    {

        $logger = $this->logManager->getLogger('admin');

        $fileHandler = $logger->fileHandler($this->file);
        $lineFormatter = $logger->lineFormatter('{date:Y-m-d:H:i},{level},{msg},file:{file}, line:{line} {n}');
        $fileHandler->setFormatter($lineFormatter);
        $logger->addHandler($fileHandler);
        $logger->setCategory(get_class($this) . '*');

        $logger->error("default logger error message");
        $logger->warning("default logger warning message");
        $logger->notice("default logger notice message");

        $content = file_get_contents($this->file);
        $this->assertRegExp('/error/',$content);
        $this->assertRegExp('/warning/',$content);
        $this->assertRegExp('/notice/',$content);

    }
}
