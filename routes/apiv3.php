<?php



/**
 * Copyright (C) 2020 Tencent Cloud.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

use App\Api\Controller as ApiController;
use \Discuz\Http\RouteCollection;
/**@var RouteCollection $route */

//删除用户和微信用户接口，上线前需去除
//$route->post('/user/delete', 'user.delete', ApiController\UsersV3\DeleteUserController::class);
//$route->post('/user/delete/wechat', 'user.delete.wechat', ApiController\UsersV3\UnbindWechatController::class);
//$route->get('/models', 'models.get', ApiController\UsersV3\GetModelsController::class);
//$route->get('/swagger', 'swagger', ApiController\SwaggerController::class);

$route->withFrequency(function(RouteCollection $route){
    $route->get('/users/pc/wechat/h5.login', 'pc.wechat.h5.login.poll', ApiController\Users\WechatPcLoginPollController::class);
    $route->get('/users/pc/wechat/h5.bind', 'pc.wechat.h5.bind.poll', ApiController\Users\WechatPcBindPollController::class);
    $route->get('/users/pc/wechat/miniprogram.bind', 'pc.wechat.miniprogram.bind.poll', ApiController\Users\MiniProgramPcBindPollController::class);
    $route->get('/users/pc/wechat/miniprogram.login', 'pc.wechat.miniprogram.login.poll', ApiController\Users\MiniProgramPcLoginPollController::class);
    $route->get('/users/pc/wechat.rebind.poll', 'pc.wechat.rebind.poll', ApiController\Users\WechatPcRebindPollController::class);
    $route->get('/dialog/message', 'dialog.message.list', ApiController\Dialog\ListDialogMessageController::class);
    $route->get('/unreadnotification', 'unreadnotification.', ApiController\Notification\UnreadNotificationController::class);
    $route->post('/dialog.update', 'dialog.update', ApiController\Dialog\UpdateUnreadStatusController::class);
},50,30,10);
/*
|--------------------------------------------------------------------------
| 注册/登录
|--------------------------------------------------------------------------
*/
//二维码生成
$route->get('/users/pc/wechat/h5.genqrcode', 'pc.wechat.h5.qrcode', ApiController\Users\WechatH5QrCodeController::class);
$route->get('/users/pc/wechat/miniprogram.genqrcode', 'pc.wechat.miniprogram.genqrcode', ApiController\Users\MiniProgramQrcodeController::class);
$route->get('/users/pc/wechat.rebind.genqrcode', 'pc.wechat.rebind.genqrcode', ApiController\Users\WechatPcRebindQrCodeController::class);
$route->get('/users/mobilebrowser/wechat.poster.genqrcode', 'mobilebrowser.wechat.poster.genqrcode', ApiController\Users\WechatPosterQrCodeController::class);
$route->get('/users/mobilebrowser/wechat/miniprogram.genscheme', 'pc.wechat.miniprogram.login.poll', ApiController\Users\MiniProgramSchemeGenController::class);
$route->get('/users/mobilebrowser/wechat/miniprogram.genbindscheme', 'mobilebrowser.wechat.miniprogram.genbindscheme', ApiController\Users\MiniProgramBindSchemeGenController::class);
$route->get('/users/mobilebrowser/wechat/miniprogram.genparamscheme', 'mobilebrowser.wechat.miniprogram.genparamscheme', ApiController\Users\MiniProgramParamSchemeGenController::class);
//登录
$route->post('/users/username.login', 'username.login', ApiController\Users\LoginController::class);
//注册
$route->withFrequency(function (RouteCollection $route) {
    $route->post('/users/username.register', 'username.register', ApiController\Users\RegisterController::class);
}, 10, 60, 10 * 60);
//控制用户名密码入口是否展示 -> 已迁移至forum接口
//$route->get('/users/username.login.isdisplay', 'username.login.isdisplay', ApiController\UsersV3\LsDisplayController::class);
//用户昵称检测
$route->post('/users/username.check', 'username.check', ApiController\Users\CheckController::class);
//手机号（不区分端）
$route->post('/users/sms.send', 'sms.send', ApiController\Users\SmsSendController::class);
$route->post('/users/sms.verify', 'sms.verify', ApiController\Users\SmsVerifyController::class);
$route->post('/users/sms.login', 'sms.login', ApiController\Users\SmsLoginController::class);
$route->post('/users/sms.bind', 'sms.bind', ApiController\Users\SmsBindController::class);
$route->post('/users/sms.rebind', 'sms.rebind', ApiController\Users\SmsRebindController::class);
$route->post('/users/sms.reset.pwd', 'sms.reset.pwd', ApiController\Users\SmsResetPwdController::class);
//H5登录
$route->get('/users/wechat/h5.oauth', 'wechat.h5.oauth', ApiController\Users\WechatH5OauthController::class);
$route->get('/users/wechat/h5.login', 'wechat.h5.login', ApiController\Users\WechatH5LoginController::class);
$route->get('/users/wechat/h5.bind', 'wechat.h5.bind', ApiController\Users\WechatH5BindController::class);
$route->get('/users/wechat/h5.rebind', 'wechat.h5.rebind', ApiController\Users\WechatH5RebindController::class);
//小程序
$route->post('/users/wechat/miniprogram.login', 'wechat.miniprogram.login', ApiController\Users\WechatMiniProgramLoginController::class);
$route->post('/users/wechat/miniprogram.bind', 'wechat.miniprogram.bind', ApiController\Users\WechatMiniProgramBindController::class);
$route->post('/users/wechat/miniprogram.rebind', 'wechat.miniprogram.rebind', ApiController\Users\WechatMiniProgramRebindController::class);
$route->get('/oauth/wechat/miniprogram/code', 'wechat.mini.program.code', ApiController\Users\WechatMiniProgramCodeController::class);
$route->get('/oauth/qq', 'qq.login', ApiController\Users\QQLoginController::class);
$route->get('/oauth/qq/user', 'qq.user', ApiController\Users\QQUserController::class);

