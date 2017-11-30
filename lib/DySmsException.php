<?php

namespace Aliyun;

/**
 * 大鱼短信异常
 */
class DySmsException extends \Exception
{
    protected $response;

    public function __construct($message = '大鱼短信异常', $response = null)
    {
        parent::__construct($message);

        if ($response) {
            $this->response = $response;
        }
    }

    /**
     * 接口响应
     *
     * @return
     */
    public function response()
    {
        return $this->response;
    }
}

