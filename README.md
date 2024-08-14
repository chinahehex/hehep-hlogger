# hehep-hlogger

## 介绍
- hehep-hlogger 是一个PHP 日志工具组件
- 支持处理器、过滤器、格式器、上下文
## 安装
- **gitee下载**:
```
git clone git@gitee.com:chinahehex/hehep-hlogger.git
```

- **github下载**:
```
git clone git@github.com:chinahehex/hehep-hlogger.git
```
- 命令安装：
```
composer require hehex/hehep-hlogger
```

## 组件配置
```php

$conf = [
  'defaultLogger'=>"hehe",
  
    // 预定义上下文
    'contexts'=>[
        'default'=>[
            'class'=>'hehe\core\hlogger\contexts\TraceContext',
            'skipClasses'=>[],// 跳过的类名,
            'skipFuns'=>[],// 跳过的函数名,
        ]
    ],
    
    // 预定义日志过滤器
    'filters'=>[
        'info'=>[
            //'class'=>'',// 过滤器类名,未填则默认为LogFilter
            'levels'=>['info'],
            'categorys'=>['admin\controller*']
        ]
    ],

    // 预定义日志格式器
    'formatters'=>[
        'default'=>[
            //'class'=>'',// 消息格式器类名,未填则默认为LogFormatter
            'tpl'=>'{date:Y-m-d:H:i} :{msg},file:{file}, line:{line},{class}->{fn} {n}',
        ]
    ],

    // 预定义日志处理器
    'handlers'=>[
        'default'=>[
            'class'=>'FileHandler',
            'logFile'=>'/home/hehe/www/logs/hehep.log',
            'formatter'=>'default'
        ]
    ],

    // 预定义日志记录器
    'loggers'=>[
        'hehe'=>[
            'bufferLimit'=>0,// 缓冲日志数量
            'handlers'=>['default'],
            'levels'=>['info','error','warning','exception','debug'],// 设置filter 后,此设置项将无效
            'categorys'=>['admin\controller\*'],// 设置filter 后,此设置项将无效
            'filter'=>'info',// 定义过滤器
            'formatter'=>'default',// 定义日志格式器
            'context'=>['default'],// 定义上下文
        ],
    ]
];

```
## 基本示例

- 记录日志
```php
use hehe\core\hlogger\LogManager;
use hehe\core\hlogger\Log;

// 创建日志管理器
$hlogger = new LogManager([]);

// 记录日志
$hlogger->info('info log message');
$hlogger->error('error log message');
Log::info('info log message');
Log::error('error log message');

```

## 日志管理器
- 说明
```
用于管理日志相关的操作,比如日志记录器,日志处理器,日志格式器,志过滤器的对象获取,以及日志的记录
```
- 日志管理器示例
```php
use hehe\core\hlogger\LogManager;
use hehe\core\hlogger\Log;

// 创建日志管理器
$hlog = new LogManager([]);

// 默认日志记录器记录日志
$hlog->info('info log message');
$hlog->error('error log message');

// 获取日志记录器hehe,单例对象
$heheLogger = $hlog->getLogger('hehe');

// 创建新日志记录器
$heheLogger = $hlog->newLogger('hehe');

// 创建日志过滤器
$levelFilter = $hlog->levelFilter('error,info');
$heheLogger->addFilter($levelFilter);

// 创建日志处理器
$fileHandler = $hlog->fileHandler('/home/hehe/www/logs/hehep.log');
$heheLogger->addHandler($fileHandler);

// 创建日志格式器
$lineFormatter = $hlog->lineFormatter('{date:Y-m-d:H:i},{level},{userid} :{msg} ,file:{file}, line:{line},{class}->{fn}{n}');
$heheLogger->setFormatter($lineFormatter);

// 新增上下文变量
$heheLogger->addContext(function(){
    return [
        'userid'=>1
    ];
});

// 写入日志
$heheLogger->info('info log message');
$heheLogger->error('error log message');

```