//手机浏览器（微信外）登录并绑定微信
//$route->get('/users/mobilebrowser/wechat/h5.bind', 'mobilebrowser.wechat.h5.bind', ApiController\UsersV3\MiniProgramSchemeGenController::class);
//$route->post('/users/mobilebrowser/username.login', 'mobilebrowser.username.login', ApiController\UsersV3\MobileBrowserLoginController::class);
//$route->get('/users/mobilebrowser/wechat/miniprogram.bind', 'mobilebrowser.wechat.miniprogram.bind', ApiController\UsersV3\MiniProgramSchemeGenController::class);
//过渡开关打开微信绑定自动创建账号
$route->get('/users/wechat/transition/username.autobind', 'wechat.transition.username.autobind', ApiController\Users\WechatTransitionAutoRegisterController::class);
$route->post('/users/wechat/transition/sms.bind', 'wechat.transition.sms.bind', ApiController\Users\WechatTransitionBindSmsController::class);
//登录页设置昵称
$route->post('/users/nickname.set', 'users.nickname.set', ApiController\Users\NicknameSettingController::class);
//前台扩展字段
// 查询扩展字段列表（用户注册后显示）
$route->get('/user/signinfields.list', 'user.signinfields.list', ApiController\SignInFields\ListUserSignInController::class);
// 用户首次提交扩展字段信息或者被驳回之后再次提交
$route->post('/user/signinfields.create', 'user.signinfields.create', ApiController\SignInFields\CreateUserSignInController::class);

//帖子查询
$route->get('/thread.detail', 'thread.detail', ApiController\Threads\ThreadDetailController::class);
$route->get('/thread.list', 'thread.list', ApiController\Threads\ThreadListController::class);
$route->get('/thread.stick', 'thread.stick', ApiController\Threads\ThreadStickController::class);
$route->get('/thread.poster', 'thread.poster', ApiController\Threads\ThreadPosterController::class);
$route->get('/thread.likedusers', 'thread.likedusers', ApiController\Threads\ThreadLikedUsersController::class);
$route->get('/tom.detail', 'tom.detail', ApiController\Threads\SelectTomController::class);
$route->get('/thread.recommends', 'thread.recommends', ApiController\Threads\ThreadRecommendController::class);
$route->get('/thread.typelist', 'thread.typelist', ApiController\Threads\ThreadTypeListController::class);
//帖子变更
$route->post('/thread.create', 'thread.create', ApiController\Threads\CreateThreadController::class);
$route->post('/thread.delete', 'thread.delete', ApiController\Threads\DeleteThreadController::class);
$route->post('/thread.update', 'thread.update', ApiController\Threads\UpdateThreadController::class);
$route->post('/tom.delete', 'tom.delete', ApiController\Threads\DeleteTomController::class);
$route->post('/tom.update', 'tom.update', ApiController\Threads\UpdateTomController::class);
$route->post('/thread/video', 'threads.video', ApiController\Threads\CreateThreadVideoController::class);

