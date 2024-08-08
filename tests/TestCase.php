<?php
namespace hlogger\tests;

use hehe\core\hlogger\Log;
use hehe\core\hlogger\LogManager;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var LogManager
     */
    protected $logManager;

    protected $file = '';

    // 单个测试之前(每个测试方法之前调用)
    protected function setUp()
    {
        $attrs =  [
            'defaultLogger'=>"default",
            // 日志过滤器定义
            'filters'=>[
                'info'=>[
                    //'class'=>'',// 过滤器类名,未填则默认为LogFilter
                    'levels'=>['info','error','warning',]
                ]
            ],
            // 日志处理器定义
            'handlers'=>[
                'default'=>[
                    'class'=>'FileHandler',
                    'logFile'=>$this->file,
                    'formatter'=>'default'
                ]
            ],

            // 日志格式器定义
            'formatters'=>[
                'default'=>[
                    //'class'=>'',// 消息格式器类名,未填则默认为LogFormatter
                    'tpl'=>'{date:Y-m-d:H:i},{level} :{msg} ,file:{file}, line:{line},{class}->{fn} {n}',
                ]
            ],

            // 日志记录器
            'loggers'=>[
                'default'=>[
                    'bufferLimit'=>0,// 达到指定数值后,日志消息将持久化
                    'handlers'=>['default'],
                    //'levels'=>['info','error','warning','exception','debug'],// 设置filter 后,此设置项将无效
                    //'categorys'=>['admin\controller*'],// 设置filter 后,此设置项将无效
                    //'filters'=>['info']// 自定义过滤器
                ],

                'hehe'=>[
                    'bufferLimit'=>0,// 达到指定数值后,日志消息将持久化
                    'handlers'=>['default'],
                    'levels'=>['info','error','warning','exception','debug'],// 设置filter 后,此设置项将无效
                    //'categorys'=>['admin\controller*'],// 设置filter 后,此设置项将无效
                    'filters'=>['info']// 自定义过滤器
                ]
            ]
        ];

        $this->logManager = new LogManager($attrs);
    }

    // 单个测试之后(每个测试方法之后调用)
    protected function tearDown()
    {
        $this->logManager = null;
        if (file_exists($this->file)) {
            @unlink($this->file);
        } else {
        }
    }

    protected function dellog(string $file)
    {
        if (file_exists($file)) {
            @unlink($file);
        }
    }

    // 整个测试类之前
    public static function setUpBeforeClass()
    {

    }

    // 整个测试类之前
    public static function tearDownAfterClass()
    {

    }


}
