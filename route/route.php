<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

Route::get('think', function () {
    return 'hello,ThinkPHP5!';
});

Route::get('hello/:name', 'index/hello');

//存活检测
Route::rule('moppet/appver', 'api/api/appver', 'get|post');

//首页
Route::rule('page/home', 'api/page/home', 'get|post');


//判断用户能否参与赞助商红包抽奖
Route::rule('lottery/checkJoinPower', 'api/lottery/checkJoinPower', 'get|post');


//抽奖券页
Route::rule('page/coupon', 'api/page/coupon', 'get|post');
//每日签到页
Route::rule('coupon/dailySignPage', 'api/coupon/dailySignPage', 'get|post');
//签到提醒开关
Route::rule('coupon/signRemindSwitch', 'api/coupon/signRemindSwitch', 'get|post');
//抽奖券列表
Route::rule('coupon/lotteryCouponList', 'api/coupon/lotteryCouponList', 'get|post');
//获取趣味问答问题
Route::rule('coupon/getQuestion', 'api/coupon/getQuestion', 'get|post');
//提交趣味问答答案
Route::rule('coupon/submitQuestionAnswer', 'api/coupon/submitQuestionAnswer', 'get|post');
//改为一日3题后，获取趣味问答问题
Route::rule('coupon/getQuestionNew', 'api/coupon/getQuestionNew', 'get|post');
//改为一日3题后，提交趣味问答答案
Route::rule('coupon/submitQuestionAnswerNew', 'api/coupon/submitQuestionAnswerNew', 'get|post');


//在线竞猜列表
Route::rule('guess/ongoingList', 'api/guess/ongoingList', 'get|post');
//参与竞猜
Route::rule('guess/join', 'api/guess/join', 'get|post');
//往期竞猜列表
Route::rule('guess/pastList', 'api/guess/pastList', 'get|post');
//金币兑换奖牌
Route::rule('guess/exchangeMedals', 'api/guess/exchangeMedals', 'get|post');
//奖牌兑换余额红包列表
Route::rule('guess/redPacketList', 'api/guess/redPacketList', 'get|post');
//奖牌兑换余额
Route::rule('guess/exchangeBalance', 'api/guess/exchangeBalance', 'get|post');
//奖牌流水
Route::rule('guess/medalsFlowList', 'api/guess/medalsFlowList', 'get|post');


//挑战首页
Route::rule('challenge/index', 'api/challenge/index', 'get|post');
//挑战详情
Route::rule('challenge/challengeInfo', 'api/challenge/challengeInfo', 'get|post');
//用户可以发起挑战
Route::rule('challenge/mode', 'api/challenge/mode', 'get|post');
//发起挑战
Route::rule('challenge/initiate', 'api/challenge/initiate', 'get|post');
//分享挑战信息
Route::rule('challenge/shareInfo', 'api/challenge/shareInfo', 'get|post');
//参与挑战
Route::rule('challenge/join', 'api/challenge/join', 'get|post');
//提交步数
Route::rule('challenge/submitStep', 'api/challenge/submitStep', 'get|post');
//挑战币流水
Route::rule('challenge/challengeCoinFlowList', 'api/challenge/challengeCoinFlowList', 'get|post');


return [

];
