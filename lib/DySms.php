<?php

namespace Aliyun;

use Aliyun\Core\Config;
use Aliyun\DySmsException;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;
use Aliyun\Api\Sms\Request\V20170525\QuerySendDetailsRequest;


/**
 * 阿里大鱼短信接口
 */
class DySms
{
    /**
     * 构造函数
     */
    public function __construct($config = null)
    {
        // 加载区域结点配置
        Config::load();

        // 短信API产品名
        $product = "Dysmsapi";

        // 短信API产品域名
        $domain = "dysmsapi.aliyuncs.com";

        // 暂时不支持多Region
        $region = "cn-hangzhou";

        // 服务结点
        $endPointName = "cn-hangzhou";

        // 读取配置文件中的 accessKeyId 和 accessKeySecret
        $this->config = $config ?: config('sms.dysms');

        // 初始化用户Profile实例
        $profile = DefaultProfile::getProfile($region, $this->config['accessKeyId'], $this->config['accessKeySecret']);

        // 增加服务结点
        DefaultProfile::addEndpoint($endPointName, $region, $product, $domain);

        // 初始化AcsClient用于发起请求
        $this->acsClient = new DefaultAcsClient($profile);
    }

    /**
     * 发送短信
     * @param  string $templateCode  必填, 短信模板Code，应严格按"模板CODE"填写
     * @param  string $phoneNumbers  必填, 短信接收号码
     * @param  array  $templateParam 选填, 假如模板中存在变量
     * @param  string $signName      选填, 短信签名，缺省为配置中的签名
     * @param  string $outId         选填, 发送短信流水号
     * @return boolean
     */
    public function send($templateCode, $phoneNumbers, $templateParam = null, $signName = null, $outId = null)
    {
        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = new SendSmsRequest();

        // 必填，设置雉短信接收号码
        $request->setPhoneNumbers($phoneNumbers);

        // 必填，设置签名名称
        $request->setSignName($signName ?: $this->config['signName']);

        // 必填，设置模板CODE
        $request->setTemplateCode($templateCode);

        // 可选，设置模板参数
        if($templateParam) {
            $request->setTemplateParam(json_encode($templateParam));
        }

        // 可选，设置流水号
        if($outId) {
            $request->setOutId($outId);
        }

        // 发起访问请求
        $acsResponse = $this->acsClient->getAcsResponse($request);

        // 打印请求结果
        // dump($acsResponse);

        if ($acsResponse->Code == 'OK') {
            return true;
        } else {
            throw new DySmsException($acsResponse->Message, $acsResponse);
        }
    }

    /**
     * 查询短信发送情况
     *
     * @param  string  $phoneNumbers 必填, 短信接收号码
     * @param  string  $sendDate     必填，短信发送日期，格式Ymd (e.g. 20170710)
     * @param  integer $pageSize     必填，分页大小
     * @param  integer $currentPage  必填，当前页码
     * @param  string  $bizId        选填，短信发送流水号
     * @return stdClass
     */
    public function queryDetails($phoneNumbers, $sendDate, $pageSize = 10, $currentPage = 1, $bizId=null)
    {
        // 初始化QuerySendDetailsRequest实例用于设置短信查询的参数
        $request = new QuerySendDetailsRequest();

        // 必填，短信接收号码
        $request->setPhoneNumber($phoneNumbers);

        // 选填，短信发送流水号
        $request->setBizId($bizId);

        // 必填，短信发送日期，支持近30天记录查询，格式Ymd
        $request->setSendDate($sendDate);

        // 必填，分页大小
        $request->setPageSize($pageSize);

        // 必填，当前页码
        $request->setCurrentPage($currentPage);

        // 发起访问请求
        $acsResponse = $this->acsClient->getAcsResponse($request);

        // 打印请求结果
        // dump($acsResponse);

        return $acsResponse;
    }
}