- 预定义配置
```php
use hehe\core\hlogger\LogManager;
use hehe\core\hlogger\Log;

// 创建日志管理器
$hlog = new LogManager([]);

// 设置默认"default"处理器参数
$hlog->setHandler(['logFile'=>'/home/hehe/www/logs/xxxx.log']);


// 设置名称为“hehe”格式器
$hlog->setFormatter('hehe',[
    'class'=>'lineFormatter',
    'tpl'=>'{date:Y-m-d:H:i} :{msg},file:{file}, line:{line},{class}->{fn} {cate} {n}'
]);

// 设置名称为“hehe”日志过滤器
$hlog->setFilter('hehe',[
    'levels'=>'error,info',
    'categorys'=>'admin\controller*',
]);

// 设置名称为“hehe”日志处理器
$hlog->setHandler('hehe',[
    'class'=>'FileHandler',
    'logFile'=>'/home/hehe/www/logs/xxxx.log',
    'formatter'=>'hehe',
    //'filter'=>'hehe'
]);

// 设置名称为“hehe”日志记录器
$hlog->setLogger('hehe',[
    'handlers'=>'hehe',
    'filters'=>'hehe',
    'levels'=>'error,info',
]);

// 获取预定义hehe日志记录器单例对象
$heheLogger = $hlog->getLogger('hehe');


// 获取预定义hehe日志记录器新对象
$heheLogger = $hlog->newLogger('hehe');

// 写入日志
$heheLogger->error('error log message');

```

## 日志记录器
- 说明
```
日志记录器类:hehe\core\hlogger\base\Logger
作用:用于记录日志，可以设置日志级别,日志过滤器,日志处理器,
属性:
'bufferLimit'=>0,// 缓冲日志数量
'handlers'=>['default'],// 日志处理器
'levels'=>['info','error','warning','exception','debug'],// 设置允许的消息级别
'categorys'=>['admin\controller*'],// 设置允许的日志类别
'filter'=>'info',// 定义过滤器
'formatter'=>'default',// 定义日志格式器
```

- 创建日志记录器
```php
use hehe\core\hlogger\LogManager;

$hlog = new LogManager([]);

// 获取预定义hehe日志记录器单例对象
$heheLogger = $hlog->getLogger('hehe');

// 获取预定义hehe日志记录器新对象
$heheLogger = $hlog->newLogger('hehe');

// 获取一个空的日志记录器对象
$heheLogger = $hlog->newLogger();

$heheLogger = $hlogger->newLogger([
    'bufferLimit'=>2,
    'levels'=>'error,info',
    'categorys'=>'',// 'admin\controller*'
    'filters'=>'default,hehe',
    'handlers'=>'default,hehe',
]);

// 记录器新增日志过滤器
$filter = $hlog->levelFilter('error,info');
$heheLogger->addFilter($filter);

// 记录器新增日志处理器
$file = 'user/xxx.log';
$handler = $hlog->fileHandler($file); 
$heheLogger->addHandler($handler);

// 设置记录器日志格式器
$hlogger->setFormatter($hlog->lineFormatter('{date:Y-m-d:H:i},{level},:{msg} ,file:{file}, line:{line},{class}->{fun}{n}'));

// 记录日志
$heheLogger->info('info log message');

```

## 日志处理器
- 说明
```
日志处理器基类:hehe\core\hlogger\handlers\LogHandler
作用:持久化日志信息，比如文件处理器,数据库处理器,邮件处理器等等
全局属性:
'filter'=>'',// 日志过滤器
'formatter'=>'',// 日志格式器

```

### 预定义处理器
```php
use hehe\core\hlogger\LogManager;
$conf = [
     // 预定义日志处理器定义
    'handlers'=>[
        // 日志文件处理器
        'default'=>[
            'class'=>'FileHandler',
            'logFile'=>'/home/hehe/www/logs/hehep.log',// 日志文件
            'filter'=>'',// 日志过滤器
            'formatter'=>'default'// 日志格式器
        ],
        
        'default1'=>[
            'class'=>'FileHandler',
            'logFile'=>'/home/hehe/www/logs/hehep.log',// 日志文件
            'filter'=>[// 日志过滤器
               'levels'=>['error'],
               'categorys'=>['admin\controller*']
            ],
            'formatter'=>[// 日志格式器
                'tpl'=>'{date:Y-m-d:H:i} :{msg} ,file:{file}, line:{line},{class}->{fun} {n}',
            ]
        ]
    ],
    
    // 预定义其他配置
];
```

### 自定义处理器
```php
namespace hehe\core\hlogger\handlers;

use hehe\core\hlogger\base\LogHandler;
use hehe\core\hlogger\base\Message;

class FileHandler extends LogHandler
{
    // 构造函数
    public function __construct(string $logFile = '',array $propertys = [])
    {
        $this->logFile = $logFile;
        parent::__construct($propertys);
   
    }

    // 同时处理多条日志消息
    public function handleMessages(array $messages)
    {
        foreach ($messages as $message) {
            $this->handleMessage($message);
        }
    }

    // 处理日志消息
    public function handleMessage(Message $message):void
    {
        // 获取格式化后消息
        $log_str = $message->getMsg();
       
    }
}
```