//首页配置接口
$route->get('/forum', 'forum.settings', ApiController\Settings\ForumSettingsController::class);

$route->post('/thread.share', 'thread.share', ApiController\Threads\ThreadShareController::class);
$route->post('/goods/analysis', 'goods.analysis', \Plugin\Shop\Controller\ResourceAnalysisGoodsController::class);

$route->post('/attachments', 'attachments.create', ApiController\Attachment\CreateAttachmentController::class);
$route->get('/emoji', 'emoji.list', ApiController\Emoji\ListEmojiController::class);
$route->get('/follow.list', 'follow.list', ApiController\Users\ListUserFollowController::class);
$route->post('/follow.create', 'follow.create', ApiController\Users\CreateUserFollowController::class);
$route->post('/follow.delete', 'follow.delete', ApiController\Users\DeleteUserFollowController::class);

//$route->get('/groups.resource', 'groups.resource', ApiController\GroupV3\ResourceGroupsController::class);//已弃用
$route->get('/topics.list', 'topics.list', ApiController\Topic\TopicListController::class);
$route->get('/users.list', 'users.list', ApiController\Users\UsersListController::class);
$route->post('/order.create', 'order.create', ApiController\Order\CreateOrderController::class);
$route->get('/order.detail', 'orders.resource.v2', ApiController\Order\ResourceOrderController::class);
$route->post('/trade/notify/wechat', 'trade.notify.wechat', ApiController\Trade\Notify\WechatNotifyController::class);

$route->withFrequency(function (RouteCollection $route) {
    $route->post('/trade/pay/order', 'trade.pay.order', ApiController\Trade\PayOrderController::class);
}, 3, 30, 10);

$route->get('/categories', 'categories', ApiController\Category\ListCategoriesController::class);
$route->get('/categories.thread', '/categories.thread', ApiController\Category\ListCategoriesThreadController::class);
$route->get('/posts.list', 'posts', ApiController\Posts\ListPostsController::class);
$route->post('/posts.update', 'posts.update', ApiController\Posts\UpdatePostController::class);
$route->post('/posts.create', 'posts', ApiController\Posts\CreatePostController::class);
$route->get('/posts.detail', 'posts.resource', ApiController\Posts\ResourcePostController::class);
$route->get('/posts.reply', 'posts.reply', ApiController\Posts\ResourcePostReplyController::class);
$route->get('/posts.postion', 'posts.postion', ApiController\Posts\PositionPostsController::class);

//用户
$route->post('/users/real', 'users.real', ApiController\Users\RealUserController::class);
$route->get('/wallet/user', 'wallet.wallet', ApiController\Wallet\ResourceUserWalletController::class);

/*
|--------------------------------------------------------------------------
| Notification
|--------------------------------------------------------------------------
*/
$route->get('/notification', 'notification.list', ApiController\Notification\ListNotificationController::class);
$route->post('/notification.delete', 'notification.delete', ApiController\Notification\DeleteNotificationController::class);


$route->get('/dialog', 'dialog.list', ApiController\Dialog\ListDialogController::class);
$route->post('/dialog.create', 'dialog.create', ApiController\Dialog\CreateDialogController::class);
$route->post('/dialog/message.create', 'dialog.message.create', ApiController\Dialog\CreateDialogMessageController::class);
$route->post('/dialog.delete', 'dialog.delete', ApiController\Dialog\DeleteDialogController::class);
$route->get('/dialog.record', 'dialog.record', ApiController\Dialog\DialogRecordController::class);

$route->post('/users/pay-password/reset', '', ApiController\Users\ResetPayPasswordController::class);
$route->post('/users/update.user', 'users.update', ApiController\Users\UpdateUsersController::class);


