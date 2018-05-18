<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    public function index(){
        //从配置文件中取得信息
        $token = C('token');

        //获得参数 signature nonce token timestamp echostr
        $nonce = $_GET['nonce'];
        $timestamp = $_GET['timestamp'];
        $echostr = $_GET['echostr'];
        $signature = $_GET['signature'];
        echo 'dd' . $token;

        //形成数组，然后按字典序排序
        $array = array($nonce, $timestamp, $token);
        sort($array);
        //拼接成字符串，sha1加密，然后与signature进行比对
        $str = sha1(implode($array));
        if ($str == $signature && $echostr) {
            //第一次接入weixin验证接口，会多传入一个$echostr参数
            echo $echostr;
            exit;
        } else {
            //微信给发送的信息
            $this->reponseMsg();
        }
    }

    /**
     * 接受时间推送并回复
     */
    public function reponseMsg() {
        //1、获取到推送的xml格式信息
        //2、处理消息类型，并返回
        $postArr = $GLOBALS['HTTP_RAW_POST_DATA'];
        $postObj = simplexml_load_string($postArr);
        //判断该数据包是否是订阅的事件推送
        if (strtolower($postObj->MsgType) == 'event') {
            //如果是官族subscribe事件
            if (strtolower($postObj->Event) == 'subscribe') {
                //回复客户信息
                $toUser   = $postObj->FromUserName;
                $fromUser = $postObj->ToUserName;
                $time     = time();
                $msgtype  = 'text';
                $content  = '欢迎关注！';
                $template = '<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							</xml>';
                $info     = sprintf($template, $toUser, $fromUser,$time,$msgtype,$content);
                echo $info;
            }
        }
    }
}