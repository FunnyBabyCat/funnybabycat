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
                    case "event":
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
            
            //天气
            $str = mb_substr($keyword, -2, 2, "UTF-8");
            $str_key = mb_substr($keyword, 0, -2, "UTF-8");
            if($str == '天气' && !empty($str_key)){
                $data = weather($str_key);
                $contentStr = weather_info($data);
                // $status = $data->{'HeWeather data service 3.0'}[0]->{'status'};
                // if($status == "ok"){
                //     $contentStr = weather_info($data);
                // }else if($status == "unknown city"){
                //     $contentStr = "未知城市";
                // }else{
                //     $contentStr = "服务器无响应或超时";
                // }
            }else if($keyword == "谁是这个世界上最美的人"){
                $ran = rand(1, 10);
                switch ($ran) {
                    case 1:
                        $contentStr = "是你, 你是这个世界上最美的人";
                        break;
                    case 2:
                        $contentStr = "你是史上最美的人, 前无古人, 后无来者!";
                        break;
                    case 3:
                        $contentStr = "有趣的问题. ";
                        break;
                    case 4:
                        $contentStr = "你应该是个学霸. ";
                        break;
                    default:
                        $contentStr = "是白雪公主!";
                        break;
                }
            }else if($keyword == "说爱我" or $keyword == "说你爱我" or $keyword == "你爱我吗"){
                $ran = rand(1, 19);
                switch ($ran) {
                    case 1:
                        $contentStr = "我不爱你";
                        break;
                    case 10:
                        $contentStr = "今晚夜色很美, 适合与朋友分享. ";
                        break;
                    case 19:
                        $contentStr = "好吧, 我爱你.";
                        break;
                    default:
                        $contentStr = "哦...";
                        break;
                }
            }else if( $keyword == "嘿" or $keyword == "嗨" or $keyword == "你好" ){
                $contentStr = "你好, 女神.";
            }else if($keyword == "男" or $keyword == "男的" or $keyword == "帅哥"){
                $contentStr = "你好, 穷比.";
            }else if($keyword == "我丑吗") {
                $contentStr = "当然不, 每次我看着你的时候, 我都为你的飒爽英姿所倾倒. ";
            }else if($keyword == "我美吗") {
                $ran = rand(1, 2);
                switch ($ran) {
                    case 1:
                        $contentStr = "我每次被人夸好看的时候, 都觉得那是因为我和你越来越像了. ";
                        break;
                    default:
                        $contentStr = "皎若太阳升朝霞, 灼若芙蕖出渌波. ";
                        break;
                }
            }else if($keyword == "小猫" or $keyword == "娘口"){
                $ran = rand(1, 2);
                switch ($ran) {
                    case 1:
                        $contentStr = "嗯?";
                        break;
                    default:
                        $contentStr = "干嘛";
                        break;
                }
            }else{
                $ran = rand(1, 4);
                switch ($ran) {
                    case 1:
                        $contentStr = "滚";
                        break;
                    case 2:
                        $fe = "/:,@o";
                        $contentStr = "我不想说话".$fe;
                        break;
                    case 3:
                        $contentStr = "不想理你";
                        break;
                    default:
                        $contentStr = "你唔明噶...";
                        break;
                }
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
                $contentStr = "FunnyBabyCat"."\n"."一只小猫=w="."\n"."请勿调戏233";
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
    *  天气查询函数
     */
    // 百度
    private function weather($city){
        include 'weather_cityId.php';
        $city_name = $weather_cityId[$city];
        $ch = curl_init();
        $url = 'http://apis.baidu.com/heweather/weather/free?city='.$city_name;
        $header = array(
            'apikey: 22e7fe8a7b368e1d2db1cb1b7db729fa',
        );
        // 添加apikey到header
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // 执行HTTP请求
        curl_setopt($ch , CURLOPT_URL , $url);
        $res = curl_exec($ch);
        //var_dump(json_decode($res));
        $data = json_decode($res); 
        // $contentStr = weather_info($data);
        return $data;
    }
    // 国家气象局
    // private function weather($n){
    //     include 'weather_cityId.php';
    //     $city_name = $weather_cityId[$n];
    //     if(!empty($city_name)){
    //         $json = file_get_contents("http://m.weather.com.cn/data/".$city_name.".html");
    //         return json_decode($json);
    //     }else{
    //         return null;
    //     }
    // }

    private function weather_info($data){
        $contentStr = "";
        $weatherinfo = $data->{'HeWeather data service 3.0'}[0];

        // 基本信息
        $basic = $weatherinfo->{'basic'};
        $contentStr .= $basic->{'city'}."\n";

        //7天天气
        $daily_forecast = $weatherinfo->{'daily_forecast'};

        // 建议
        $suggestion = $weatherinfo->{'suggestion'};

        for ($i=1; $i < 7; $i++) { 
        $weinfo = $daily_forecast[$i];
        $cond = $weinfo->{'cond'};
        $date = $weinfo->{'date'};
        $tmp = $weinfo->{'tmp'};
        $year = mb_substr($date, 0, 4, "UTF-8");
        $date = substr_replace($date, "", 0, 5);
            if($i == 1){
                $contentStr .= $date."\t".$cond->{'txt_d'}."\t".$tmp->{'min'}."-".$tmp->{'max'}."\n\n";
                $drsg = $suggestion->{'drsg'};
                $contentStr .= $drsg->{'brf'}.", ".$drsg->{'txt'}."\n\n";
            }else if($i == 6){
                $contentStr .= $date."\t".$cond->{'txt_d'}."\t".$tmp->{'min'}."-".$tmp->{'max'};
            }else{
                $contentStr .= $date."\t".$cond->{'txt_d'}."\t".$tmp->{'min'}."-".$tmp->{'max'}."\n";
            }
        }
        return $contentStr;
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