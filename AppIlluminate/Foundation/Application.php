<?php

namespace AppIlluminate\Foundation;

use Illuminate\Foundation\Application as OriginalApplication;

class Application extends OriginalApplication
{
    /**
     * .env 文件名称
     * The environment file to load during bootstrapping.
     *
     * @var string
     */
    protected $environmentFile = '_ENV';

    /**
     * 原路径 iFramework/app 改为 app
     *
     * Get the path to the application "app" directory.
     *
     * @return string
     */
    public function path()
    {
        return dirname($this->basePath).DIRECTORY_SEPARATOR.'app';
    }

    /**
     * 原路径 iFramework/config 改为 config
     *
     * Get the path to the application configuration files.
     *
     * @return string
     */
    public function configPath()
    {
        return dirname($this->basePath).DIRECTORY_SEPARATOR.'config';
    }

    /**
     * 原路径 iFramework/resources/lang 改为 config/lang
     *
     * Get the path to the language files.
     *
     * @return string
     */
    public function langPath()
    {
        return $this->configPath().DIRECTORY_SEPARATOR.'lang';
    }

    /**
     * 原路径 iFramework/storage 改为 tmp/storage
     *
     * Get the path to the storage directory.
     *
     * @return string
     */
    public function storagePath()
    {
        return $this->storagePath ?: dirname($this->basePath).DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'storage';
    }

    /**
     * 原路径 iFramework/database 改为 drive/database
     *
     * Get the path to the database directory.
     *
     * @return string
     */
    public function databasePath()
    {
        return $this->databasePath ?: dirname($this->basePath).DIRECTORY_SEPARATOR.'drive'.DIRECTORY_SEPARATOR.'database';
    }

    /**
     * 原路径 iFramework 改为 config
     *
     * .env 文件所在路径
     *
     * Get the path to the environment file directory.
     *
     * @return string
     */
    public function environmentPath()
    {
        return $this->environmentPath ?: $this->configPath();
    }
}
