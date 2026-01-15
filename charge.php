<?php

//header("Content-Type:text/html;charset=utf-8");

// require('./functions.php');

//2016-03-25 新增fttps
function curl_get_https($url, $data=array(), $header=array(), $timeout=30){ //123
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查 
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在 
	curl_setopt($ch, CURLOPT_URL, $url); 
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); 

	$response = curl_exec($ch); 

	if($error=curl_error($ch)){ 
		die($error); 
	} 

	curl_close($ch); 

	return $response; 
}

public function curl_get_https_json($url, $data=array(), $header=array(), $timeout=30){ 
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在 
    curl_setopt($ch, CURLOPT_URL, $url); 
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
    curl_setopt($ch, CURLOPT_POST, true); 
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // Encode data as JSON
    
    // Specify that we're sending JSON
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen(json_encode($data))
    ));
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); 

    $response = curl_exec($ch); 

    if($error = curl_error($ch)){ 
        die($error); 
    } 

    curl_close($ch); 

    return $response; 
} 


function newcurl_get_https($url, $data=array(), $header=array(), $timeout=30){ 
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查 
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true); // 从证书中检查SSL加密算法是否存在 
	curl_setopt($ch, CURLOPT_URL, $url); 
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header); 
	curl_setopt($ch, CURLOPT_POST, true); 
	curl_setopt($ch, CURLOPT_POSTFIELDS,$data); 
	var_dump(http_build_query($data));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); 

	$response = curl_exec($ch); 

	if($error=curl_error($ch)){ 
		die($error); 
	} 

	curl_close($ch); 

	return $response; 
}


function newcurl_post($url, $data) {
	$request_headers=array();
	$request_headers[]='Content-Type:application/json; charset=UTF-8';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers); 
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	$res = curl_exec($ch);
	curl_close($ch);
	return $res;
}

function curl_get($url){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	$output = curl_exec($ch);
	curl_close($ch);
	return $output;
}

function curl_post($url, $data) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	$res = curl_exec($ch);
	curl_close($ch);
	return $res;
}

//2017-02-12 新增得力流量
function deli($arr,$tid){
	if(!is_array($arr)) {
		return false;
	}
	$account = $arr['account'];//用户名
	$password = $arr['password'];//充值平台的密码
	$key = $arr['key'];//密钥
	
	$flowCode=$arr['flowCode'];  // 产品编码
	$mobile = $arr['mobile'];//手机号
	$orderNumber = $tid;//  商户订单号
	$callbackURL=UrlEncode('http://yuanp.517.lv/deli_url.php');
	
	$sign=strtoupper(md5(md5($password).$mobile.$flowCode.$orderNumber.$key));
	
	$url = "http://119.147.44.148/index.php/Api/Order/flow?account=$account&sign=$sign&flowCode=$flowCode&mobile=$mobile&orderNumber=$orderNumber&callbackURL=$callbackURL";
	
	global $mysql;
	$where['tid']=$tid;
	$value=$mysql->table('trades')->where($where)->find();
	$data2['submit_info']=$value['submit_info'].'||'.$url;
	$data2['submit_time']=date('Y-m-d H:i:s',time());
	$mysql->table('trades')->where($where)->update($data2); 
	
	
	$res=curl_get($url);
	
	
	$data3['return_info']=$value['return_info'].'||'.$res;
	$mysql->table('trades')->where($where)->update($data3); 
	
	$newres=json_decode($res);var_dump($newres);
	if($newres->code=='2000'){
		$return = '提交成功';
	}else{
		$return = $newres->msg;	
	}
	return $return; 
}

//2017-02-12 新增点道
function diandao($arr,$tid){
	if(!is_array($arr)) {
		return false;
	}
	
	$flow=explode('_',$arr['flow']);
	if(empty($flow[1])){
		$res='编码错误';
		return $res;die();
	}
	
	$apikey='6c6ee58eb3a649929a07de3fd6977a72'; 
	$req['Action']='charge';//命令
	$req['V']='1.1';//版本号
	$req['Range']=$flow[0];//流量类型  0 全国流量 1省内流量，不带改参数时默认为0
	$req['OutTradeNo']=$tid;//订单号
	$req['Account']=$arr['Account']; //帐号
	$req['Mobile']=$arr['Mobile'];
	$req['Package']=$flow[1]; //流量包大小
	$req['Sign']=strtolower(md5("account={$req['Account']}&mobile={$req['Mobile']}&package={$req['Package']}&key={$apikey}")); //签名
	$url="http://106.14.32.232:8080/api.aspx";
	
	global $mysql;
	$where['tid']=$tid;
	$value=$mysql->table('trades')->where($where)->find();
	
	$data=json_encode($req);
	
	$data2['submit_info']=$value['submit_info'].'||'.$data;
	$where['tid']=$tid;
	$data2['submit_time']=date('Y-m-d H:i:s',time());
	$res=$mysql->table('trades')->where($where)->update($data2); 
	
	
	//echo $data.'<br/>';
	$result = curl_post($url,$req);
	$resultCode = json_decode($result);
    
	$resultCode2=json_encode($resultCode);
	$data3['return_info']=$value['return_info'].'||'.$resultCode2;
	$data3['return_tid']=$resultCode->transCode;
	$mysql->table('trades')->where($where)->update($data3); 
	 
	if($resultCode->Code=='0')
	{
	   return '提交成功';
	}
	else
	{
		return $resultCode->Message;
	}
	
}

//  新增 恒焱科技  2017-01-21
function hengyan($arr,$tid){
	if(!is_array($arr)) {
		return false;
	}

	$flow=$arr['flow'];
	$f=explode('_',$flow);
	if(empty($f[1])){
		echo '编码错误';die();
	}
	
	$userid=3;     // 平台ID
	$key='cqyp'; 
	$num=1;  // 数量
	$orderno=$tid;  // 订单号
	$dockcode=$f[1];   // 对接编码
	$accountno=$arr['accountno'];   // 充值手机号
	$flowtype=$f[0];   // af全国 sf 省网
	
	$sign=strtolower(md5($userid.$num.$orderno.$dockcode.$accountno.$key));  //加密串
	
    	
	$url="http://106.14.17.151:8123/Flowbuy.ashx?userid=$userid&num=$num&orderno=$orderno&dockcode=$dockcode&accountno=$accountno&flowtype=$flowtype&sign=$sign";
	
	global $mysql;
	$where['tid']=$tid;
	$value=$mysql->table('trades')->where($where)->find();
	$data2['submit_info']=$value['submit_info'].'||'.$url;
	$data2['submit_time']=date('Y-m-d H:i:s',time());
	$mysql->table('trades')->where($where)->update($data2); 
	
	
	$res=curl_get($url);
	
	
	$data3['return_info']=$value['return_info'].'||'.$res;
	$mysql->table('trades')->where($where)->update($data3); 
	
	$newres=explode('|',$res);
	if($newres[0]=='0'){
		$return = '提交成功';
	}else{
		$return = $newres[1];	
	}
	return $return; 
}  

//  新增 金豌豆  2017-01-21
function jwd($arr,$tid){
	$flow=explode('_',$arr['flow']);
	if(empty($flow[2])){
		$res='编码错误';
		return $res;die();
	}
	$identity=time();
	$timestamp=date('YmdHis',time());
	$appsecret='5JREdlDZ245zQIqyz5jzNJP8gI7HRa9q';
	$user=$arr['user'];
	$scope=$flow[0];
	$orderno='ypcq'.$tid;
	$mobile=$arr['mobile'];
	$activeflag=0; 
	$expiration=1;
	$fluxid=(int)$flow[1];
	$fluxnum=(int)$flow[2];
	$backurl='http://yuanp.517.lv/jwd_url.php';
	$sign=md5($timestamp.$user.$appsecret.$scope.$orderno.$mobile.$activeflag.$expiration.$fluxnum);
	$header['timestamp']=$timestamp;
	$header['identity']=$identity;
	$header['sign']=$sign;
	$data['user']=$user;
	$data['scope']=(int)$scope; 
	$data['orderno']=$orderno;
	$data['mobile']=$mobile;
	$data['activeflag']=$activeflag;
	$data['expiration']=$expiration;
	$data['fluxid']=$fluxid;
	$data['fluxnum']=$fluxnum;
	$data['backurl']=$backurl;
	
	$newdata['header']=$header;
	$newdata['payload']['data']=$data; 
	
	//$url='http://139.224.59.94/llfxtest/api.php/ytauth/order?appid=@k75C2BcWr4hOwzWJO4BhyFDROxK0U0m&access_token=OFKqQUWJYaMrlxlwz9XH@KkDFdq4yBYw';
	$url='http://59.110.51.81/llfx/api.php/ytauth/order?appid=@k75C2BcWr4hOwzWJO4BhyFDROxK0U0m&access_token=OFKqQUWJYaMrlxlwz9XH@KkDFdq4yBYw';

	global $mysql;
	$where['tid']=$tid;
	$value=$mysql->table('trades')->where($where)->find();
	$data2['submit_info']=$value['submit_info'].'||'.$url;
	$data2['submit_time']=date('Y-m-d H:i:s',time());
	$mysql->table('trades')->where($where)->update($data2); 
	 
	 
	$res=newcurl_post($url, json_encode($newdata));
	
	$data3['return_info']=$value['return_info'].'||'.$res;
	$mysql->table('trades')->where($where)->update($data3); 
	
	$newres=json_decode($res);
	
	if($newres->header->errcode=='0'){ 
		$return = '提交成功';
	}else{
		$return = $newres->header->errmsg;	
	}
	return $return;
	
	
}


