<?php
/**
 * Created by PhpStorm.
 * User: Ray
 * Date: 2016/12/29
 * Time: 下午8:45
 */

namespace channel;


class Controller_Mp extends \Fuel\Core\Controller_Rest
{
    //微信公众号
    private $account = false;

    /**
     * 获取微信服务器推送的数据
     *
     * @param $appid    微信公众号APPID或者ID
     * @return mixed
     */
    private function get_request($appid){

        //判断必要参数是否存在
        if(! \Input::get('signature', false)
            || ! \Input::get('timestamp', false)
            || ! \Input::get('nonce', false)){

            die($this->response(['status' => 'err', 'msg' => '非法请求', 'errcode' => 10], 404));
        }

        //获取微信公众号实体
        if(is_numeric($appid)){
            $this->account = \Model_MPAccount::find($appid);
        }else if(is_string($appid)){
            $this->account = \Model_MPAccount::find_one_by_app_id($appid);
        }

        //判断消息来源是否合法
        if(\Fuel::$env == \Fuel::PRODUCTION
            && ! $this->check_signature(\Input::get('signature'),
                \Input::get('timestamp'),
                \Input::get('nonce'),
                $this->account->token)){
            die($this->response(['status' => 'err', 'msg' => '非法消息来源', 'errcode' => 110], 404));
        }

        return \Input::xml();
    }

    /**
     * 验证消息是否合法
     *
     * @param $signature
     * @param $timestamp
     * @param $nonce
     * @param $token
     * @return bool
     */
    private function check_signature($signature, $timestamp, $nonce, $token) {
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        return $tmpStr == $signature;
    }

    /**
     * 微信服务器推送的普通消息处理
     *
     * @param $params
     */
    private function general_handle($params){
        $result = false;
        switch($params['MsgType']){
            case 'text':
                //写一个处理类
                $result = '可以返回具体动作：返回图文，图片，文字，语音，小视频等';
                $result = [
                    'ToUserName' => \Str::random('alnum', 16),
                    'FromUserName' => \Str::random('alnum', 16),
                    'CreateTime' => time(),
                    'MsgType' => 'Text',
                    'Content' => '什么呀',
                    'MsgId' => 0
                ];
                break;
            case 'image':
                $result = '可以返回具体动作：返回图文，图片，文字，语音，小视频等';
                break;
            case 'voice':
                $result = '可以返回具体动作：返回图文，图片，文字，语音，小视频等';
                break;
            case 'video':
                $result = '可以返回具体动作：返回图文，图片，文字，语音，小视频等';
                break;
            case 'shortvideo':
                $result = '可以返回具体动作：返回图文，图片，文字，语音，小视频等';
                break;
            case 'location':
                $result = '可以返回具体动作：返回图文，图片，文字，语音，小视频等';
                break;
            case 'link':
                $result = '可以返回具体动作：返回图文，图片，文字，语音，小视频等';
                break;
        }

        return $result;
    }

