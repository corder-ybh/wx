<?php
namespace Home\Controller;

use Think\Controller;

require('./sphinxapi.php');

class WxController extends Controller
{
    public function index()
    {
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
            $this->reponseMsg();
        }
    }

    /**
     * 接受事件推送并回复
     */
    public function reponseMsg()
    {
        //1、获取到推送的xml格式信息
        //2、处理消息类型，并返回
        $postStr = file_get_contents('php://input') ? file_get_contents('php://input') : $GLOBALS["HTTP_RAW_POST_DATA"];
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $toUser = $postObj->FromUserName;
        $fromUser = $postObj->ToUserName;

        $postObj = (object)null;


        //判断该数据包是否是订阅的事件推送
        if (strtolower($postObj->MsgType) == 'event') {
            //如果是关注subscribe事件
            if (strtolower($postObj->Event) == 'subscribe') {
                //关注事件时自动回复回复客户信息
                $content = '欢迎关注呦桃优惠券！本公众号专业提供各类淘宝天猫内部优惠券，您可以直接回复您想购买的商品名称来查找对应商品的优惠券！如：回复“女装”获取女装商品的优惠券！回复:？调出帮助菜单';
                $this->returnTxMsg($toUser, $fromUser, $content);
            }
        }

        //处理文本消息
        if (strtolower($postObj->MsgType) == 'text') {
            //回复客户信息
            $cusContent = $postObj->Content;
            $time = time();

            if (!preg_match('//u', $cusContent)) {
                $cusContent = iconv('gb2312', 'UTF-8//IGNORE', $cusContent);
            }

            //处理返回信息
            if ("?" == $cusContent || "？" == $cusContent) {
                $content = "呦桃优惠券为您提供各类淘宝天猫优惠券，您可直接回复商品信息查询对应的优惠券信息。如：回复“女装”获取女装商品信息(*^_^*)";
                $this->returnTxMsg($toUser, $fromUser, $content);
            } else {
                $sph = new \SphinxClient();
                $sph->SetServer('localhost', 9312);
                $sphRet = $sph->Query($cusContent, "*");
                $ids = array_keys($sphRet['matches']);

                if (empty($ids)) {
                    $content = '对不起，暂未找到您搜索的商品的优惠券信息，我们将记录您的需求，为您提供更加完善的优惠券信息！';
                    $this->returnTxMsg($toUser, $fromUser, $content);
                }

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
                    $item .= "<item>
                                  <Title><![CDATA[{$ticArr['sp_name']}]]></Title>
                                  <Description><![CDATA[{$ticArr['sp_name']}]]></Description>
                                  <PicUrl><![CDATA[{$ticArr['sp_main_picture']}]]></PicUrl>
                                  <Url><![CDATA[{$url}]]></Url>
                               </item>";
                }
                $returnStr .= $item;
                $returnStr .= "</Articles>
                                 </xml>";
                echo $returnStr;
                exit;
            }
        }
    }

    /**
     * 自动回复文本消息
     * @param $toUserName
     * @param $fromUserName
     * @param $content
     */
    public function returnTxMsg($toUserName, $fromUserName, $content)
    {
        //回复客户信息
        $msgtype = 'text';
        $time = time();
        $template = '<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							</xml>';
        $info = sprintf($template, $toUserName, $fromUserName, $time, $msgtype, $content);
        echo $info;
        exit;
    }
}