function ziyouchong($arr,$tid){
	$flow=explode('_',$arr['flow']);
	if(empty($flow[1])){
		$res='编码错误';
		return $res;die();
	}
	
	
	
	$phones=$arr['phones'];
	//$flow=$arr['flow'];
	$requst='https://tcc.taobao.com/cc/json/mobile_tel_segment.htm?tel='.$phones;
	$resultCode = iconv("GB2312","UTF-8",curl_get_https($requst));
	if(strpos($resultCode,'中国移动')){
		$data['mobilePackageSize']=$flow[0];
	}elseif(strpos($resultCode,'中国联通')){
		$data['unicomPackageSize']=$flow[0];
	}elseif(strpos($resultCode,'中国电信')){
		$data['telecomPackageSize']=$flow[0];
	}else{
		$return = '未能识别手机号运营商';
		return $return;die;
	}
	
	
	/*$phones=$arr['phones'];
	//$flow=$arr['flow'];
	$requst='https://tcc.taobao.com/cc/json/mobile_tel_segment.htm?tel='.$phones;
	$resultCode = iconv("GB2312","UTF-8",curl_get_https($requst));*/
	
	
	//2016-09-20 新增
/*	$phones=$arr['phones'];
	//$flow=$arr['flow'];
	$requst='https://tcc.taobao.com/cc/json/mobile_tel_segment.htm?tel='.$phones;
	$resultCode = iconv("GB2312","UTF-8",curl_get_https($requst));
	
	$ch = curl_init();
	$url = 'http://apis.baidu.com/chazhao/mobilesearch/phonesearch?phone='.$phones;
	$header = array(
		'apikey: 036e9f92dfc71772e7b11c62546a3820',
	);
	// 添加apikey到header
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	// 执行HTTP请求
	curl_setopt($ch, CURLOPT_URL, $url);
	$resultCode = curl_exec($ch);
	
	//var_dump($resultCode);die;
	
	
	
	
	
	
	if(strpos($resultCode,'移动')){
		$data['mobilePackageSize']=$flow[0];
	}elseif(strpos($resultCode,'联通')){
		$data['unicomPackageSize']=$flow[0];
	}elseif(strpos($resultCode,'电信')){
		$data['telecomPackageSize']=$flow[0];
	}else{
		$return = '未能识别手机号运营商';
		return $return;die;
	}
	*/
	$appSecret='7a606c99b3084642b79447f2b18b1957';
	$data['appKey']=$arr['appKey'];
	$data['random']=(string)rand('1000000000','9999999999');
	//echo $arr['appKey'].$data['random'].$appSecret;
	$data['sign']=md5($arr['appKey'].$data['random'].$appSecret);
	$data['phones'][]=$phones;
	$data['cpBatchId']=$arr['requestNo'];
	$data['requestNo']=$arr['requestNo'];
	$data['productCode']=$flow[1];
	//var_dump(json_encode($data));
	
	$url='http://llaccess.izton.com/inter1.3/dRechargeReceiverPhone';
	
	global $mysql;
	$where['tid']=$tid;
	$value=$mysql->table('trades')->where($where)->find();
	$data2['submit_info']=$value['submit_info'].'||'.json_encode($data);
	$data2['submit_time']=date('Y-m-d H:i:s',time());
	$mysql->table('trades')->where($where)->update($data2); 
	
	
	$res=curl_post($url,json_encode($data));
	
	//var_dump($res);
	$data3['return_info']=$value['return_info'].'||'.$res;
	$mysql->table('trades')->where($where)->update($data3); 
	
	
	$newres=json_decode($res);
	if($newres->code=='00000'){
		$return = '提交成功';
	}else{
		$return = $newres->msg;	
	}
	return $return;
}

/* 2017-01-13  新增南京爱尔贝/K  */ 
function aierbei($arr,$tid){ 
	if(!is_array($arr)) {
		return false;
	}
	//newliuliang(array('phoneCode'=>'15314629063','agentCode'=>'110007','supplier'=>'3','flow'=>'1_30M'),111111);
	$flow=$arr['flow'];
	$f=explode('_',$flow);
	if(empty($f[1])){
		echo '编码错误';die();
	}
	$data['phoneCode']=$arr['phoneCode'];
	$data['agentCode']=$arr['agentCode'];
	$data['supplier']=$arr['supplier'];
	$data['parValue']=$f[1];
	$data['dataType']=$f[0];
	$data['nonceStr']=md5(time().mt_rand(0,1000));
	$newatr="phoneCode=".$data['phoneCode']."&agentCode=".$data['agentCode']."&supplier=".$data['supplier']."&parValue=".$data['parValue']."&dataType=".$data['dataType']."&nonceStr=".$data['nonceStr']."&checkKey=12D9A1415A98471BA9D097B59F10A522";
	$data['checkCode']=strtoupper(md5($newatr));

	//$url='http://139.224.34.42:8080/ERecharge/recharge/forFlow';
	$url='http://139.224.34.42:8080/ERecharge/recharge/forFlow?phoneCode='.$data['phoneCode'].'&agentCode='.$data['agentCode'].'&supplier='.$data['supplier'].'&parValue='.$data['parValue'].'&dataType='.$data['dataType'].'&nonceStr='.$data['nonceStr'].'&businessNo='.$tid.'&checkCode='.$data['checkCode'];

	global $mysql;
	$where['tid']=$tid;
	$value=$mysql->table('trades')->where($where)->find();
	
	//$dat=json_encode($data);
	
	$data2['submit_info']=$value['submit_info'].'||'.$url;
	//$where['tid']=$tid;
	$mysql->table('trades')->where($where)->update($data2); 

	$res=curl_get($url); 

	$data3['return_info']=$value['return_info'].'||'.$res;
	//$where['tid']=$tid;
	$mysql->table('trades')->where($where)->update($data3);

	$newres=json_decode($res);

	if(!empty($newres->returnCode)){
		if($newres->returnCode=='0000'){
			$result='提交成功';
		}else{
			$result=$newres->returnMsg;
		}
	}else{
		$result='未知错误';
	} 
	return $result;
}


//2017-01-12 新增威合伦
function weihelun($arr,$tid){
	$username=$arr['username'];  //用户账号
	$apikey=$arr['apikey'];     
	$number=$arr['number'];      //手机号码
	$flowsize=$arr['flowsize'];  // 充值流量大小 5m参数为5,10m参数为10,1g参数为1024,2g参数为2048以此类推
	$scope=$arr['scope'];        //充值流量范围（0 全国流量，1 省内流量）
	$effecttime=0;               // 生效日期（0 即时生效，1次月生效）
	$user_order_id=$tid;   //商户订单号，可选参数，非空则判断唯一性，最多支持40位的字符串
	$method='GET';     //回调方式，默认POST。如果需要使用GET方式:method=GET
	$reporturl='http://yuanp.517.lv/weihelun_url.php';  //回调地址，可选参数，填则回调，不填则不回调（设置回调地址有两种方式:1.接口传入回调地址 2.后台配置固定回调地址。如果同时在接口传入回调地址和后台配置了固定回调地址，那么优先选择接口传入的回调地址）
	
	$sign=strtoupper(md5('username='.$username.'&apikey='.$apikey));
	
	$url="http://139.196.58.196:32001/api/v1/sendOrder?username=$username&number=$number&flowsize=$flowsize&scope=$scope&user_order_id=$user_order_id&method=$method&reporturl=$reporturl&sign=$sign";
	
	global $mysql;
	$where['tid']=$tid;
	$value=$mysql->table('trades')->where($where)->find();
	$data2['submit_info']=$value['submit_info'].'||'.$url;
	$data2['submit_time']=date('Y-m-d H:i:s',time());
	$mysql->table('trades')->where($where)->update($data2); 
	
	
	$res=curl_get($url);
	
	$data3['return_info']=$value['return_info'].'||'.$res;
	$mysql->table('trades')->where($where)->update($data3); 
	
	$newres=json_decode($res);
	if($newres->errcode=='0'){
		$return = '提交成功';
	}else{
		$return = $newres->errmsg;	
	}
	return $return;
}

//2016-09-07 新增迪为流量
function diwei($arr,$tid){
	$tel=$arr['tel'];
	$uid=$arr['uid'];
	$ts=time();
	$pid=$arr['pid'];
	$oid=$arr['oid'];
	$price=$arr['price'];
	$safeCode='17c083e9313f086f';
	$sign=md5($tel.$uid.$ts.$safeCode);
	
	$url="http://ll.deovo.com/index.php/Api/Products/order?tel=$tel&uid=$uid&ts=$ts&sign=$sign&pid=$pid&oid=$oid&price=$price";
	
	
	global $mysql;
	$where['tid']=$tid;
	$value=$mysql->table('trades')->where($where)->find();
	$data2['submit_info']=$value['submit_info'].'||'.$url;
	$data2['submit_time']=date('Y-m-d H:i:s',time());
	$mysql->table('trades')->where($where)->update($data2); 
	
	
	$res=curl_get($url);
	
	$data3['return_info']=$value['return_info'].'||'.$res;
	$mysql->table('trades')->where($where)->update($data3); 
	
	$newres=json_decode($res);
	if($newres->status=='1' || $newres->status=='4'){
		$return = '提交成功';
	}else{
		$return = $newres->msg;	
	}
	return $return;
}


/*//2016-08-27 新增自由充
function ziyouchong($arr,$tid){
	$flow=explode('_',$arr['flow']);
	if(empty($flow[1])){
		$res='编码错误';
		return $res;die();
	}
	
	$phones=$arr['phones'];
	//$flow=$arr['flow'];
	$requst='https://tcc.taobao.com/cc/json/mobile_tel_segment.htm?tel='.$phones;
	$resultCode = iconv("GB2312","UTF-8",curl_get_https($requst));
	if(strpos($resultCode,'中国移动')){
		$data['mobilePackageSize']=$flow[0];
	}elseif(strpos($resultCode,'中国联通')){
		$data['unicomPackageSize']=$flow[0];
	}elseif(strpos($resultCode,'中国电信')){
		$data['telecomPackageSize']=$flow[0];
	}else{
		$return = '未能识别手机号运营商';
		return $return;die;
	}
	
	$appSecret='9a7ddda645224904ae885b6dd81e5781';
	$data['appKey']=$arr['appKey'];
	$data['random']=(string)rand('1000000000','9999999999');
	//echo $arr['appKey'].$data['random'].$appSecret;
	$data['sign']=md5($arr['appKey'].$data['random'].$appSecret);
	$data['phones'][]=$phones;
	$data['cpBatchId']=$arr['requestNo'];
	$data['requestNo']=$arr['requestNo'];
	$data['productCode']=$flow[1];
	//var_dump(json_encode($data));
	
	$url='http://llaccess.izton.com/inter1.3/dRechargeReceiverPhone';
	
	global $mysql;
	$where['tid']=$tid;
	$value=$mysql->table('trades')->where($where)->find();
	$data2['submit_info']=$value['submit_info'].'||'.json_encode($data);
	$data2['submit_time']=date('Y-m-d H:i:s',time());
	$mysql->table('trades')->where($where)->update($data2); 
	
	
	$res=curl_post($url,json_encode($data));
	
	//var_dump($res);
	$data3['return_info']=$value['return_info'].'||'.$res;
	$mysql->table('trades')->where($where)->update($data3); 
	
	
	$newres=json_decode($res);
	if($newres->code=='00000'){
		$return = '提交成功';
	}else{
		$return = $newres->msg;	
	}
	return $return;
}*/



