<?php
namespace hehe\core\hlogger\contexts;

use hehe\core\hlogger\base\LogContext;

/**
 * web 相关参数
 */
class WebContext extends LogContext
{

    /**
     * web相关参数
     * @var array
     */
    protected $webData;

    protected $fields = array(

    );

    public function __construct(array $webData = [])
    {
        if (empty($webData)) {
            $webData = &$_SERVER;
        }

        $this->webData = $webData;

        $this->fields = [
            'url' => 'REQUEST_URI',
            'ip'  => [$this,'getUserIp'],
            'method' => 'REQUEST_METHOD',
            'server' => 'SERVER_NAME',
            'referrer' => 'HTTP_REFERER',
        ];
    }

    /**
     * @param string $name 名称 REQUEST_METHOD
     * @param string $alias 别名 method
     * @return $this
     */
    public function addField(string $name,string $alias = ''):self
    {
        if ($alias !== '') {
            $this->fields[$alias] = $name;
        } else {
            $this->fields[$name] = $name;
        }

        return $this;
    }

    protected function getCtxData():array
    {
        $ctx = [];
        foreach ($this->fields as $alias => $name) {
            if (isset($this->webData[$name])) {
                $ctx[$alias] = $this->webData[$name];
            } else {
                $ctx[$alias] = '';
            }
        }

        return $ctx;
    }

    public function getUserIp():string
    {
        if (isset($this->webData['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $this->webData['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown',$arr);
            if (false !== $pos) {
                unset($arr[$pos]);
            }
            $userIp = trim($arr[0]);
        } else if (isset($this->webData['HTTP_CLIENT_IP'])) {
            $userIp = $this->webData['HTTP_CLIENT_IP'];
        } else if (isset($this->webData['REMOTE_ADDR'])) {
            $userIp = $this->webData['REMOTE_ADDR'];
        } else {
            $userIp = '';
        }

        return $userIp;
    }

    public function handle():array
    {
        return $this->getCtxData();
    }
}
