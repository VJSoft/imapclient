<?php

namespace Vjsoft\Imapclient\Controllers;

class ImapClientPackageManager
{
    protected $app;

    protected $accounts = [];

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function account($name = null)
    {
        $name = $name ?: $this->getDefaultAccount();

        if (! isset($this->accounts[$name])) {
            $this->accounts[$name] = $this->resolve($name);
        }

        return $this->accounts[$name];
    }

    protected function resolve($name)
    {
        $config = $this->getConfig($name);

        return new Client($config);
    }

    protected function getConfig($name)
    {
        if ($name === null || $name === 'null') {
            return ['driver' => 'null'];
        }

        return $this->app['config']["imap.accounts.{$name}"];
    }

    public function getDefaultAccount()
    {
        return $this->app['config']['imap.default'];
    }

    public function setDefaultAccount($name)
    {
        $this->app['config']['imap.default'] = $name;
    }

    public function __call($method, $parameters)
    {
        $callable = [$this->account(), $method];

        return call_user_func_array($callable, $parameters);
    }
}