//2016-05-26 新增德佳流量
	function deyi($arr,$tid){
		//拦截
		if(!is_array($arr)){
			return false;
		}
		$apikey='iCCJ1m2cM2ZEeXIaEeKKNzDvZBsSXwgE';
		$resp['account']  	= $arr['account'];//账号
		$resp['action']   	= "Query";		//操作值 //"Query";//
		$resp['orderID'] 	= $arr['orderID'];//手机号
		$resp['timeStamp']	= $arr['timeStamp'];//时间
		$resp['sign']= md5($sign_str);
		
		$resp_url 	= "http://121.41.8.25:8081/Query.php";//查询
		$phone 		= $arr['phone'];//手机号
		$channelid 	= $arr['channelid'];//平台分配渠道
		$productid 	= $arr['productid'];//订购产品包编号
		$timestamp	= $arr['timestamp'];//时间戳
		//请求签名 $data['sign']
		//$sign_str = "{$arr['apiKey']}account={$resp['account']}&action=Charge&phone={$resp['phone']}&range={$resp['range']}&size={$resp['size']}&timeStamp={$resp['timeStamp']}{$arr['apiKey']}";
		//签名
		#$sign = MD5($channelid.$productid."phones".$timestamp.md5(md5($apikey)));
		
		
		$sign = md5($channelid.$productid.$phone.$timestamp.md5(md5($apikey)));
		//参数
		//post测试
		$newdata['channelid']=$channelid;
		$newdata['productid']=$productid;
		$newdata['phone']=$phone;
		$newdata['timestamp']=$timestamp;
		$newdata['sign']=$sign;
		$url='http://admin.xll.sfssh.com/adapter/flowrecharge';
		
		global $mysql;
		$where['tid']=$tid;
		$value=$mysql->table('trades')->where($where)->find();
		
		//$dat=json_encode($data);
		
		$data2['submit_info']=$value['submit_info'].'||'.json_encode($newdata);
		$data2['submit_time']=date('Y-m-d H:i:s',time());
		//$where['tid']=$tid;
		$res=$mysql->table('trades')->where($where)->update($data2); 
		
		$result =  curl_post($url,$newdata);
		
		$newresult=json_decode($result, true);
		
		$data3['return_info']=$value['return_info'].'||'.$result;
		$data3['return_tid']=$newresult['flowrecord'];
		//$where['tid']=$tid;
		$res=$mysql->table('trades')->where($where)->update($data3); 
			//var_dump($result);die();
			if(empty($result)){
				$newresult['msg'] = '提交超时';
			}
		return $newresult['msg'];
	}

function chargeWithPlatformMiaoChong($arr,$tid) {

	if(!is_array($arr)) {
		return false;
	}

	$name = $arr['name'];//充值平台的账号
	$password = $arr['password'];//充值平台的密码
	$method = $arr['method'];//insertorder下单
	$types = $arr['types'];//产品编号
	$mobile = $arr['mobile'];//充值号码
	$order = $arr['order'];//唯一订单号(不能低于12位)

	$requst = "http://112.74.93.118/api.php?name=$name&password=$password&method=$method&types=$types&mobile=$mobile&order=$order";
 //var_dump($requst);
	 global $mysql;
	$where['tid']=$tid;
	$value=$mysql->table('trades')->where($where)->find();
	
	//$dat=json_encode($data);
	
	$data2['submit_info']=$value['submit_info'].'||'.$requst;
	$data2['submit_time']=date('Y-m-d H:i:s',time());
	//$where['tid']=$tid;
	$res=$mysql->table('trades')->where($where)->update($data2); 
 
	$resultCode = curl_get($requst);
	
	$data3['return_info']=$value['return_info'].'||'.$resultCode;
	//$where['tid']=$tid;
	$res=$mysql->table('trades')->where($where)->update($data3); 

	$resultCode = substr($resultCode, strlen($resultCode) - 1);

	$resultList = array(
		0 => '提交成功',
		1 => '提交失败',
		2 => '余额不足',
		3 => '非法操作',
		4 => '号码有误',
		5 => '账号错误',
		6 => '重复提交',
		7 => '账号限制'
	);

	return $resultList[$resultCode];
}

//2016-04-25  新增易赛平台查询
function yisai_find($order){
	$key='ef96e88ed176a48a6b55d1cc1eef088a';
	$data['UserNumber']='8755227';
	$data['OutOrderNumber']=$order;
	$data['RecordKey']=substr(md5($data['UserNumber'].$order.$key),0,16); 
	//md5($data['UserNumber'].$order.$key,16);
	$url = "http://llbchongzhi.esaipai.com/IRechargeResult_Flow";
	$result = curl_post($url, $data);
	//var_dump($data);die();
	$res=xml_to_array($result);
	//var_dump($res["Esaipay"]['Result']);die();
	if($res["Esaipay"]['Result']=='success'){
		$newres=$res["Esaipay"]["OrderResult"];
	}else{
		$newres='查询失败';
	}
	return $newres;
}
//2016-04-30 新增桌一平台查询
function zhuoyi_find($order){
	$customer='gzslskj';
	$timestamp=getMillisecond();
	$customerOrderId=$order;
	$token='5487361589';
	$userKey=md5($customer.$token.$timestamp);
	$url="http://120.26.78.209/nettraffic/api/transaction?customer=$customer&timestamp=$timestamp&customerOrderId=$customerOrderId&userKey=$userKey";
	//echo $url;
	$result = json_decode(curl_get($url));
	if($result->resultMsg=='订单处理中'){
		$result->resultMsg='充值进行中';
	}
	//var_dump($result->resultMsg);die();
	return $result->resultMsg;
	
}

//新增优派流量

	function youpai($arr,$tid){
		if(!is_array($arr)) {
			return false;
		}
		//var_dump($arr);die();
		$flow=explode('_',$arr['flow']);
		if(empty($flow[1])){
			$res='编码错误';
			return $res;die();
		}
		$Account=$arr["Account"];
		$Password='123789';
		$OutTradeNo=$arr["OutTradeNo"];	
		$Mobile=$arr["Mobile"];
		$Range=$flow[0];
		$Package=$flow[1];
		$Action='charge';
		
		$url='http://101.200.142.119:8080/api.aspx?v=1.0&action=charge&account='.$Account.'&mobile='.$Mobile.'&package='.$Package.'&range='.$Range.'&outTradeNo='.$OutTradeNo.'&password='.$Password;
		
		global $mysql;
		$where['tid']=$tid;
		$value=$mysql->table('trades')->where($where)->find();
		
		
		$data2['submit_info']=$value['submit_info'].'||'.$url;
		$data2['submit_time']=date('Y-m-d H:i:s',time());
		//$where['tid']=$tid;
		$mysql->table('trades')->where($where)->update($data2); 
		
		$res = curl_get($url);
		
		$data3['return_info']=$value['return_info'].'||'.$res;
		//$where['tid']=$tid;
		$mysql->table('trades')->where($where)->update($data3); 
		
		$newres=json_decode($res);
		
		if($newres->Message=='充值提交成功'){
			$return='提交成功';
		}else{
			$return=$newres->Message;
		}
		//var_dump($return);die();
		return $return;
	}