### 创建处理器对象
```php
use hehe\core\hlogger\LogManager;

$hlog = new LogManager([]);
$logger = $hlog->newLogger();

// 创建预定义“default1”日志处理器
$handler = $hlog->newHandler('default1');

// 创建空的日志处理器对象
$handler = $hlog->newHandler();
$handler->addFilter($hlog->levelFilter('error,info'));
$handler->setFormatter($hlog->lineFormatter('{date:Y-m-d:H:i} :{msg} ,file:{file}, line:{line},{class}->{fn} {n}'));
$logger->addHandler($handler);

// 记录日志
$logger->error('error log message');

```

### 默认处理器集合
#### 文件处理器
- 说明
```
基类:hehe\core\hlogger\handlers\FileHandler
属性:
'logFile'=>'',// 日志文件
'useLock'=>false,// 是否使用文件锁(flock),默认为false
```

- 示例代码
```php
use hehe\core\hlogger\LogManager;
$hlog = new LogManager();
$logger = $hlog->newLogger();

// 创建处理器, 默认日志文件为/home/hehe/www/logs/hehep.log,
$handler = $hlog->fileHandler('/home/hehe/www/logs/hehep.log');
$handler = $hlog->fileHandler([
    'logFile'=>'/home/hehe/www/logs/hehep.log',
    'useLock'=>false,// 是否使用文件锁(flock),默认为false
]);

$logger->addHandler($handler);

$logger->info('info log message');
```

#### 文件大小轮转处理器
- 说明
```
类名:hehe\core\hlogger\handlers\ByteRotatingFileHandler, 继承自RotatingFileHandler
属性:
'logFile'=>'',// 日志文件
'maxByte'=>0,// 最大文件容量,单kb,日志文件超过该值时,将创建新的日志文件
'rotatefmt'=>'{filename}_{date:YmdHis}_{rand:6}',// 轮转文件格式,变量可以取自日志上下文, filename:当前日志文件名 ,date:为当前日期,"YmdHis" 日期格式,rand:为6位随机数
'rotatefmtParams'=>['filename'=>'\w+'],// 轮转文件格式参数,可设置变量的正则表达式
'backupCount'=>0,// 最大备份文件数量,默认为0,表示不限制
'backupfmt'=>'{filename}_up{index}',// 备份文件格式,变量可以取自日志上下文, filename:当前轮转文件名 ,index:自增序号 日期格式
'backupfmtParams'=>['index'=>'\d+'],// 备份文件格式参数,可设置变量的正则表达式

```

- 示例代码
```php
use hehe\core\hlogger\LogManager;
$hlog = new LogManager();
$logger = $hlog->newLogger();

// 创建处理器, 默认日志文件为/home/hehe/www/logs/hehep.log, 日志文件大小为5M
$handler = $hlog->byteRotatingFileHandler('/home/hehe/www/logs/hehep.log',1024 * 5);
$handler = $hlog->byteRotatingFileHandler([
    'logFile'=>'/home/hehe/www/logs/hehep.log',
    // 最大文件容量,单kb,5M
    'maxByte'=>1024 * 5,
    // filename:当前日志文件名 ,date:为当前日期,"YmdHis" 日期格式,rand:为6位随机数
    'rotatefmt'=>'{filename}_{date:YmdHis}_{rand:6}',
]);

$logger->addHandler($handler);

$logger->info('info log message');
```

#### 文件日期轮转处理器
- 说明
```
类名:hehe\core\hlogger\handlers\TimedRotatingFileHandler, 继承自RotatingFileHandler
属性:
'logFile'=>'',// 日志文件
'rotateMode'=>'d',// 日志轮转模式,支持d(天),h(小时),m(月),s(分钟),w(周),y(年),默认为d,表示按天轮转
'rotatefmt'=>'{filename}_{date:YmdHi}_hehe',// 轮转文件格式,变量可以取自日志上下文, filename:当前日志文件名 ,date:为当前日期,"YmdHis" 日期格式
'rotatefmtParams'=>['filename'=>'\w+'],// 轮转文件格式参数,可设置变量的正则表达式
'maxFile'=>0,// 最大文件数量,默认为0,表示不限制'
'maxByte'=>0,// 最大文件容量,单kb,日志文件超过该值时,将创建新的日志文件
'backupCount'=>0,// 最大备份文件数量,默认为0,表示不限制
'backupfmt'=>'{filename}_up{index}',// 备份文件格式,变量可以取自日志上下文, filename:当前轮转文件名 ,index:自增序号 日期格式
'backupfmtParams'=>['index'=>'\d+'],// 备份文件格式参数,可设置变量的正则表达式
```

