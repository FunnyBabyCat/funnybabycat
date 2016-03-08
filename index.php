<?php
/**
  * wechat php test
  */

//define your token
define("TOKEN", "FBC");
$wechatObj = new wechatCallbackapiTest();
$wechatObj->responseMsg();
//$wechatObj->valid();

class wechatCallbackapiTest
{
    /*
    public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }
     */

    public function responseMsg()
    {
        //get post data, May be due to the different environments
        /* 接受微信平台发送过来的用户消息
        *  该消息数据结构为XML, 不是php默认识别的数据类型, 因此用 $GLOBALS["HTTP_RAW_POST_DATA"] 来接受
        *  基本上 $GLOBALS['HTTP_RAW_POST_DATA'] 和 $_POST 是一样的
        *  但是如果 post 过来的数据不是PHP能够识别的, 用 $GLOBALS['HTTP_RAW_POST_DATA'] 来接受接收, 比如 text/xml 或者 soap 等等。
         */
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        //extract post data
        if (!empty($postStr)){

                libxml_disable_entity_loader(true);

                $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $RX_TYPE = trim($postObj->MsgType);

                switch ($RX_TYPE) {
                    case "text":
                        $resultStr = $this->handleText($postObj);
                        break;
                    case "Event":
                        $resultStr = $this->handleEvent($postObj);
                        break;
                    default:
                        $resultStr = "Unknown msg type".$RX_TYPE;
                        break;
                }
                echo $resultStr;
        }else {
            echo "";
            exit;
        }
    }

    public function handleText($postObj){
        $fromUsername = $postObj->FromUserName;
        $toUsername = $postObj->ToUserName;
        $keyword = trim($postObj->Content);
        $time = time();
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[%s]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>0</FuncFlag>
                    </xml>";
        if(!empty($keyword)){
            $msgType = "text";

            if($keyword == "最美的人"){
                $contentStr = "您就是这个世界上最美的人";
            }else{
                $contentStr = "Welcome to wechat world!";                
            }

            $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
            echo $resultStr;
        }else{
            echo "Input something...";
        }        
    }

    public function handleEvent($object){
        $contentStr = "";
        switch ($object->Event) {
            case "subscribe":
                $contentStr = "感谢关注 FunnyBabyCat"."\n"."会生活也是很让人羡慕的.";
                break;
            
            default:
                $contentStr = "Unknown Event: ".$object->Event;
                break;
        }
        $resultStr = $this->responseText($object, $contentStr);
        return $resultStr;
    }
    
    public function responseText($object, $content, $flag = 0){
        $textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[text]]></MsgType>
                    <Content><![CDATA[%s]]></Content>
                    <FuncFlag>%d</FuncFlag>
                    </xml>";
        $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content, $flag);
        return $resultStr;
    }

    /*
    *  加密/校验流程：
    *   1. 将token、timestamp、nonce三个参数进行字典序排序
    *   2. 将三个参数字符串拼接成一个字符串进行sha1加密
    *   3. 开发者获得加密后的字符串可与signature对比，标识该请求来源于微信
     */
    private function checkSignature()
    {
        // you must define TOKEN by yourself
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }
        
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
                
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
}

?>