//新增ahhy流量

	function ahhy($arr,$tid){
		if(!is_array($arr)) {
			return false;
		}
		//var_dump($arr);die();
		$flow=explode('_',$arr['flow']);
		if(empty($flow[1])){
			$res='编码错误';
			return $res;die();
		}
		$Account=$arr["Account"];
		$Password='123456';
		$OutTradeNo=$arr["OutTradeNo"];	
		$Mobile=$arr["Mobile"];
		$Range=$flow[0];
		$Package=$flow[1];
		$Action='charge';
		
		$url='http://120.76.72.110:7070/api.aspx?v=1.0&action=charge&account='.$Account.'&mobile='.$Mobile.'&package='.$Package.'&range='.$Range.'&outTradeNo='.$OutTradeNo.'&password='.$Password;
		//echo $url;die;
		global $mysql;
		$where['tid']=$tid;
		$value=$mysql->table('trades')->where($where)->find();
		
		
		$data2['submit_info']=$value['submit_info'].'||'.$url;
		$data2['submit_time']=date('Y-m-d H:i:s',time());
		//$where['tid']=$tid;
		$mysql->table('trades')->where($where)->update($data2); 
		
		$res = curl_get($url);
		//var_dump($res);die();
		$data3['return_info']=$value['return_info'].'||'.$res;
		//$where['tid']=$tid;
		$mysql->table('trades')->where($where)->update($data3); 
		
		$newres=json_decode($res);
		
		if($newres->Message=='充值提交成功'){
			$return='提交成功';
		}else{
			$return=$newres->Message;
		}
		//var_dump($return);die();
		return $return;
	}
	
	
	
	//新增 时迅捷流量

	function xunjie($arr,$tid){
		if(!is_array($arr)) {
			return false;
		}
		//var_dump($arr);die();
		$flow=explode('_',$arr['flow']);
		if(empty($flow[1])){
			$res='编码错误';
			return $res;die();
		}
		$Account=$arr["Account"];
		$Password='123456';
		$OutTradeNo=$arr["OutTradeNo"];	
		$Mobile=$arr["Mobile"];
		$Range=$flow[0];
		$Package=$flow[1];
		$Action='charge';
		
		$url='http://114.55.75.222:8080/api.aspx?v=1.0&action=charge&account='.$Account.'&mobile='.$Mobile.'&package='.$Package.'&range='.$Range.'&outTradeNo='.$OutTradeNo.'&password='.$Password;
		//echo $url;die;
		global $mysql;
		$where['tid']=$tid;
		$value=$mysql->table('trades')->where($where)->find();
		
		
		$data2['submit_info']=$value['submit_info'].'||'.$url;
		$data2['submit_time']=date('Y-m-d H:i:s',time());
		//$where['tid']=$tid;
		$mysql->table('trades')->where($where)->update($data2); 
		
		$res = curl_get($url);
		//var_dump($res);die();
		$data3['return_info']=$value['return_info'].'||'.$res;
		//$where['tid']=$tid;
		$mysql->table('trades')->where($where)->update($data3); 
		
		$newres=json_decode($res);
		
		if($newres->Message=='充值提交成功'){
			$return='提交成功';
		}else{
			$return=$newres->Message;
		}
		//var_dump($return);die();
		return $return;
	}
	//新增 时迅捷流量

	function cqyd($arr,$tid){
		if(!is_array($arr)) {
			return false;
		}
		//var_dump($arr);die();
		$flow=explode('_',$arr['flow']);
		if(empty($flow[1])){
			$res='编码错误';
			return $res;die();
		}
		$Account=$arr["Account"];
		$Password='7606913';
		$OutTradeNo=$arr["OutTradeNo"];	
		$Mobile=$arr["Mobile"];
		$Range=$flow[0];
		$Package=$flow[1];
		$Action='charge';
		
		$url='http://120.76.191.96:6060/api.aspx?v=1.0&action=charge&account='.$Account.'&mobile='.$Mobile.'&package='.$Package.'&range='.$Range.'&outTradeNo='.$OutTradeNo.'&password='.$Password;
		//echo $url;die;
		global $mysql;
		$where['tid']=$tid;
		$value=$mysql->table('trades')->where($where)->find();
		
		
		$data2['submit_info']=$value['submit_info'].'||'.$url;
		$data2['submit_time']=date('Y-m-d H:i:s',time());
		//$where['tid']=$tid;
		$mysql->table('trades')->where($where)->update($data2); 
		
		$res = curl_get($url);
		//var_dump($res);die();
		$data3['return_info']=$value['return_info'].'||'.$res;
		//$where['tid']=$tid;
		$mysql->table('trades')->where($where)->update($data3); 
		
		$newres=json_decode($res);
		
		if($newres->Message=='充值提交成功'){
			$return='提交成功';
		}else{
			$return=$newres->Message;
		}
		//var_dump($return);die();
		return $return;
	}

//新增武汉鼎信 
	function whdingxin($arr,$tid){
		if(!is_array($arr)) {
			return false;
		}
		//var_dump($arr);die();
		$flow=explode('_',$arr['flow']);
		if(empty($flow[1])){
			$res='编码错误';
			return $res;die();
		}
		$Account=$arr["Account"];
		$Password='qq841653227';
		$OutTradeNo=$arr["OutTradeNo"];	
		$Mobile=$arr["Mobile"];
		$Range=$flow[0];
		$Package=$flow[1];
		$Action='charge';
		
		$url='http://121.43.116.127:8080/api.aspx?v=1.0&action=charge&account='.$Account.'&mobile='.$Mobile.'&package='.$Package.'&range='.$Range.'&outTradeNo='.$OutTradeNo.'&password='.$Password;
		
		global $mysql;
		$where['tid']=$tid;
		$value=$mysql->table('trades')->where($where)->find();
		
		
		$data2['submit_info']=$value['submit_info'].'||'.$url;
		$data2['submit_time']=date('Y-m-d H:i:s',time());
		//$where['tid']=$tid;
		$mysql->table('trades')->where($where)->update($data2); 
		
		$res = curl_get($url);
		
		$data3['return_info']=$value['return_info'].'||'.$res;
		//$where['tid']=$tid;
		$mysql->table('trades')->where($where)->update($data3); 
		
		$newres=json_decode($res);
		
		if($newres->Message=='充值提交成功'){
			$return='提交成功';
		}else{
			$return=$newres->Message;
		}
		
		return $return;
	}
//2016-05-12 新增鼎信流量
	function dx_gprs($arr,$tid){
		if(!is_array($arr)) {
			return false;
		}
		//var_dump($arr);die();
		$flow=explode('_',$arr['flow']);
		if(empty($flow[1])){
			$res='编码错误';
			return $res;die();
		}
		$userid  = strtoupper($arr["userid"]);//用户id
		$pwd	 = strtoupper($arr["pwd"]);	//用户密码
		$orderid = $arr["orderid"];			//订单号
		$account = $arr["auccount"];			//充入的手机号
		$gprs 	 = $flow[1];			//流量
		$area 	 = $flow[0];			//1省内 或者0全国
		$effecttime= 0;	//0 即时生效，1次日生效，2 次月生效
		$validity = 0;		//传入月数 0就是当月有效
		$times 	 = date("YmdHis",time());	//时间戳
		$apikey='6889DE7163436EA9A39D64B876DAF0D3';
		//签名
		$userkey = MD5("userid{$userid}pwd{$pwd}orderid{$orderid}account{$account}gprs{$gprs}area{$area}effecttime{$effecttime}validity{$validity}times{$times}{$apikey}");
		//参数
		$parameter = "userid={$userid}&pwd={$pwd}&orderid={$orderid}&account={$account}&gprs={$gprs}&area={$area}&effecttime={$effecttime}&validity={$validity}&times={$times}&userkey={$userkey}";
		
		global $mysql;
		$where['tid']=$tid;
		$value=$mysql->table('trades')->where($where)->find();
		
		
		$data2['submit_info']=$value['submit_info'].'||'.$parameter;
		$data2['submit_time']=date('Y-m-d H:i:s',time());
		//$where['tid']=$tid;
		$res=$mysql->table('trades')->where($where)->update($data2); 
	
	
		//请求地址
		$get_url = "http://api.ejiaofei.net:11140/gprsChongzhiAdvance.do?".$parameter;
		//发送请求信息
		$xml_str = curl_get($get_url);
		//将xml转成对象
		$result = xml_to_arry($xml_str);
		
		$data3['return_info']=$value['return_info'].'||'.json_encode($result);
		//$where['tid']=$tid;
		$res=$mysql->table('trades')->where($where)->update($data3); 

		
		if($result->state==0){
			if($result->error==0 or $result->error==8){
				$res='提交成功';
			}elseif($result->error==1003){
				$res='用户ID错误';
			}elseif($result->error==1004){
				$res='用户IP错误';
			}elseif($result->error==1005){
				$res='用户接口已关闭';
			}elseif($result->error==1006){
				$res='加密结果错误';
			}elseif($result->error==1007){
				$res='订单号不存在';
			}elseif($result->error==1011){
				$res='号码归属地未知';
			}elseif($result->error==1013){
				$res='手机对应的商品有误或者没有上架';
			}elseif($result->error==1014){
				$res='无法找到手机归属地';
			}elseif($result->error==1015){
				$res='余额不足';
			}elseif($result->error==1017){
				$res='产品未分配用户，联系商务';
			}elseif($result->error==1018){
				$res='订单生成失败';
			}elseif($result->error==1019){
				$res='充值号码与产品不匹配';
			}elseif($result->error==1020){
				$res='号码运营商未知';
			}elseif($result->error==9998){
				$res='参数有误';
			}elseif($result->error==9999){
				$res='系统错误';
			}else{
				$res='未知错误';
			}
		}else{
			$res='请求异常';
		}
		return $res;
		//返回请求结果 
		#return get_error_msg($result->error);//获取是否错误
	}

function xml_to_array( $xml )
{
    $reg = "/<(\\w+)[^>]*?>([\\x00-\\xFF]*?)<\\/\\1>/";
    if(preg_match_all($reg, $xml, $matches))
    {
        $count = count($matches[0]);
        $arr = array();
        for($i = 0; $i < $count; $i++)
        {
            $key= $matches[1][$i];
            $val = xml_to_array( $matches[2][$i] );  // 递归
            if(array_key_exists($key, $arr))
            {
                if(is_array($arr[$key]))
                {
                    if(!array_key_exists(0,$arr[$key]))
                    {
                        $arr[$key] = array($arr[$key]);
                    }
                }else{
                    $arr[$key] = array($arr[$key]);
                }
                $arr[$key][] = $val;
            }else{
                $arr[$key] = $val;
            }
        }
        return $arr;
    }else{
        return $xml;
    }
}


//2016-04-14 新增大汉流量查询
function dahan_find($order){
	$account='AdminLiank';
	$pwd='929517';
	$sign=md5($account.md5($pwd));
	$clientOrderId=$order;
	$url = "http://if.dahanbank.cn/FCSearchReportDataServlet?account=$account&sign=$sign&clientOrderId=$clientOrderId";
	$resultCode = json_decode(curl_get($requst));
	//var_dump($resultCode);die();
	if(empty($resultCode)){
		$res='充值进行中';
	}else{
		if(!empty($resultCode->status)){
			if($resultCode->status==0){
				$res='充值成功';	
			}else{
				$res='充值失败';	
			}
		}else{
			$res='查询失败';	
		}
	}
	return $res;
	//var_dump($resultCode);die();
}

//2016-05-26 新增瑞翼平台查询
function ruiyi_find($order){
	 require_once "includes/rsa.class.php";
     $rsa=new RSA1();
	 $result=$rsa->query_order_status($order);
	 $res=json_decode($result[1]);
	 if($res->isp_status_code=='E00000') 
	 {
		 $msg='充值成功';
	 }
	 if($res->isp_status_code=='E10000' ) 
	 {
		// $msg='充值进行中';
	 }
	 if($res->isp_status_code=='E31001') 
	 {
		 //$msg='';
	 }
	return $res;
}

