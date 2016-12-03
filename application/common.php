<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件


/**
 * 系统非常规MD5加密方法
 * @param  string $str 要加密的字符串
 * @return string
 */
function think_ucenter_md5($str, $key = 'ThinkUCenter'){
    return '' === $str ? '' : md5(sha1($str) . $key);
}


/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
 * @return mixed
 */
function get_client_ip($type = 0,$adv=false) {
    $type       =  $type ? 1 : 0;
    static $ip  =   NULL;
    if ($ip !== NULL) return $ip[$type];
    if($adv){
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos    =   array_search('unknown',$arr);
            if(false !== $pos) unset($arr[$pos]);
            $ip     =   trim($arr[0]);
        }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip     =   $_SERVER['HTTP_CLIENT_IP'];
        }elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip     =   $_SERVER['REMOTE_ADDR'];
        }
    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip     =   $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u",ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

/**
 * 时间戳格式化
 *
 * @param int $time
 * @return string 完整的时间显示
 * @author huajie <banhuajie@163.com>
 */
function time_format($time = NULL, $format = 'Y-m-d H:i')
{
    $time = $time === NULL ? time() : intval($time);
    return date($format, $time);
}


function analy_coupon_rule($rule){

    $regex = '/(\d+)([y,m,w,d,h,M,s])\/(\d+)/';
    $matches = array();

    if(preg_match($regex, $rule, $matches)){

        $ctime=$matches[1];
        $curr_date=date('Y-n-j H:i:s');
        if($matches[2]=='y'){
            $y=date('Y');
            $data['starttime']=strtotime($y."-01-01");

            $y=$y+$ctime-1;
            $data['endtime']=strtotime($y."-12-31 23:59:59");
        }
        else if($matches[2]=='m'){
            $BeginDate=date('Y-m-01', strtotime(date("Y-m-d")));
            $data['starttime']=strtotime($BeginDate);
            $data['endtime']=strtotime("$BeginDate +$ctime month -1 day"."23:59:59");
        }
        else if($matches[2]=='w'){
            $date=new \DateTime();
            $date->modify('this week');

            $data['starttime']=$first_day_of_week=strtotime($date->format('Y-m-d'));
            $date->modify('this week +'.($ctime*6).' days');
            $data['endtime']=$end_day_of_week=strtotime($date->format('Y-m-d').'23:59:59');
        }
        else if($matches[2]=='d'){

            $data['starttime']=strtotime(date('Y-m-d 00:00:00'));

            $BeginDate=date('Y-m-d');
            $ctime=$ctime-1;
            $data['endtime']=strtotime("$BeginDate +$ctime day"."23:59:59");
        }
        $data['count']=$matches[3];

        return $data;
    }
    else{
        return false;
    }
}



function add_user_score($uid,$score_type)
{
    $year = date("Y");
    $month = date("m");
    $day = date("d");
    $dayBegin = mktime(0,0,0,$month,$day,$year);//当天开始时间戳
    $dayEnd = mktime(23,59,59,$month,$day,$year);//当天结束时间戳

    $actionLog=new \app\index\model\ActionLog();
    $sumScore=$actionLog->where(array('user_id'=>$uid,'create_time'=>array('between',array($dayBegin,$dayEnd))))->sum('score');


    if($sumScore>=30){
        //今天总分已达到30分。
        return false;
    }

    //获取会员信息
    $userModel= new \app\index\model\Member();
    $user=$userModel->where(array('uid'=>$uid))->field('uid,nickname,mobile,score,group_id')->find();
    if(!$user)
        return false;

    //获取会员用户信息
    $userGroupModel= new \app\index\model\UserGroup();
    $userGroup=$userGroupModel->where(array('status'=>1))->select();

    $currentUserGroupIndex=0;
    for ($i = 0; $i < count($userGroup); $i++){
        if($userGroup[$i]['id']==$user['group_id']);
        {
            $currentUserGroupIndex=$i;
            break;
        }
    }

    $type_group=array(
        'share_web'=>array('邀请新用户成为会员',array(3,5,10)),
        'in_activity'=>array('参加福利活动',array(2,5,10)),
        'get_fuli'=>array('领取福利优惠',array(2,5,10)),
        'user_sign'=>array('每日签到',array(1,2,3)),
        'edit_profile'=>array('完善个人信息',array(5,5,10)),
    );

    $userdata['score']=$user['score']+$type_group[$score_type][1][$currentUserGroupIndex];


    //判断是否达到升级条件，达到就修改用户组
    $userdata['group_id']=$user['group_id'];
    foreach ($userGroup as $val){
        if($userdata['score']<=$val['creditshigher']&&$userdata['score']>=$val['creditslower']){
            $userdata['group_id']=$val['id'];
            break;
        }
    }

    $userModel->where(array('uid'=>$uid))->update($userdata);

    //添加行为日志
    $data['action_id'] = 14;
    $data['user_id'] = $uid;
    $data['action_ip'] = ip2long(get_client_ip());
    $data['model'] = 'member';
    $data['record_id'] = $uid;
    $data['score'] = $type_group[$score_type][1][$currentUserGroupIndex];
    $data['remark'] = '用户：'.$user['mobile'].'，在'.time_format().'，通过"'.$type_group[$score_type][0].'"操作，获得积分'.$type_group[$score_type][1][$currentUserGroupIndex].'分';
    $data['remark'] =$data['remark'] .'，该操作变更前积分为：'.$user['score'].'，变更后为：'.$userdata['score'];
    $data['create_time'] = time();
    $actionLog->insert($data);


}


