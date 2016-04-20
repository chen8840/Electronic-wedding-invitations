<?php
require_once('config.php');
require_once('functions.php');

function getAccessToken($code) {
	$url = sprintf(ACCESS_TOKEN_URL.'?appid=%s&secret=%s&code=%s&grant_type=authorization_code',APP_ID,APP_SECRET,$code);
	return curl_get($url);
}

function getUserInfo($access_token, $openid) {
	$url = sprintf(GET_USERINFO_URL.'?access_token=%s&openid=%s',$access_token,$openid);
	return curl_get($url);
}

$accessTokenObj = json_decode(getAccessToken($_GET['code']), true);
var_dump($accessTokenObj);
if(!empty($accessTokenObj['access_token']) && !empty($accessTokenObj['openid'])) {
	$userinfo = json_decode(getUserInfo($accessTokenObj['access_token'],$accessTokenObj['openid']), true);
} else {
	exit('can not get access_token');
}

if(!empty($userinfo['nickname']) && !empty($userinfo['headimgurl'])) {
	$url = sprintf(FRONT_PAGE_URL.'?nickname=$s&headingurl=%s',$userinfo['nickname'],$userinfo['headimgurl']);
	header('Location:'.FRONT_PAGE_URL.$url);
} else {
	eixt('can note get userinfo');
}