//2016-05-26 新增鼎信流量查询
function dinxin_find($order){
	
	    $userid  = strtoupper('15317110118');//用户id
		$pwd	 = strtoupper('38D7F46978F034FBDEEA57FFE433110B');	//用户密码
		$orderid = $order;			//订单号
		$apikey='6889DE7163436EA9A39D64B876DAF0D3';
		//签名
		$userkey = MD5("userid{$userid}pwd{$pwd}orderid{$orderid}{$apikey}");
		//参数
		$parameter = "userid={$userid}&pwd={$pwd}&orderid={$orderid}&userkey={$userkey}";
		
		//请求地址
		$get_url = "http://api.ejiaofei.net:11140/query_jkorders.do?".$parameter;
		//发送请求信息
		$xml_str = curl_get($get_url);
		//将xml转成对象
		$result = xml_to_arry($xml_str);
		
		if($result->error=='0')
		{
			if($result->state=='1')
			{
				$msg='充值成功';
			}
			elseif($result->state=='2')
			{
				$msg='充值失败';
			}
			else
			{
				$msg='充值进行中';
			}
		}
		else
		{
			$msg=get_error_msg($result->error);
		}
	   return $msg;
}

//2016-05-27 新增德佳平台查询
function deyi_find($order){
	$data['flowrecord']=$order;
	$data['channelid']='uxmn0bqh';
	$data['timestamp']=date('YmdHis');
	$apikey='iCCJ1m2cM2ZEeXIaEeKKNzDvZBsSXwgE';
	$data['sign']=md5($data['channelid'].$data['timestamp'].md5(md5($apikey)));
	$url='http://admin.xll.sfssh.com/adapter/queryflowrecharge';
	
	$result=json_decode(curl_post($url,$data));
	if($result->ret=='000')
	{
		$msg='充值成功';
	}
	elseif($result->ret=='001')
	{
		$msg='充值失败';
	}
	else
	{
		$msg=$result->msg;
	}
	return $msg;
	
}
//2016-06-07 新增秒冲流量查询
function miaochong_find($order){
	
	$url='http://112.74.93.118/order.php?name=lianke&password=qq841653227&order='.$order;
	
	$res=curl_get($url);
	
	preg_match_all('/\d+/',$res,$arr);
	$res = join('',$arr[0]);
	
	if($res=='0000'){
		$msg='充值成功';
	}elseif($res=='0001'){
		$msg='充值失败';
	}elseif($res=='0002'){
		$msg='账号错误';
	}elseif($res=='0003'){
		$msg='订单不存在';
	}elseif($res=='0004'){
		$msg='充值进行中';
	}elseif($res=='0005'){
		$msg='等待充值';
	}else{
		$msg='未知错误';
	}
	return $msg;
	
}


function chargeWithPlatformDaHanTricom($arr,$tid) {
	if(!is_array($arr)) {
		return false;
	}

	$currentTimestamp = time();

	if($currentTimestamp - $arr['timestamp'] < 300000) {
		// return false;
	}

	$sign = MD5(  $arr['account'] . MD5($arr['pwd']) . $arr['timestamp'] . $arr['mobiles']  );

	$packageSize = $arr['packageSize'];

	$tmpPackageSize = $arr['packageSize'];
	$packageSize = $tmpPackageSize;
	$packageSizeUnit = substr($packageSize, strlen($packageSize) - 1);
	$size = str_replace('M', '', $tmpPackageSize);
	$size = str_replace('G', '', $size);

	if($packageSizeUnit === 'G') {
		$size = $size * 1000;
	}

	$data = array(
		'account' => $arr['account'],
		'timestamp' => $arr['timestamp'],
		'mobiles' => $arr['mobiles'],
		'sign' => $sign,
		'msgTemplateId' => 1,
		'packageSize' => $size,
		'clientOrderId' => $arr['clientOrderId']
	);

	$data=json_encode($data);
	 
//var_dump($data); die();
	$url = "http://if.dahanbank.cn/FCOrderServlet";
	
	global $mysql;
	$where['tid']=$tid;
	$value=$mysql->table('trades')->where($where)->find();
	
	$dat=json_encode($data);
	
	$data2['submit_info']=$value['submit_info'].'||'.$dat;
	$data2['submit_time']=date('Y-m-d H:i:s',time());
	//$where['tid']=$tid;
	$res=$mysql->table('trades')->where($where)->update($data2); 

	$result = curl_post($url, $data);
	
	$data3['return_info']=$value['return_info'].'||'.$result;
	//$where['tid']=$tid;
	$res=$mysql->table('trades')->where($where)->update($data3); 

	//var_dump($result);
//die();
	return $result;
}
//2016-03-25 新增C平台
function tianjin($arr,$tid){
	if(!is_array($arr)) {
		return false;
	}
	$v = $arr['v'];//固定值
	$action = $arr['action'];
	$account = $arr['account'];//充值平台的账号
	$password = $arr['password'];//充值平台的密码
	$Mobile = $arr['Mobile'];//手机号
	$Package = $arr['Package'];//套餐
	$Range = $arr['Range'];//类型
	$OutTradeNo = $arr['OutTradeNo'];//单号
	//http://120.25.248.107:8080/api.aspx
	//http://*/api.aspx?v=1.0&action=charge&account=帐号&password=密码&Mobile=手机号&Package=100
	$requst = "http://120.25.248.107:8080/api.aspx?v=$v&action=$action&account=$account&password=$password&mobile=$Mobile&Package=$Package";
	
	global $mysql;
	$where['tid']=$tid;
	$value=$mysql->table('trades')->where($where)->find();
	
	//$dat=json_encode($data);
	
	$data2['submit_info']=$value['submit_info'].'||'.$requst;
	//$where['tid']=$tid;
	$res=$mysql->table('trades')->where($where)->update($data2); 
	
	//echo $requst;
	$resultCode = json_decode(curl_get($requst));
	
	$resultCode2=json_encode($requst);
	
	$data3['return_info']=$value['return_info'].'||'.$resultCode2;
	//$where['tid']=$tid;
	$res=$mysql->table('trades')->where($where)->update($data3); 
	
	//var_dump($resultCode);die();
	if(!empty($resultCode->Code)){
		$res=$resultCode->Message;
	}else{
		$res='提交成功';
	}
	return $res;
}




//2016-06-06 新增秒冲流量
function miaochong($arr,$tid){
	if(!is_array($arr)) {
		return false;
	}
	$name = $arr['name'];//账号
	$password = $arr['password'];//密码
	$method ='add';//固定值
	$types = $arr['types'];//编码
	$mobile = $arr['mobile'];//手机号
	$order = $arr['order'];//订单号
	//http://112.74.193.118/api.php?name=test&password=111111&method=add&types=100&mobile=13800138000&order=12345678941222
	
	$url='http://112.74.93.118/api.php?name='.$name.'&password='.$password.'&method=add&types='.$types.'&mobile='.$mobile.'&order='.$order;
	
	global $mysql;
	$where['tid']=$tid;
	$mysql->table('trades')->where($where)->find();
	
	//$dat=json_encode($data);
	
	$data2['submit_info']=$value['submit_info'].'||'.$url;
	//$where['tid']=$tid;
	$mysql->table('trades')->where($where)->update($data2); 
	
	$res=number(curl_get($url));
	
	$data3['return_info']=$value['return_info'].'||'.$res;
	//$where['tid']=$tid;
	$mysql->table('trades')->where($where)->update($data3); 
	
	if($res=='0000'){
		$newres='提交成功';
	}elseif($res=='0001'){
		$newres='提交失败';
	}elseif($res=='0002'){
		$newres='余额不足';
	}elseif($res=='0003'){
		$newres='非法操作';
	}elseif($res=='0004'){
		$newres='号码有误';
	}elseif($res=='0005'){
		$newres='账号错误';
	}elseif($res=='0006'){
		$newres='重复提交';
	}elseif($res=='0007'){
		$newres='号码限制';
	}else{
		$newres='未知错误';
	}
	return $newres;
}


//2016-03-28 新增E平台
function liuliang2491($rsa,$arr,$tid){
	if(!is_array($arr)) {
		return false;
	}

	$tel=$arr['mobile'];//电话
	$partnerOrderNo=$arr['partnerOrderNo'];//订单号
	$types=$arr['types'];//产品编号/id
	$rsa->_order_id=$partnerOrderNo;
	$resultCode=$rsa->single_charge($tel,$types,'');
	
	global $mysql;
	$where['tid']=$tid;
	
	$value=$mysql->table('trades')->where($where)->find();
	
	$data3['return_info']=$value['return_info'].'||'.json_encode($resultCode);
	//$where['tid']=$tid;
	$res=$mysql->table('trades')->where($where)->update($data3); 
	//return $resultCode;
	//var_dump($resultCode);die();
	if($resultCode[0]==''){
		$result=json_decode($resultCode[1]);
		$res=$result->msg;
	}else{
		$res='提交成功';
	}
	return $res;
}

//2016-05-11 新增鼎信接口  充值话费接口
function dx_chong($arr,$tid){
	$userid  = strtoupper($arr["userid"]);//用户id
	$pwd	 = strtoupper($arr["pwd"]);//用户密码
	$orderid = $arr["orderid"];//订单号
	$face 	 = $arr["face"];//编码
	$account = $arr["auccount"];//充入的手机号
	$amount	 = 1;//购买数量
	$apikey='6889DE7163436EA9A39D64B876DAF0D3';
	//签名【加密结果】 
	$userkey = MD5("userid{$userid}pwd{$pwd}orderid{$orderid}face{$face}account{$account}amount1{$apikey}");

	//发送参数
	$parameter = "userid={$userid}&pwd={$pwd}&orderid={$orderid}&account={$account}&face={$face}&amount=1&userkey={$userkey}";
	//请求地址
	$get_url = "http://api.ejiaofei.net:11140/chongzhi_jkorders.do?".$parameter;
	//发送请求信息
	$xml_str = curl_get($get_url);
	//将xml转成对象
	$result = xml_to_arry($xml_str);
	//返回请求结果 
	return get_error_msg($result->error);//获取是否错误
}