- 示例代码
```php
use hehe\core\hlogger\LogManager;
$hlog = new LogManager();
$logger = $hlog->newLogger();
$timedRotatingFileHandler = $logger->timedRotatingFileHandler('/home/hehe/www/logs/hehep.log','s');
$timedRotatingFileHandler->setMaxFiles(2);
$timedRotatingFileHandler->setRotatefmt('{filename}_{date:YmdHi}_hehe');
// $timedRotatingFileHandler->setRotatefmt('{date:Ym/d/H/}{filename}_{date:YmdHi}_hehe'); 目录日期格式
$timedRotatingFileHandler->setMaxByte(10);
$timedRotatingFileHandler->setBackupCount(4);
$logger->addHandler($timedRotatingFileHandler);
$logger->setFormatter($logger->lineFormatter('{date:Y-m-d:H:i},{level},{msg} ,file:{file}, line:{line} {n}'));
$logger->error("default logger error message");

```

## 日志过滤器
- 说明
```
日志过滤器基类:hehe\core\hlogger\base\LogFilter
作用:过滤日志消息，比如只记录error级别日志，或者只记录"admin\controller*"控制器的日志
属性:
'levels'=>'info,error',// 支持的日志级别
'categorys'=>['admin\controller*'],// 支持的日志分类
```

### 预定义过滤器
```php
use hehe\core\hlogger\LogManager;
$conf = [
    // 预定日志过滤器
    'filters'=>[
        'default'=>[
            //'class'=>'',// 过滤器类名,未填则默认为LevelFilter
            'levels'=>['info','error'],// 过滤器支持的级别
            'categorys'=>['admin\controller*'],// 过滤器支持分类
        ],
        
        'default1'=>[
            'class'=>'LevelFilter',// 过滤器类名,未填则默认为LevelFilter
            'levels'=>['info','error'],// 过滤器支持的级别
            'categorys'=>['admin\controller*'],// 过滤器支持分类
        ],
    ],
];

```

### 自定义过滤器
```php
namespace hehe\core\hlogger\filters;

use hehe\core\hlogger\base\LogFilter;
use hehe\core\hlogger\base\Message;
use hehe\core\hlogger\Utils;

class LevelFilter extends LogFilter
{

    public function __construct(string $levels = '',array $propertys = [])
    {
        parent::__construct($propertys);
    }
    
    // 检查消息是否满足条件
    public function check(Message $message):bool
    {
        // @todo 实现自己的过滤规则
        
    }  
}
```

### 创建过滤器对象
```php
use hehe\core\hlogger\LogManager;

$hlog = new LogManager([]);
$logger = $hlog->newLogger();

// 创建预定义“default1”日志处理器
$defaultFilter = $hlog->newFilter('default1');
$logger->addFilter($defaultFilter);

// 创建空的日志处理器对象
$emptyFilter = $hlog->newFilter();
$emptyFilter->setLevels('error,info');
$emptyFilter->setCategorys(['admin\controller*']);
$logger->addFilter($emptyFilter);

// 创建level过滤器对象
$levelFilter = $hlog->levelFilter('error,info',['admin\controller*']);
$logger->addFilter($levelFilter);

$logger->addHandler($hlog->fileHandler('/home/hehe/www/logs/hehep.log',1024 * 5));

// 记录日志
$logger->error('error log message');

```

## 日志格式器
- 说明
```
日志格式器基类:hehe\core\hlogger\base\LogFormatter
作用:格式化日志消息，比如将日志消息转换为字符串  
```
  
### 预定义日志格式器
```php
$conf = [
    // 日志格式器定义
    'formatters'=>[
        'default'=>[
            //'class'=>'',// 消息格式器类名,未填则默认为LineFormatter
            'tpl'=>'{date:Y-m-d:H:i} :{msg},file:{file}, line:{line},{class}->{fn} {n}',
        ]
    ],
];

```