//发送验证码
function couponmsg($mobile,$coupon = '',$url='')
{

    if (empty($mobile)) {
        echo '{"result":2}';
    }

    $data = array(
        'serviceid' => '20160614001',
        'passwd' => '0A5ABFEA09',
        'sendtarget' => $mobile,
        'smcontent' => "恭喜尾号(".substr($mobile,7).")的用户已成功领取 ".$coupon." 福利优惠券。请您通过此链接使用 ".$url."。感谢您对校园生活圈的关注，如有疑问请咨询官方客服QQ：1120500326。",
        'sign' => '101'
    );

    $result = http_post_data('http://118.145.4.108:8085/sms', json_encode($data));

}

//发送验证码
function verifymsg($mobile = '')
{

    if ($mobile=='') {
        echo '{"result":2,message:"请输入手机号码！"}';
        exit();
    }
    $code = make_rand();

    cache('verify_code'.$mobile,array('mobile'=>$mobile,'code'=>$code),300);
//    session('verify_code', array('mobile'=>$mobile,'code'=>$code));
    $data = array(
        'serviceid' => '20160614001',
        'passwd' => '0A5ABFEA09',
        'sendtarget' => $mobile,
        'smcontent' => "您的验证码为：$code 。请勿将验证码泄露给其他人，如非本人操作，请忽略。",
        'sign' => '101'
    );

    $result = http_post_data('http://118.145.4.108:8085/sms', json_encode($data));
    return $result;
}

// 连续创建目录
function makeDir($path, $mode = "0777") {
    if (!$path) return false;

    if(!file_exists($path)) {
        return mkdir($path,$mode,true);
    } else {
        return true;
    }
}

/**
 * 实例化阿里云oos
 * @return object 实例化得到的对象
 */
function new_oss(){
    vendor('Alioss.autoload');
    $config=config('ALIOSS_CONFIG');
    $oss=new \OSS\OssClient($config['KEY_ID'],$config['KEY_SECRET'],$config['END_POINT']);
    return $oss;
}

/**
 * 上传文件到oss并删除本地文件
 * @param  string $path 文件路径
 * @return bollear      是否上传
 */
function oss_upload($path){
    // 获取配置项
    $bucket=config('ALIOSS_CONFIG.BUCKET');
    // 先统一去除左侧的.或者/ 再添加./
    $oss_path=ltrim($path,'./');
    $path='./'.$oss_path;
    if (file_exists($path)) {
        // 实例化oss类
        $oss=new_oss();
        // 上传到oss
        $oss->uploadFile($bucket,$oss_path,$path);
        // 如需上传到oss后 自动删除本地的文件 则删除下面的注释
        // unlink($path);
        return true;
    }
    return false;
}

function make_rand($length = "6")
{
    $str = "0123456789";
    $result = "";
    for ($i = 0; $i < $length; $i ++) {
        $num[$i] = rand(0, 9);
        $result .= $str[$num[$i]];
    }
    return $result;
}

function http_post_data($url, $data_string)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charset=utf-8',
        'Content-Length: ' . strlen($data_string)
    ));
    ob_start();
    curl_exec($ch);
    $return_content = ob_get_contents();
    ob_end_clean();

    $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    return array(
        $return_code,
        $return_content
    );
}