function xml_to_arry($xml){
	$xml_str = simplexml_load_string($xml);
	$res_xml = json_decode(json_encode($xml_str,true));
	return $res_xml;
}
	function get_error_msg($code){
		switch($code){
			case 0:
				$str = "提交成功";
				break;
			case "1003":
				$str = "用户id出错";
				break;
			case "1004":
				$str = "提交成功";
				break;
			case "1005":
				$str = "当前接口已关闭，请联系供应商、";
				break;
			case "1006":
				$str = "签名出错";
				break;
			case "1007":
				$str = "订单号不存在";
				break;
			case "1011":
				$str = "号码归属地未知";
				break;
			case "1013":
				$str = "手机对应的产品未知";
				break;
			case "1014":
				$str = "无法找到手机归属地";
				break;
			case "1015":
				$str = "余额不足";
				break;
			case "1016":
				$str = "QQ号格式错误";
				break;
			case "1017":
				$str = "产品未分配用户，联系供应商";
				break;
			case "1018":
				$str = "订单生成失败";
				break;
			case "1019":
				$str = "充值号码与产品不配";
				break;
			case "1020":
				$str = "号码运营商未知";
				break;
			case "9998":
				$str = "参数有误";
				break;
			case "9999":
				$str = "系统错误，请联系供应商";
				break;
		}
		return $str;
	}
	
//2016-05-11 新增智信流量

function zhixin($arr,$tid){
	if(!is_array($arr)) {
		return false;
	}
	$flow=explode('_',$arr['flow']);
	if(empty($flow[1])){
		$res='编码错误';
		return $res;die();
	}
	$req['mrch_no']='100334';    //商户代号
	$req['request_time']=date('YmdHis',time());
	$req['client_order_no']=$tid;    //商户交易流水号
	$req['product_type']=$flow[0];    //产品类型   4 国内流量  5 省内流量
	$req['phone_no']=$arr['phone_no'];   //充值号码
	$req['cp']='';   //运营商  
	$req['city_code']='';     //地区码
	$req['recharge_amount']=$flow[1];    //充值面额/流量包大小
	$req['recharge_type']=0;    //支付方式  0: 预存金中扣
	$req['recharge_desc']='';  //充值描述
	$req['notify_url']='link.517.lv/zhixin_url.php';     //回调地址
	
	ksort($req);
	
	foreach($req as $k=>$v)
	{
		$str.=$k.$v;
	}
	$secret_key='UBlCQMxzxKNsMqbKNFAxvprTq5wosY7a';
	$str.=$secret_key;
	
	$req['sign']=strtolower(md5($str));
	
	$url="http://api.julives.com:9080/zxpaycore/v2/recharge";
	//var_dump(json_encode($req));
	global $mysql;
	$where['tid']=$tid;
	$value=$mysql->table('trades')->where($where)->find();
	
	$data=json_encode($req);
	
	$data2['submit_info']=$value['submit_info'].'||'.$data;
	$where['tid']=$tid;
	$data2['submit_time']=date('Y-m-d H:i:s',time());
	$res=$mysql->table('trades')->where($where)->update($data2); 
	
	
	$resultCode = json_decode(curl_post_json($url,json_encode($req)));
	
	 $resultCode2=json_encode($resultCode);
	 $data3['return_info']=$value['return_info'].'||'.$resultCode2;
	 $data3['return_tid']=$resultCode->transCode;
	$mysql->table('trades')->where($where)->update($data3); 
	
	if($resultCode->code==2){
		$res='提交成功';
	}else{
		$res=$resultCode->message;
	}
	
	return $res;
	
}



function curl_post_json($url, $data) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=UTF-8'));  
	$res = curl_exec($ch);
	curl_close($ch);
	return $res;
}



//2016-05-11 新增尚通 
	function shangTom($data,$tid){
		//拦截
		if(!is_array($data)){return false;}
		$flow=explode('_',$data['flow']);
		if(empty($flow[1])){
			$res='编码错误';
			return $res;die();
		}
		//var_dump($flow);die();
		$pad['account']  	= $data['account'];//账号
		$pad['action']   	= "Charge";//操作值 //"Charge";//
		$pad['phone'] 	  	= $data['phone'];//手机号
		$pad['size'] 	  	= $flow[1];//流量大小
		$pad['range'] 	  	= $flow[0];//范围 1-0
		$pad['timeStamp']	= time();//时间
		$apiKey=$data['apiKey'];
		//请求签名 $data['sign']
		$sign_str = "{$apiKey}account={$pad['account']}&action=Charge&phone={$pad['phone']}&range={$pad['range']}&size={$pad['size']}&timeStamp={$pad['timeStamp']}{$apiKey}";
		$pad['sign'] = md5($sign_str);
		
		global $mysql;
		$where['tid']=$tid;
		$value=$mysql->table('trades')->where($where)->find();
		
		$newdata=json_encode($pad);
		
		$data2['submit_info']=$value['submit_info'].'||'.$newdata;
		$where['tid']=$tid;
		$data2['submit_time']=date('Y-m-d H:i:s',time());

		$res=$mysql->table('trades')->where($where)->update($data2); 
		
		
		//请求地址
		$resp_url = "http://121.41.8.25:8081/Submit.php";//"http://mysqlin.kailuo.org/Api/trystr.php";//
		
		$result = json_decode(curl_post($resp_url,$pad));
		
		 $resultCode2=json_encode($result);
		 $data3['return_info']=$value['return_info'].'||'.$resultCode2;
		 //$data3['return_tid']=$resultCode->orderID;
		$mysql->table('trades')->where($where)->update($data3); 
		
		//var_dump($result);die();
		if($result->respCode=='0000'){
			$res='提交成功';
		}else{
			$res=$result->respMsg;
		}
		return $res;
	}



//2016-04-14 新增容米查询
function rongmi_find($order){
	$requst = "http://120.26.54.129:8090/kzllczpt/api/response/restful/queryluochdj";
	$resultCode =curl_post($requst,$order);
	$data=explode('-',$resultCode);
	if(!empty($data[2])){
		if($data[2]=='00001'){
			$res='充值号码为空';
		}elseif($data[2]=='00002'){
			$res='账户余额不足';
		}elseif($data[2]=='00003'){
			$res='下单失败';
		}elseif($data[2]=='00004'){
			$res='加密不匹配';
		}elseif($data[2]=='00005'){
			$res='传递参数格式不正确';
		}elseif($data[2]=='00006'){
			$res='充值成功';
		}elseif($data[2]=='00007'){
			$res='充值进行中';
		}elseif($data[2]=='00008'){
			$res='充值失败';
		}elseif($data[2]=='00009'){
			$res='订单不存在';
		}else{
			$res='未知错误';
		}
	}else{
		$res='未知错误';
	}
	return $res;
}

//2016-04-09 新增容米平台
function rongmi($arr,$tid){
	if(!is_array($arr)) {
		return false;
	}
	$dlbh=$arr['dlbh'];//代理编号
	$phone=$arr['phone'];//手机号
	$pid=$arr['pid'];//产品id
	$ddbh=$arr['ddbh']; //订单号
	$secretkey=$arr['secretkey'];
	$iv=$dlbh;
	$str=$dlbh.'-'.$phone.'-'.$pid.'-'.$ddbh.'-'.$secretkey;
	$res=md5($str);
	$requst = "http://120.26.54.129:8080/kzllczpt/api/response/restful/luochdj";
	$data=$dlbh.'-'.$phone.'-'.$pid.'-'.$ddbh.'-'.$secretkey.'-'.$res;
	
	global $mysql;
	$where['tid']=$tid;
	$value=$mysql->table('trades')->where($where)->find();
	
	//$dat=json_encode($data);
	
	$data2['submit_info']=$value['submit_info'].'||'.$data;
	//$where['tid']=$tid;
	$res=$mysql->table('trades')->where($where)->update($data2); 
	
	
	//echo $data.'<br/>';
	$resultCode = json_decode(curl_post($requst,$data));
	
	$resultCode2=json_encode($resultCode);
	$data3['return_info']=$value['return_info'].'||'.$resultCode2;
	$mysql->table('trades')->where($where)->update($data3); 
	
	//var_dump($resultCode);die();
	if(!empty($resultCode->result_code) and $resultCode->result_code!='00000' and $resultCode->result_code!='00006'){
		if($resultCode->result_code=='00001'){
			$res2['code']='充值号码为空';
		}elseif($resultCode->result_code=='00002'){
			$res2['code']='账户余额不足';
		}elseif($resultCode->result_code=='00003'){
			$res2['code']='下单失败';
		}elseif($resultCode->result_code=='00004'){
			$res2['code']='加密不匹配';
		}elseif($resultCode->result_code=='00005'){
			$res2['code']='传递参数格式不正确';
		}elseif($resultCode->result_code=='00007'){
			$res2['code']='充值进行中';
		}elseif($resultCode->result_code=='00008'){
			$res2['code']='充值失败';
		}elseif($resultCode->result_code=='00009'){
			$res2['code']='订单不存在';
		}else{
			$res2['code']='未知错误';
		}
		$res=$resultCode->Message;
	}elseif($resultCode->result_code=='00000'){
		$res2['code']='提交成功';
		$res2['request_no']=$resultCode->request_no;
	}elseif(!empty($resultCode->developerMessage)){
		$res2['code']=$resultCode->developerMessage;
	}else{
		$res2['code']='未知错误';
	}
	return $res2;
}

function getMillisecond() {
list($t1, $t2) = explode(' ', microtime());
return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
}

