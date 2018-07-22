<?php
namespace Home\Controller;
use Think\Controller;
require ('./sphinxapi.php');

class WxController extends Controller {
    public function index(){
        //从配置文件中取得信息
        $token = C('token');

        //获得参数 signature nonce token timestamp echostr
        $nonce = $_GET['nonce'];
        $timestamp = $_GET['timestamp'];
        $echostr = $_GET['echostr'];
        $signature = $_GET['signature'];

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
            //:print_r("1");
            $this->reponseMsg();
        }
    }

    /**
     * 接受事件推送并回复
     */
    public function reponseMsg() {
        //1、获取到推送的xml格式信息
        //2、处理消息类型，并返回
        $postStr = file_get_contents('php://input') ? file_get_contents('php://input') : $GLOBALS["HTTP_RAW_POST_DATA"];
        // print_r($postStr);
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $toUser     = $postObj->FromUserName;
        $fromUser   = $postObj->ToUserName;
//        var_dump($postObj);
        $time       = time();

        //判断该数据包是否是订阅的事件推送

        if (strtolower($postObj->MsgType) == 'event') {
            //如果是关注subscribe事件
            if (strtolower($postObj->Event) == 'subscribe') {
                //回复客户信息
                $toUser   = $postObj->FromUserName;
                $fromUser = $postObj->ToUserName;
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
                exit;
            }
        } elseif (strtolower($postObj->MsgType) == 'text') {
            //回复客户信息
            $cusContent = $postObj->Content;
            $time       = time();

            if (!preg_match('//u', $cusContent)) {
                $cusContent = iconv('gb2312', 'UTF-8//IGNORE', $cusContent);
            }

            //处理返回信息
            if("?" == $cusContent || "？" == $cusContent) {
                $content  = "呦桃优惠券为您提供各类淘宝天猫优惠券，您可直接回复商品信息查询对应的优惠券信息。如：回复“女装”获取女装商品信息(*^_^*)";
                $template = '<xml>
                                 <ToUserName><![CDATA[%s]]></ToUserName>
                                 <FromUserName><![CDATA[%s]]></FromUserName>
                                 <CreateTime>%s</CreateTime>
                                 <MsgType><![CDATA[text]]></MsgType>
                                 <Content><![CDATA[%s]]></Content>
                             </xml>';
                $info     = sprintf($template, $toUser, $fromUser,$time,$content);
                echo $info;
                exit;
            } else {
                //                $cusContent = "手工";
                $sph = new \SphinxClient();
                $sph->SetServer('localhost', 9312);
                $sphRet = $sph->Query($cusContent, "*");
                $ids = array_keys($sphRet['matches']);

                print_r($ids . "xx". $cusContent);


                $tkModel = D('Home/Ticket');
                $ticInfo = $tkModel->getImgUrl($ids);
                $count = count($ticInfo);
                $item = '';

                //回复测试
                $returnStr = "<xml>
                                 <ToUserName><![CDATA[$toUser]]></ToUserName>
                                 <FromUserName><![CDATA[$fromUser]]></FromUserName>
                                 <CreateTime>$time</CreateTime>
                                 <MsgType><![CDATA[news]]></MsgType>
                                 <ArticleCount>$count</ArticleCount>
                                 <Articles>";
                foreach ($ticInfo as $id => $ticArr) {
                    $url = "http://youtaoquan.xin/Home/Index/ticket/id/" . $id;
                    $desc = "原价: ￥" . $ticArr['sp_price'] . " 优惠券: ￥" . $ticArr['yhq_value'];
                    $item .= "<item>
                                  <Title><![CDATA[{$ticArr['sp_name']}]]></Title>
                                  <Description><![CDATA[{$desc}]]></Description>
                                  <PicUrl><![CDATA[{$ticArr['sp_main_picture']}]]></PicUrl>
                                  <Url><![CDATA[{$url}]]></Url>
                               </item>";
                }
                $returnStr .= $item;
                $returnStr .=  "</Articles>
                                 </xml>";
                echo $returnStr;
                exit;
            }
        }
    }
}
