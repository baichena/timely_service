<?php

namespace exception;

class BaseException extends \Exception
{
    protected $data;

    public function __construct($message = null, $code = 0, $previous = null, $data = [])
    {
        $this->data = $data;
        if (!empty($message) && is_string($message)) {
            parent::__construct($message, $code, $previous);
        } else {
            $msg = isset(self::$errorMessages[$code]) ? self::$errorMessages[$code] : '错误代码:' . $code;
            parent::__construct($msg, $code, $previous);
        }
    }

    public function getData()
    {
        return $this->data;
    }

    const HTTP_NOT_FOUND                                      = 404;
    const HTTP_INTERNAL_ERROR                                 = 500;


    const PUSH_TO_QUEUE_FAILED                                = 600;
    const PARAMS_MISSING                                      = 601;
    const DATA_ABNORMAL                                       = 602;
    const BAD_VALIDATOR_PARAMS                                = 700;
    const HTTP_METHOD_OPTION                                  = 998;
    const BLACK_LIST_USER                                     = 701;   // 该商户已经被锁定，详情请联系管理人员
    const SYS_MAINTAIN                                        = 99001; // 维护模式
    const API_DELETED                                         = 99003; // 接口已停止使用

    // 1000 - 1999 支付模块
    const ERR_BUSINESS_NOT_EXEIT                              = 1001;  //商户不存在
    const ERR_SBUSINESS_CONFIG                                = 1002;  //商户配置错误
    const ERR_CHANNEL_CAPTCHA_NOT_EXEIT                       = 1003;  //渠道不存在
    const ERR_AMOUNT                                          = 1004;   //短信验证码已经过期
    const ERR_AMOUNT_CONFIG                                   = 1005;   //支付金额限制配置错误
    const ERR_ORDER_SN_EXEIT                                  = 1006;   //订单已存在
    const ERR_SING                                            = 1007;   //签名错误
    const ERR_CODE_CAPTCHA_NOT_EXEIT                          = 1008;   //支付通道不存在
    const ERR_USER_CAPTCHA_NOT_EXEIT                          = 1009;   //用户未分配渠道
    const ERR_CHANNLE_NOT_EXEIT                               = 1032;   //渠道未开启
    const ERR_SMS_PHONE_MISSING                               = 2007;   //手机号码缺失
    const ERR_SMS_PHONE_ERROR                                 = 2008;   //手机号码非法
    const ERR_SMS_TYPE_ERROR                                  = 2009;   //短信类别非法
    const ERR_SMS_TYPE_MISSING                                = 2010;   //短信类别缺失
    const ERR_SMS_TYPE_PARAMETER_ERROR                        = 2011;   //短信参数格式非法
    const ERR_SMS_TYPE_PARAMETER_MISSING                      = 2012;   //短信参数缺失
    const ERR_SMS_TYPE_TEMPLATE_MISSING                       = 2013;   //短信模版缺失
    const ERR_SMS_CAPTCHA_ERROR                               = 2014;   //短信验证码错误
    const ERR_SMS_SEND_RECORD_ERROR                           = 2015;
    const ERR_SEND_SMS_FAILED                                 = 2016;
    const ERR_READ_MESSAGE_FIELD                              = 2017;
    const ERR_SMS_IN_BLACKLIST                                = 2018;
    const ERR_MESSAGE_NOT_FIND                                = 2019;
    const PASSWORD_FAILD_FIVE_TIMES                           = 2020;
    const ERR_SMS_PHONE_EXISTED                               = 2021;   //手机号码已存在
    const ERR_SMS_PHONE_NOT_EXISTS                            = 2022;   //手机号码不存在
    const ERR_SMS_SMS_NOT_EXISTS                              = 2023;   //手机号码不存在
    // 1000 - 1999 支付模块其他模块请勿占用 END

   //风控规则
    const NO_IN_TIME                                           = 3001;//不在交易时间段
    const NO_IN_MONEY                                          = 3002;//金额不在范围内
    const NO_IN_UTL                                            = 3003;//域名白名单
    const ERR_TOTLE_MAX_AMOUNT                                 = 3003;//交易最大额
    const ERR_TOTLE_MAX_COUNT                                  = 3004;//单位交易最大笔数
    const ERR_TOTLE_TIME_MAX_AMOUNT                            = 3005;//单位时间交易总金额
    const ERR_MONEY                                            = 3006;//支付金额配置错误
    public static $errorMessages = [
        self::HTTP_NOT_FOUND                              => '请求的URL不存在',
        self::PUSH_TO_QUEUE_FAILED                        => '入队失败',
        self::PARAMS_MISSING                              => '参数缺失',
        self::DATA_ABNORMAL                               => '数据异常',
        self::HTTP_METHOD_OPTION                          => '',
        self::BLACK_LIST_USER                             => '该商户已经被锁定，详情请联系客服',
        self::SYS_MAINTAIN                                => '维护模式',
        // 1001 - 1999 下单逻辑 START
        self::ERR_BUSINESS_NOT_EXEIT                      => '商户不存在',
        self::ERR_SBUSINESS_CONFIG                        => '商户配置错误',
        self::ERR_CHANNEL_CAPTCHA_NOT_EXEIT               => '渠道关闭中,暂时无法连接',
        self::ERR_CODE_CAPTCHA_NOT_EXEIT                  => '通道关闭中,暂时无法连接',
        self::ERR_USER_CAPTCHA_NOT_EXEIT                  => '用户未分配通道,暂时无法连接',
        self::ERR_AMOUNT                                  => '支付金额错误',
        self::ERR_AMOUNT_CONFIG                           => '支付金额配置错误,请联系平台管理人员',
        self::ERR_ORDER_SN_EXEIT                          => '订单已存在',
        self::ERR_CHANNLE_NOT_EXEIT                       => '渠道未开启',
        // 2000 - 2999 短信模块使用 START
        self::ERR_SMS_PHONE_MISSING                       => '手机号码缺失',
        self::ERR_SMS_PHONE_ERROR                         => '手机号码非法',
        self::ERR_SMS_PHONE_EXISTED                       => '手机号码已存在',
        self::ERR_SING                                    => '签名错误',
        self::ERR_SMS_TYPE_MISSING                        => '短信类别缺失',
        self::ERR_SMS_TYPE_PARAMETER_ERROR                => '短信参数格式非法',
        self::ERR_SMS_TYPE_PARAMETER_MISSING              => '短信类别缺失',
        self::ERR_SMS_TYPE_TEMPLATE_MISSING               => '短信模版缺失',
        self::ERR_SMS_CAPTCHA_ERROR                       => '短信验证码错误',
        self::ERR_SMS_SEND_RECORD_ERROR                   => '短信验证码已失效，请重新发送',
        self::ERR_SEND_SMS_FAILED                         => '短信验证码发送失败',
        self::ERR_READ_MESSAGE_FIELD                      => '站内信不存在或已读',
        self::ERR_SMS_IN_BLACKLIST                        => '验证码发送失败，手机号码在黑名单',
        self::ERR_MESSAGE_NOT_FIND                        => '无消息',
        self::PASSWORD_FAILD_FIVE_TIMES                   => '密码错误次数超过5次，半小时内无法登录',
        self::ERR_SMS_PHONE_NOT_EXISTS                    => '手机号码不存在',
        // 3000 - 3999 风控模块暂用

    ];


}