$route->get('/signature', 'signature', ApiController\Qcloud\CreateVodUploadSignatureController::class);
$route->post('/threads/operate', 'threads.operate', ApiController\Threads\OperateThreadController::class);
$route->post('/posts.reward', 'posts.reward', ApiController\Posts\CreatePostRewardController::class);


//个人中心
$route->get('/wallet/log', 'wallet.log.list', ApiController\Wallet\ListUserWalletLogsController::class);
$route->get('/wallet/cash', 'wallet.cash.list', ApiController\Wallet\ListUserWalletCashController::class);
$route->post('/users/sms.reset.pay.pwd', 'sms.reset.pay.pwd', ApiController\Users\SmsResetPayPwdController::class);
$route->post('/wallet/cash', 'wallet.cash.create', ApiController\Wallet\CreateUserWalletCashController::class);
//$route->get('/favorites', 'favorites', ApiController\ThreadsV3\ListFavoritesController::class);//无使用
$route->post('/users/background', 'user.upload.background', ApiController\Users\UploadBackgroundController::class);
$route->get('/user', 'user.resource', ApiController\Users\ProfileController::class);
$route->post('/users/update.mobile', 'update.mobile', ApiController\Users\UpdateMobileController::class);
$route->post('/users/avatar', 'user.upload.avatar', ApiController\Users\UploadAvatarController::class);


$route->get('/users/deny.list', 'user.deny.list', ApiController\Users\ListDenyUserController::class);
$route->post('/users/deny.create', 'user.deny', ApiController\Users\CreateDenyUserController::class);
$route->post('/users/deny.delete', 'user.delete.deny', ApiController\Users\DeleteDenyUserController::class);

$route->get('/tom.permissions', 'tom.permissions', ApiController\Group\TomPermissionsController::class);
//$route->get('/threads.paid', 'threads.paid', ApiController\UsersV3\ListPaidThreadsController::class);//无使用

$route->post('/user/thread.stick', 'user.thread.stick', ApiController\Threads\ThreadUserStickController::class);

//待使用接口
$route->post('/reports', 'reports.create', ApiController\Report\CreateReportsController::class);
$route->get('/redpacket.resource', 'redpacket.resource', ApiController\RedPacket\ResourceRedPacketController::class);

// 邀请invite
$route->get('/invite.users.list', 'invite.users.list', ApiController\Invite\InviteUsersListController::class);
$route->get('/invite.link.create', 'invite.link.create', ApiController\Invite\CreateInviteLinkController::class);

// 个人中心-站点信息-我的权限
$route->get('/group.permission.list', 'group.permission.list', ApiController\Group\GroupPermissionListController::class);

//附件分享
$route->get('/attachment.share', 'attachment.share', ApiController\Attachment\ShareAttachmentController::class);
$route->get('/attachment.download', '/attachment.download', ApiController\Attachment\DownloadAttachmentController::class);

//生成jssdk签名
$route->get('/offiaccount/jssdk', 'offiaccount.jssdk', ApiController\Wechat\OffIAccountJSSDKController::class);

$route->get('/test', 'thread.test', ApiController\Threads\TestController::class);

$route->get('/view.count', 'view.count', ApiController\Threads\ViewCountController::class);

$route->withFrequency(function ($route) {
    //上传文件临时参数
    $route->post('/coskey', 'coskey', ApiController\Attachment\CoskeyAttachmentController::class);
//记录前端上传文件的参数
    $route->post('/attachment.relation', 'attachment.relation', ApiController\Attachment\RelationAttachmentController::class);
}, 50, 60, 10 * 60);

//用户投票
$route->post('/vote.thread', 'vote.thread', ApiController\Threads\VoteThreadController::class);

$route->get('/check.user.get.redpacket', 'check.user.get.redpacket', ApiController\Threads\CheckUserGetRedpacketController::class);

//用户组升级列表
$route->get('/upgrade.group', 'upgrade.group', ApiController\Group\ListPayGroupsController::class);

$route->get('/plugin/list', 'plugin', ApiController\Plugin\PluginListController::class);
$route->get('/plugin/settinginfo', 'plugin.settinginfo', ApiController\Plugin\GetSettingController::class);