//2016-04-29 新增上海卓一
function zhuoyi($arr,$tid){
	if(!is_array($arr)) {
		return false;
	}
	$req['customer']=$arr['customer'];//渠道商ID
	$req['product']=$arr['product'];//产品编码
	$req['phone']=$arr['phone'];//号码
	$req['customerOrderId']=$tid;//订单号
	//$token=$arr['token'];
	$req['timestamp']=getMillisecond();
	$req['userKey']=md5($arr['customer'].$arr['token'].$req['timestamp']);
	//$req['returnUrl']='http://hinh.517.lv/zy_url.php';
	$req['returnUrl']='http://link.517.lv/zy_url.php';
	$url="http://120.26.78.209/nettraffic/api/order";
	
	
	
	global $mysql;
	$where['tid']=$tid;
	$value=$mysql->table('trades')->where($where)->find();
	
	$data=json_encode($req);
	
	$data2['submit_info']=$value['submit_info'].'||'.$data;
	$where['tid']=$tid;
	$data2['submit_time']=date('Y-m-d H:i:s',time());
	$res=$mysql->table('trades')->where($where)->update($data2); 
	
	
	//echo $data.'<br/>';
	$resultCode = json_decode(curl_post($url,$req));
	
	$resultCode2=json_encode($resultCode);
	$data3['return_info']=$value['return_info'].'||'.$resultCode2;
	$data3['return_tid']=$resultCode->transCode;
	$mysql->table('trades')->where($where)->update($data3); 
	
	return $resultCode->resultMsg;
	
}

//2016-05-28 新增任我行
function renwoxing($arr,$tid){
	if(!is_array($arr)) {
		return false;
	}
	
	$flow=explode('_',$arr['flow']);
	if(empty($flow[1])){
		$res='编码错误';
		return $res;die();
	}
	
	$apikey='88740a05a8a448249188b0801c4ac9bc'; 
	$req['Action']='charge';//命令
	$req['V']='1.1';//版本号
	$req['Range']=$flow[0];//流量类型  0 全国流量 1省内流量，不带改参数时默认为0
	$req['OutTradeNo']=$tid;//订单号
	$req['Account']=$arr['Account']; //帐号
	$req['Mobile']=$arr['Mobile'];
	$req['Package']=$flow[1]; //流量包大小
	$req['Sign']=strtolower(md5("account={$req['Account']}&mobile={$req['Mobile']}&package={$req['Package']}&key={$apikey}")); //签名
	$url="http://my.llt800.com/api.aspx";
	
	global $mysql;
	$where['tid']=$tid;
	$value=$mysql->table('trades')->where($where)->find();
	
	$data=json_encode($req);
	
	$data2['submit_info']=$value['submit_info'].'||'.$data;
	$where['tid']=$tid;
	$data2['submit_time']=date('Y-m-d H:i:s',time());
	$res=$mysql->table('trades')->where($where)->update($data2); 
	
	
	//echo $data.'<br/>';
	$result = curl_post($url,$req);
	$resultCode = json_decode($result);
    //var_dump($resultCode);exit;
	$resultCode2=json_encode($resultCode);
	$data3['return_info']=$value['return_info'].'||'.$resultCode2;
	$data3['return_tid']=$resultCode->transCode;
	$mysql->table('trades')->where($where)->update($data3); 
	 
	if($resultCode->Code=='0')
	{
	   return '提交成功';
	}
	else
	{
		return $resultCode->Message;
	}
	
}

/* 手机号信息  2017-01-13*/
/*function mobile_info($mobile)
{
	$requst='http://apis.juhe.cn/mobile/get?phone='.$mobile.'&key=facdb088ab535062ebb9e1f53dac77d1';
	
	$res = curl_get_https($requst);
	$res=json_decode($res,true);		
	$result=$res['result'];
	
	if($res['resultcode']!=200)
	{
		$result['error']=$res['reason'];
	}
	return $result;
}*/

function mobile_info($mobile)
{
	//$requst='http://apis.juhe.cn/mobile/get?phone='.$mobile.'&key=facdb088ab535062ebb9e1f53dac77d1';
	$requst='http://sj.apidata.cn/?mobile='.$mobile;
	
	$res = curl_get($requst);
	$res=json_decode($res,true);		
	$result=$res['data'];
	
	if($res['status']!=1)
	{
		$result['error']=$res['message'];
	}
	return $result;
}

//2016-03-25 地区判断
/*function curl_https($Mobile,$region,$operator){
	$region=substr($region,0,6);
	//https://tcc.taobao.com/cc/json/mobile_tel_segment.htm?tel=15170007698
	$requst='https://tcc.taobao.com/cc/json/mobile_tel_segment.htm?tel='.$Mobile;
	//echo $requst;
	$resultCode = iconv("GB2312","UTF-8",curl_get_https($requst));
	$res='';//最终返回的结果
	if(!strstr($resultCode,$region) && $region!='全国'){
		$res='提交号码省份错误,';
	}
	if(!strstr($resultCode,$operator)){

		$res='运营商错误';
	}
	if(empty($res)){
		return '1';
	}else{
		return trim($res,',');
	}
}*/

//2016-09-19 新增
function curl_https($Mobile,$region,$operator){
		$region=substr($region,0,6);
			//https://tcc.taobao.com/cc/json/mobile_tel_segment.htm?tel=15170007698
			$requst='https://tcc.taobao.com/cc/json/mobile_tel_segment.htm?tel='.$Mobile;
			//echo $requst;
			$resultCode = iconv("GB2312","UTF-8",curl_get_https($requst));
			$res='';//最终返回的结果
		
			if(!strstr($resultCode,$region) && $region!='全国'){
				$res='提交号码省份错误,';
			} 
			if(!strstr($resultCode,$operator)){
		
				$res='运营商错误2';
			}
			if(empty($res)){
				$return = '1';
			}else{
				$return = trim($res,',');
			}
			if($return=='1'){
				return $return;
			}else{
				$region=substr($region,0,6);
				$requst='http://apis.juhe.cn/mobile/get?phone='.$Mobile.'&key=facdb088ab535062ebb9e1f53dac77d1';
				$resultCode = curl_get_https($requst);
				$res='';//最终返回的结果
				if(!strstr($resultCode,$region) && $region!='全国'){
					$res='提交号码省份错误,';
				}
				if(!strstr($resultCode,$operator)){
			
					$res='运营商错误3'.$operator;
				}
				if(empty($res)){
					return '1';
				}else{
					return trim($res,',');
				}
			}
	}

// // instance 1
// echo('=========秒冲平台测试=========<br>');
// print_r(chargeWithPlatformMiaoChong(array(
// 	'name' => 'test',
// 	'password' => '111111',
// 	'method' => 'add',
// 	'types' => '100',
// 	'mobile' => '13800138000',
// 	'order' => '12345678941222'
// )));

// //instance 2
// echo('<br>=========银行平台测试=========<br>');
// print_r(chargeWithPlatformDaHanTricom(array(
// 	'account' => '123456',
// 	'mobiles' => '123456',
// 	'clientOrderId' => 1,
// 	'packageSize' => '1G',
// 	'pwd' => '123',
// 	'timestamp' => time()
// )));
//sichuan(array('type'=>'llcz','account'=>'699999','mobile'=>'13551897773','productid'=>'100030','key'=>'4308af60633aeaa0397615df784e59a6','range'=>0,'outtradeno'=>'123456'));
function sichuan($arr,$tid){
		
		if(!is_array($arr)) {
			return false;
		}
		$flow=explode('_',$arr['flow']);
		if(empty($flow[1])){
			$res='编码错误';
			return $res;die();
		}
		$time	=	getMillisecond(); 
		$arr['range']=$flow[0];
		$arr['productid']=$flow[1];
		$str	=	'account='.$arr['account'].'&mobile='.$arr['mobile'].'&productid='.$arr['productid'].'&_t='.$time.'&key='.$arr['key'];
		//$str	=	'account=699999&mobile=15914312933&productid=100030&_t=1467710995&key=4308af60633aeaa0397615df784e59a6';
		$sign	=	md5($str);
		//var_dump($sign);die;
		$data	=	array(
			'type'		=>	$arr['type'],
			'account'	=>	$arr['account'],
			'mobile'	=>	$arr['mobile'],
			'productid'	=>	$arr['productid'],
			'_t'		=>	$time,
			'outtradeno'		=>	$arr['outtradeno'],
			'range'		=>	$arr['range'],
			'sign'		=>	$sign,
		);
	
		global $mysql;
		$where['tid']=$tid;
		$value=$mysql->table('trades')->where($where)->find();
		
		$newdata=json_encode($data);
		
		$data2['submit_info']=$value['submit_info'].'||'.$newdata;
		$where['tid']=$tid;
		$data2['submit_time']=date('Y-m-d H:i:s',time());
		$mysql->table('trades')->where($where)->update($data2); 
	
		$url	=	"http://112.74.93.128:8090/index.php/Home/Liuliang/sub_data"; 
		
		//$result	=iconv('GB2312', 'UTF-8', curl_get_https($url, $data));
		$result	=curl_get_https($url, $data);
		$res=json_decode($result);
		//var_dump($result);die;
		/*if(empty($res->Code)){
			$res='请求超时';
			return $res;die();
		}*/
		
		$data3['return_info']=$value['return_info'].'||'.$result;
		if(isset($res->TaskID)){
			$data3['return_tid']=$res->TaskID;
		}
		$mysql->table('trades')->where($where)->update($data3); 
		
		
		if($res->Code=='0'){
			$return='提交成功';
		}else{
			$return=$res->Message;
		}
		
		return $return;
		
		
}

function huayi2($arr){
		if(!is_array($arr)) {
			return false;
		}
		$time	=	time();
		$str	=	'account='.$arr['account'].'&_t='.$time."&key=".$arr['key'];
		$sign	=	md5($str);
		$data	=	array(
			'type' 		=> $arr['type'],
			'account' 	=> $arr['account'],
			'_t' 		=> $time,
			'sign' 		=> $sign,
		);
		$url	=	"http://112.74.93.128:1110/post.api";
		$result	=curl_get_https($url, $data);
		$res=json_decode(iconv('GB2312', 'UTF-8',$result));
		var_dump($res);
}