### 自定义日志格式器
```php
namespace hehe\core\hlogger\formatters;

use hehe\core\hlogger\base\LogFormatter;
use hehe\core\hlogger\base\Message;
use hehe\core\hlogger\Utils;

class LineFormatter extends LogFormatter
{
    public function __construct(string $tpl = '',array $propertys = [])
    {
        $this->tpl = $tpl;
        parent::__construct($propertys);
    }
    
    // 解析消息
    public function parse(Message $message)
    {
        // 获取上下文对象
        $context = $message->getContext();
        // 获取上下文值
        $ip = $context->getValue('ip');
        $class = $context->getValue('class');
        $msg = $context->getValue('msg');
        $date = $context->getValue('date',['Y-m-d:H:i']);
    }
}
```

### 创建格式器器对象
```php
use hehe\core\hlogger\LogManager;

$hlog = new LogManager([]);
$logger = $hlog->newLogger();

// 创建预定义“default1”日志处理器
$defaultFormatter = $hlog->newFormatter('default1');
$logger->setFormatter($defaultFormatter);

// 创建空的日志处理器对象
$emptyFormatter = $hlog->newFormatter();
$emptyFormatter->setTpl('{date:Y-m-d:H:i} :{msg},file:{file}, line:{line},{class}->{fn} {n}');
$logger->setFormatter($emptyFormatter);

// 创建level过滤器对象
$lineFormatter = $hlog->lineFormatter('{date:Y-m-d:H:i} :{msg} ,file:{file}, line:{line},{class}->{fn} {n}');
$logger->setFormatter($lineFormatter);

$logger->addHandler($hlog->fileHandler('/home/hehe/www/logs/hehep.log',1024 * 5));
// 记录日志
$logger->error('error log message');

```

### 日志消息转单行字符串格式器
- 默认日志模版变量
```
date:日志时间,基本格式:{date:Y-m-d:H:i}
msg:日志内容,基本格式:{msg}
level:日志级别,基本格式:{level}
file:记录日志时的文件路径,基本格式:$file}
line:记录日志时的行数,基本格式:{line}
class:记录日志时的类名,基本格式:{class}
fn:记录日志时的方法名或函数名,基本格式:{fn}
cate:调用日志时位置(格式:类名:方法名),基本格式:{cate}
n:换行符,'\n',基本格式:{n}
```

- 示例代码
```php
use hehe\core\hlogger\LogManager;
$hlog = new LogManager();
$logger = $hlog->newLogger();
// 创建单行字符串日志格式器
$lineFormatter = $hlog->lineFormatter('{date:Y-m-d:H:i} :{msg} ,file:{file}, line:{line},{class}->{fn} {n}');

// 创建预定义“default”日志格式器
$lineFormatter = $hlog->newFormatter('default');

$logger->setFormatter($lineFormatter);

```

## 日志上下文
- 说明
```
日志上下文基类:hehe\core\hlogger\base\LogContext
作用:日志上下文,比如记录日志时的任务ID,用户ID等

```

### 预定义日志上下文
```php
$conf = [
    'contexts'=>[
        'default'=>[
            'class'=>'hehe\core\hlogger\contexts\TraceContext',
            'skipClasses'=>[],// 跳过的类名,
            'skipFuns'=>[],// 跳过的函数名,
        ]
    ],

];

```

### 自定义日志上下文
```php
namespace hehe\core\hlogger\contexts;

use hehe\core\hlogger\base\LogContext;

class LineContext extends LogContext
{
    public function handle():array
    {
        return [
            'user'=>'admin',
            'ip'=>'127.0.0.1'
        ];
    }
}

```

### 日志上下文示例代码
```php
use hehe\core\hlogger\LogManager;
use \hehe\core\hlogger\contexts\TraceContext;
$hlog = new LogManager();
$logger = $hlog->newLogger();
// 创建单行字符串日志格式器
$lineFormatter = $hlog->lineFormatter('{date:Y-m-d:H:i},{msg} {user}:{ip},file:{file}, line:{line},{class}->{fn} {n}');

$logger->setFormatter($lineFormatter);

// 创建预定义“default”日志上下文
$defaultContext = $hlog->newContext('default');

// 快速创建traceContext日志上下文
$traceContext = $hlog->traceContext(["hlogger"]);
$logger->addContext($traceContext);

// new TraceContext 上下文
$traceContext = new \hehe\core\hlogger\contexts\TraceContext();
$logger->addContext($traceContext);
$logger->addContext(TraceContext::class);

// 添加闭包上下文
$logger->addContext(function(){
    return [
        'user'=>'admin',
        'ip'=>'127.0.0.1'
    ];
});

// 数组上下文
$logger->error('error log message',['goodid'=>'123']);

```
    