    /**
     * 微信服务器推送的事件消息处理
     *
     * @param $params
     */
    private function event_handle($params){
        $result = false;
        switch($params['Event']){
            case 'subscribe':
                //扫码关注时有此参数：$params['EventKey']
                //扫码关注时有此参数：$params['Ticket']

                //第一步：拉取用户详细信息（条件：必须是认证过的号）。
                $mpServer = new \handle\mp\RequestWechatServer();
                $userInfo = $mpServer->getUserInfo($this->account->access_token, $params['FromUserName']);
                //第二步：检测用户信息
                $wechat = \Model_Wechat::query()
                    ->where([])
                    ->get_one();
                if( ! $wechat){
                    \Model_WechatOpenId::forge($userInfo)->save();
                }else{
                    //第三步：创建用户信息（user、peoples、wechat、wechat_openid）
                    \Model_User::forge();
                    \Model_People::forge();
                    \Model_Wechat::forge();
                    \Model_WechtOpenID::forge();
                }

                //第四步：响应欢迎信息
                $result = '可以返回，关注时定义的消息体';
                break;
            case 'unsubscribe':
                //取消关注
                break;
            case 'SCAN':
                //$params['EventKey']
                //$params['Ticket']
                $result = '可以返回，不同方式关注时定义的消息体';
                break;
            case 'LOCATION':
                //$params['Latitude'] 纬度
                //$params['Longitude'] 经度
                //$params['Precision'] 地理位置精度
                //仅记录，不做其它操作
                break;
            case 'CLICK':
                //$params['EventKey']
                $result = '可以返回具体动作：返回图文，图片，文字，语音，小视频等';
                break;
            case 'VIEW':
                //$params['EventKey']
                break;
            case 'MASSSENDJOBFINISH':
                /**
                 * <xml>
                <ToUserName><![CDATA[gh_4d00ed8d6399]]></ToUserName>
                <FromUserName><![CDATA[oV5CrjpxgaGXNHIQigzNlgLTnwic]]></FromUserName>
                <CreateTime>1481013459</CreateTime>
                <MsgType><![CDATA[event]]></MsgType>
                <Event><![CDATA[MASSSENDJOBFINISH]]></Event>
                <MsgID>1000001625</MsgID>
                <Status><![CDATA[article is a reprint and user choose not to send]]></Status>
                <TotalCount>0</TotalCount>
                <FilterCount>0</FilterCount>
                <SentCount>0</SentCount>
                <ErrorCount>0</ErrorCount>
                <CopyrightCheckResult>
                <Count>2</Count>
                <ResultList>
                <item>
                <ArticleIdx>1</ArticleIdx>
                <UserDeclareState>0</UserDeclareState>
                <AuditState>2</AuditState>
                <OriginalArticleUrl><![CDATA[Url_1]]></OriginalArticleUrl>
                <OriginalArticleType>1</OriginalArticleType>
                <CanReprint>1</CanReprint>
                <NeedReplaceContent>1</NeedReplaceContent>
                <NeedShowReprintSource>1</NeedShowReprintSource>
                </item>
                <item>
                <ArticleIdx>2</ArticleIdx>
                <UserDeclareState>0</UserDeclareState>
                <AuditState>2</AuditState>
                <OriginalArticleUrl><![CDATA[Url_2]]></OriginalArticleUrl>
                <OriginalArticleType>1</OriginalArticleType>
                <CanReprint>1</CanReprint>
                <NeedReplaceContent>1</NeedReplaceContent>
                <NeedShowReprintSource>1</NeedShowReprintSource>
                </item>
                </ResultList>
                <CheckState>2</CheckState>
                </CopyrightCheckResult>
                </xml>
                 */
                # 请参考：https://mp.weixin.qq.com/wiki?action=doc&id=mp1481187827_i0l21&t=0.42532466699185933
                $result = '群发结束';
                break;
        }

        return $result;
    }

    /**
     * 业务程序最后的执行结果
     *
     * @param $result
     */
    private function response_handle($result){

        switch ($result->msg_type){
            case 'text':
                //'响应文字消息';
                break;
            case 'image':
                //'响应图片消息';
                break;
            case 'voice':
                //响应语音消息
                break;
            case 'video':
                //响应视频消息
                break;
            case 'music':
                //响应音乐消息
                break;
            case 'news':
                //响应图文消息
                break;
        }
    }


    /**
     * 接受微信服务器推送的消息
     *
     * @param bool $appid   微信公众号APPID或者实体ID
     */
    public function action_action($appid = false){

        if(! $appid){
            return $this->response_handle(['status' => 'err', 'msg' => '非法请求', 'errcode' => 2010], 404);
        }

        $params = $this->get_request($appid);

        if( ! $params){
            return $this->response_handle(['status' => 'err', 'msg' => '非法请求', 'errcode' => 2010], 404);
        }

        $result = $params['MsgType'] == 'event' ? $this->event_handle($params) : $this->general_handle($params);

        if( ! $result){
            die('SUCCESS');
        }

        //响应微信服务器端程序
        $this->response($result, 200);
    }

}