//2016-07-21 小陈新增
function beibei($arr,$tid){
	if(!is_array($arr)){
		return false;
	}
	$flow	=	explode("-",$arr['flow']);
	if(empty($flow[1])){
		$res = "编码错误";
		return $res ;die();
	}
	$user			=	"18121022000@api";
	$password		=	"022000@api";
	$pwd			=	md5($password);
	$bcallbackUrl 	= 	"http://link.517.lv/beibei_url.php";
	$interfaceSign	= 	"af5b6fc0591043f1a2f879bca885d0da";
	
	$sign=md5("userName=".$user."&userPwd=".$pwd.$interfaceSign."&mobile=".$arr['mobile']."&proKey=".$flow[1]."&orderNo=".$arr['orderNo']."&bcallbackUrl=".$bcallbackUrl);
	$post			=	"flowType=".$flow[0]."&f=recharge&userName=".$user."&userPwd=".$pwd."&mobile=".$arr['mobile']."&proKey=".$flow[1]."&orderNo=".$arr['orderNo']."&bcallbackUrl=".$bcallbackUrl."&sign=".$sign ;
	
		global $mysql;
		$where['tid']=$tid;
		$value=$mysql->table('trades')->where($where)->find();
		
		$data2['submit_info']=$value['submit_info'].'||'.$post;
		$data2['submit_time']=date('Y-m-d H:i:s',time());
		$mysql->table('trades')->where($where)->update($data2); 
		
	$url	=	"http://119.29.48.212:9090/flowRquest.do";//"http://ll.ibumobile.com";
	$result	=	curl_post($url,$post);
	
		
		$data3['return_info']=$value['return_info'].'||'.$result;
		$mysql->table('trades')->where($where)->update($data3); 
	
	$res	=	json_decode($result);
	if($res->code=='100'){
		$return ='提交成功';
	}else{
		$return=$res->tip;
	}
	return $return;
}
//2016-07-07 新增四川查询
function sichuan_find($order){
		$type='ztcx';
		$account='699999';
		$taskid=$order;
		$_t=getMillisecond();
		$key='4308af60633aeaa0397615df784e59a6';
		$sign=md5('account='.$account.'&taskid='.$taskid.'&_t='.$_t."&key=".$key);
		
		$data['type']=$type;
		$data['account']=$account;
		$data['taskid']=$taskid;
		$data['_t']=$_t;
		$data['sign']=$sign;
		
		$url	=	"http://112.74.93.128:1110/post.api";
		
		$result	=curl_get_https($url, $data);
		$res=json_decode(iconv('GB2312', 'UTF-8',$result));
		
		if($res->Detail->status==2){
			$return='充值成功';
		}elseif($res->Detail->status==1){
			$return='充值失败';
		}else{
			$return='充值进行中';
		}
		return $return;
}

function chargeWithEsai($arr,$tid) {

	if(!is_array($arr)) {
		return false;
	}
$StartTime=date("Y-m-d H:i:s");//"2016-03-16 02:30:14";//date("Y-m-d H:i:s")
$TimeOut="5000";
$str=$arr['UserNumber'].$arr['OutOrderNumber'].$arr['ProId'].$arr['PhoneNumber'].'1'.$StartTime.$TimeOut."ef96e88ed176a48a6b55d1cc1eef088a";
$str=iconv('utf-8', 'gb2312', $str);
$sign=substr(md5($str),0,16); 
$data = array(
		'UserNumber' => $arr['UserNumber'],
		'ProId' => $arr['ProId'],
		'PhoneNumber' => $arr['PhoneNumber'],
		'OutOrderNumber' => $arr['OutOrderNumber'],
 		'TimeOut' =>$TimeOut,
 		'PayAmount' =>1,
 		'StartTime' =>$StartTime,
 		'RecordKey' =>$sign
	);


	global $mysql;
	$where['tid']=$tid;
	$value=$mysql->table('trades')->where($where)->find();
	
	//$dat=json_encode($data);
	$dat=json_encode($data);
	$data2['submit_info']=$value['submit_info'].'||'.$dat;
	$data2['submit_time']=date('Y-m-d H:i:s',time());
	//$where['tid']=$tid;
	$res=$mysql->table('trades')->where($where)->update($data2); 
	//$data=json_encode($data);
	//var_dump($str);// die();
	//  var_dump($data);// die();
	//  var_dump(md5($str));// die();
	$url = "http://llbchongzhi.esaipai.com/IRecharge_Flow";

	$result = curl_post($url, $data);
	
	$data3['return_info']=$value['return_info'].'||'.$result;
	//$where['tid']=$tid;
	$res=$mysql->table('trades')->where($where)->update($data3); 

 
	 
	
	//jiami规则：MD5(UserNumber+OutOrderNumber+ProId+PhoneNumber+PayAmount+StartTime+TimeOut+Remark+UserSystemKey+UserCustomKey，16）

	//时间戳，格式 为 yyyy-MM-dd HH:mm:ss，时间戳与易赛时间不能相差正负 5 分钟。
 
 $resultCode=$result;
	if (strpos($resultCode, "success")) {
	 	$resultList ="提交成功";
	 }  
	if (strpos($resultCode, "attrerr")) {
	 	$resultList ="参数异常";
	 }  
	if (strpos($resultCode, "phoneareaerr")) {
	 	$resultList ="区域错误";
	 }  
	if (strpos($resultCode, "proerr")) {
	 	$resultList ="产品信息不存在";
	 }  
	if (strpos($resultCode, "prostatuserr")) {
	 	$resultList ="未开通此产品充值";
	 }  
	if (strpos($resultCode, "proclose")) {
	 	$resultList ="产品关闭中";
	 } 
	if (strpos($resultCode, "recordKeyerr")) {
	 	$resultList ="验证密钥错误";
	 }
	if (strpos($resultCode, "statuserr")) {
	 	$resultList ="用户接口未开放";
	 } 

	if (strpos($resultCode, "amounterr")) {	 	$resultList ="充值数量异常";	 }
	if (strpos($resultCode, "signerr")) {	 	$resultList ="用户签名数据异常";	 }
	if (strpos($resultCode, "syserr")) {	 	$resultList ="接口内部错误";	 } 
	 

// 状态码 状态描述 备注
// success  成功  验证通过
// attrerr  参数异常  提交参数有误。
// phoneareaerr 区域错误  充值号码与充值产品所在的区域不符
// proerr  产品信息不存在  需要充值的产品信息不存在。
// prostatuserr 未开通此产品充值  用户没有开通该产品充值。
// proclose  产品关闭中  该产品被关闭，目前暂时无法充值。
// recordKeyerr 验证密钥错误  用户提交的订单签名数据异常，一般是用户编号有误或
// 者数据被篡改。
// statuserr  用户接口未开放  用户被关闭，需要联系客服处理。
// amounterr  充值数量异常  不支持多数量送充的产品提交的充值数量大于一。
// signerr  用户签名数据异常  用户签名数据异常，确认数据正确可联系客服处理。
// syserr  接口内部错误  接口内部发生异常，可重新调用接口重试。

	return $resultList;
}


function shuolang($arr,$tid) {

	if(!is_array($arr)) {
		return false;
	}

	$number = $arr['number'];//手机号码
	$user_order_id = $arr['user_order_id'];//订单号
	
	$flow=explode('_',$arr['flow']);//流量类型以及流量大小 0_10
	
	if(empty($flow[1])){
		$res='编码错误';
		return $res;die();
	}
	$sign = strtoupper(md5($number.$flow[1]));//签名
	$scope=$flow[0];
	$flowsize=$flow[1];
	
	
	$requst = "http://123.56.182.32:32001/api/v1/sendOrder?apikey=f905e6ba06a838b3e004173db174f23b&number=$number&flowsize=$flowsize&scope=$scope&user_order_id=$user_order_id&sign=$sign";
	
	
	global $mysql;
	$where['tid']=$tid;
	$value=$mysql->table('trades')->where($where)->find();
	
	$data2['submit_info']=$value['submit_info'].'||'.$requst;
	$data2['submit_time']=date('Y-m-d H:i:s',time());
	$mysql->table('trades')->where($where)->update($data2); 
	
	$restore = json_decode(curl_get($requst));
	
	$data3['return_info']=$value['return_info'].'||'.json_encode($restore);
	$mysql->table('trades')->where($where)->update($data3); 
	
	
	if($restore->errcode != 0){
		$show = $restore->errmsg;
	}else{
		$show = "提交成功";
	}
	//var_dump($requst);die;
	return $show;
	
}

function wanzai($arr,$tid) {

	if(!is_array($arr)) {
		return false;
	}

	$data['username'] = $arr['username'];//用户名
	list($t1, $t2) = explode(' ', microtime());
	$data['timestamp'] = sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
	$data['tradeNo'] = $arr['tradeno'];//订单号
	$data['mobiles'] = $arr['mobiles'];//电话
	$flow=explode('_',$arr['area']);//流量类型以及流量大小 0_10 
	 
	if(empty($flow[1])){
		$res='编码错误';
		return $res;die();
	}
	$data['areaType']=$flow[0];
	$data['spec']=$flow[1];
	$data['effectiveType'] ='tm';
	$data['url'] ='http://link.517.lv/wanzai_url.php?';
	$url = "http://120.27.26.18:9000/pf/api/1.0/order/create-single";
	
	$data['signature'] = md5($data['timestamp'].$data['tradeNo'].$data['mobiles'].$data['spec'].$data['url'].'897075bbbdfc41cea732ed580d752634');
	
	$data = json_encode($data);
	
	global $mysql;
	$where['tid']=$tid;
	$value=$mysql->table('trades')->where($where)->find();
	
	$data2['submit_info']=$value['submit_info'].'||'.$data;
	$data2['submit_time']=date('Y-m-d H:i:s',time());
	$mysql->table('trades')->where($where)->update($data2); 

	$rest = json_decode(newcurl_post($url,$data));
	
	
	$data3['return_info']=$value['return_info'].'||'.json_encode($rest);
	$mysql->table('trades')->where($where)->update($data3); 
	//var_dump($rest);die;
	if($rest->ok == true  || $rest->code == 0){
		$show = "提交成功";
	}else{
		$show = $rest->message;
	}
	return $show;
	
}

function shuolang_find($order){
	$apikey='f905e6ba06a838b3e004173db174f23b';
	$url="http://123.56.182.32:32001/api/v1/orderState?apikey=$apikey&user_order_id=$order";
	$res=json_decode(curl_get($url));
	$newres=$res->order->status;
	if($newres==8){
		$return = '充值失败'.$res->order->msg;
	}elseif($newres==4){
		$return = '充值成功';
	}else{
		$return = '充值进行中';
	}
	return $return;
}


?>