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

            //翻译
            $isTrans = mb_substr($keyword, 0, 2, "UTF-8");
            $transinfo = mb_strcut($keyword, 6, strlen($keyword) - 6, "UTF-8");
            if($str == '天气' && !empty($str_key)){
                $data = $this->weather($str_key);
                if($data != null){
                    $contentStr = $this->weather_info($data);    
                }else{
                    $face = "/::~";
                    $contentStr = $face."发生错误了.../::~";
                }                
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
            }else if( $isTrans == "翻译" ){
                $key = "1006358614";
                $keyfrom = "FunnyBabyCat";
                $url = "http://fanyi.youdao.com/openapi.do?keyfrom=".$keyfrom."&key=".$key."&type=data&doctype=json&version=1.1&q=".$transinfo;
                $trans = json_decode(file_get_contents($url));
                var_dump($trans);
                $errorCode = $trans->{"errorCode"};
                $contentStr = "";
                switch ($errorCode) {
                    case 0:
                        $contentStr = $trans->{"translation"}[0];
                        break;
                    case 20:
                        $contentStr = "要翻译的文本过长";
                        break;
                    case 30:
                        $contentStr = "无法进行有效的翻译";
                        break;
                    case 40:
                        $contentStr = "不支持的语言类型";
                        break;
                    case 50:
                        $contentStr = "无效的key";
                        break;
                    default:
                        $contentStr = '/:,@!出错了...';
                        break;
                }
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
            }else if(preg_match("^晚安^", $keyword)){
            	$contentStr = "晚安/:moon";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
            }else if ( strtolower($keyword) == "song") {
                include 'song.php';
                $num = count($arr)>10?10:count($arr);
                $template = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[%s]]></MsgType>
                    <ArticleCount>".$num."</ArticleCount>
                    <Articles>";
                for ($i = 0; $i < $num; $i++) { 
                    $v = $arr[$i];
                    $template .="<item>
                        <Title><![CDATA[".$v['title']."]]></Title> 
                        <Description><![CDATA[".$v['description']."]]></Description>
                        <PicUrl><![CDATA[".$v['picUrl']."]]></PicUrl>
                        <Url><![CDATA[".$v['url']."]]></Url>
                        </item>";
                }
                $template .="</Articles>
                            </xml> ";
                $resultStr = sprintf($template, $fromUsername, $toUsername, $time, 'news');
            } else if(preg_match("^最美的人|最漂亮的人^", $keyword)){ // 回复 最美的人
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
                    default:
                        $contentStr = "是白雪公主!";
                        break;
                }
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
            }else if(preg_match("^爱我^", $keyword)){
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
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
            }else if(preg_match("^嗨|你好|嘿^", $keyword)){
                $contentStr = "你好, 女神.";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
            }else if(preg_match("^男的|男人|帅哥^", $keyword)){
                $contentStr = "你好, 丑男.";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
            }else if(preg_match("^[\s\S]*?我[\s\S]*?(丑|不好看|不美|不漂亮)[\s\S]*?^", $keyword)) {
                $contentStr = "当然不, 每次我看着你的时候, 我都为你的飒爽英姿所倾倒. ";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
            }else if(preg_match("^[\s\S]*?我[\s\S]*?(美|好看|漂亮)[\s\S]*?^", $keyword)) {
                $ran = rand(1, 2);
                switch ($ran) {
                    case 1:
                        $contentStr = "我每次被人夸好看的时候, 都觉得那是因为我和你越来越像了. ";
                        break;
                    default:
                        $contentStr = "皎若太阳升朝霞, 灼若芙蕖出渌波. ";
                        break;
                }
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
            }else if(preg_match("^小猫|娘口|在吗|在干嘛^", $keyword)){
                $ran = rand(1, 2);
                switch ($ran) {
                    case 1:
                        $contentStr = "嗯?";
                        break;
                    default:
                        $contentStr = "干嘛";
                        break;
                }
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
            }else{
                $ran = rand(1, 4);
                switch ($ran) {
                    case 1:
                        $contentStr = "滚";
                        break;
                    case 2:
                        $contentStr = "我不想说话/:,@o";
                        break;
                    case 3:
                        $contentStr = "/:<@不想理你";
                        break;
                    default:
                        $contentStr = "你唔明噶...";
                        break;
                }
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
            }

            
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
        $cityid = $weather_cityId[$city];
        $cityid = substr_replace($cityid, "", 0, 2);
        $ch = curl_init();
        $url = 'http://apis.baidu.com/apistore/weatherservice/recentweathers?cityid='.$cityid;
        $header = array(
            'apikey: 22e7fe8a7b368e1d2db1cb1b7db729fa',
        );
        // 添加apikey到header
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // 执行HTTP请求
        curl_setopt($ch , CURLOPT_URL , $url);
        $res = curl_exec($ch);
        // var_dump(json_decode($res));
        $data = json_decode($res); 
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
        $retData = $data->{"retData"};

        $city = $retData->{"city"}; // 当前城市
        $today = $retData->{"today"}; // 今日的信息
        $today_date = $today->{"date"}; // 今天的日期
        $today_date = substr_replace($today_date, "", 0, 5); // 去掉年份
        $today_week = $today->{"week"}; // 今天周几
        $today_curTemp = $today->{"curTemp"}; // 现在的温度
        $today_hightemp = $today->{"hightemp"}; // 今日最高温
        $today_lowtemp = $today->{"lowtemp"}; // 今日最低温
        $today_type = $today->{"type"}; // 今日的天气状况
        $today_index = $today->{"index"}; // 今日指标
        $suggestion = $today_index[2]; // 穿衣建议
        
        $contentStr = $city."  当前温度: ".$today_curTemp."\n"; // 第一行: 城市 当前温度 
        $contentStr .= $today_date."  ".$today_week."  ".$today_type."\n".$today_lowtemp."-".$today_hightemp."\n"; // 第二行: 日期 周几 天气状况 温度范围
        $contentStr .= $suggestion->{"details"}."\n\n"; // 第三行 穿衣建议

        $forecast = $retData->{"forecast"}; // 未来预测
        foreach ($forecast as $f){
            $date = $f->{"date"};
            $date = substr_replace($date, "", 0, 5);
            $week = $f->{"week"};
            $hightemp = $f->{"hightemp"};
            $lowtemp = $f->{"lowtemp"};
            $type = $f->{"type"};
            $contentStr .= $date."  ".$week."  ".$type."\n".$lowtemp."-".$hightemp."\n";
        }
        $contentStr .= "\n/:8-)铲屎官可还满意";
        return $contentStr;
    }
    /*
    *  加密/校验流程：
    *   1. 将token、timestamp、nonce三个参数进行字典序排序
    *   2. 将三个参数字符串拼接成一个字符串进行sha1加密
    *   3. 开发者获得加密后的字符串可与signature对比，标识该请求来源于微信
     */
    private function checkSignature(){
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