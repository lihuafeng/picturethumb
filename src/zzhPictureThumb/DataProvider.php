<?php
/**
 * Created by PhpStorm.
 * User: 华
 * Date: 2020/5/11
 * Time: 13:54
 */

namespace zzhPictureThumb;

/**
 * Class DataProvider
 * @package common\services
 */
class DataProvider
{
    protected static $serviceClient;

    /**
     * @param $class
     * @return mixed
     */
    public static function client($class)
    {

        if(self::$serviceClient instanceof $class) return self::$serviceClient;
        self::$serviceClient=new $class;
        return self::$serviceClient;
    }
}