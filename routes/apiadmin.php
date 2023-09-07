<?php
/**@var Discuz\Http\RouteCollection $route */
use App\Api\Controller as ApiController;

$route->post('/login', 'login', ApiController\Users\AdminLoginController::class);

$route->get('/reports', 'reports.list', ApiController\Report\ListReportsController::class);
$route->post('/reports/batch', 'reports.batchUpdate', ApiController\Report\BatchUpdateReportsController::class);
$route->post('/reports/delete', 'reports.batchDelete', ApiController\Report\BatchDeleteReportsController::class);
$route->get('/settings', 'settings.list', ApiController\Settings\ListSettingsController::class);
$route->post('/settings/logo', 'settings.upload.logo', ApiController\Settings\UploadLogoController::class);
$route->post('/settings/delete.logo', 'settings.delete.logo', ApiController\Settings\DeleteLogoController::class);
$route->get('/siteinfo', 'site.info', ApiController\SiteInfoController::class);
$route->post('/settings.create','settings.create',ApiController\Settings\SetSettingsController::class);

//用户组
$route->post('/groups.create', 'groups.create', ApiController\Group\CreateGroupController::class);
$route->get('/groups.list', 'groups.list', ApiController\Group\ListGroupsController::class);
$route->post('/groups.batchupdate', 'groups.batchupdate', ApiController\Group\BatchUpdateGroupController::class);
$route->post('/groups.batchdelete', 'groups.batchdelete', ApiController\Group\BatchDeleteGroupsController::class);
$route->post('/users/update.user', 'users.admin', ApiController\Users\UpdateAdminController::class);
$route->post('/users/examine', 'users.examine', ApiController\Users\UpdateUsersStatusController::class);

// 财务
$route->get('/users.wallet.logs', 'users.wallet.logs', ApiController\Wallet\UsersWalletLogsListController::class);
$route->get('/users.order.logs', 'users.order.logs', ApiController\Order\UsersOrderLogsListController::class);
$route->get('/users.cash.logs', 'users.cash.logs', ApiController\Wallet\UsersCashLogsListController::class);
$route->post('/wallet.cash.review', 'wallet.cash.review', ApiController\Wallet\UserWalletCashReviewController::class);
$route->get('/statistic.finance', 'statistic.finance', ApiController\Statistic\FinanceProfileController::class);
$route->get('/statistic.financeChart', 'statistic.financeChart', ApiController\Statistic\FinanceChartController::class);
$route->get('/user.wallet', 'wallet.user', ApiController\Wallet\ResourceUserWalletAdminController::class);
$route->post('/update.user.wallet', 'update.wallet.user', ApiController\Wallet\UpdateUserWalletController::class);

//内容分类
$route->get('/categories', 'categories', ApiController\Category\AdminListCategoriesController::class);
$route->post('/categories.create', 'categories.create', ApiController\Category\CreateCategoriesController::class);
$route->post('/categories.update', 'categories.update', ApiController\Category\BatchUpdateCategoriesController::class);
$route->post('/categories.delete', 'categories.delete', ApiController\Category\BatchDeleteCategoriesController::class);

$route->post('/permission.update', 'permission.update', ApiController\Permission\UpdateGroupPermissionController::class);

$route->get('/groups.resource', 'groups.resource', ApiController\Group\ResourceGroupsController::class);
//注册扩展
$route->get('/signinfields.list', 'signinfields.list', ApiController\SignInFields\ListAdminSignInController::class);
$route->post('/signinfields.create', 'signinfields.create', ApiController\SignInFields\CreateAdminSignInController::class);
$route->get('/user/signinfields', 'user.signinfields.resource', ApiController\SignInFields\ResourceUserSignInController::class);

$route->post('/threads.batch', 'threads.batch', ApiController\Threads\BatchThreadsController::class);
//审核主题列表
$route->get('/manage.thread.list', 'manage.thread.list', ApiController\Admin\ManageThemeList::class);
//审核评论列表
$route->get('/manage.posts.list', 'manage.posts.list', ApiController\Admin\ManagePostList::class);
//提交审核
$route->post('/manage.submit.review', 'manage.review', ApiController\Admin\ManageSubmitReview::class);
//话题管理
$route->get('/topics.list', 'topics.list', ApiController\Topic\AdminTopicListController::class);
$route->post('/topics.batch.update', 'topics.batch.update', ApiController\Topic\BatchUpdateTopicController::class);
$route->post('/topics.batch.delete', 'topics.batch.delete', ApiController\Topic\BatchDeleteTopicController::class);

$route->get('/statistic/firstChart', 'statistic/firstChart', ApiController\Statistic\FirstChartController::class);

//用户
$route->get('/export/users', 'export.users', ApiController\Users\ExportUserController::class);
$route->post('/users/avatar', 'user.upload.avatar', ApiController\Users\UploadAvatarsController::class);
$route->post('/delete/users/avatar', 'user.upload.avatar', ApiController\Users\DeleteAvatarController::class);
$route->get('/users', 'users.list', ApiController\Users\ListUserScreenController::class);
$route->get('/user', 'user.resource', ApiController\Users\AdminProfileController::class);
$route->get('/users.invite', 'users.invite', ApiController\Users\ListUserInviteController::class);

//内容过滤
$route->post('/stopwords.batch', 'stopwords.batch', ApiController\StopWords\BatchCreateStopWordsController::class);
$route->get('/stopwords.list', 'stopwords.list', ApiController\StopWords\ListStopWordsController::class);
$route->post('/stopwords.delete', 'stopwords.delete', ApiController\StopWords\DeleteStopWordController::class);

//管理端站点设置
$route->get('/forum', 'forum.settings', ApiController\Settings\AdminForumSettingsController::class);

//消息模板
$route->get('/notification/tpl', 'notification.tpl.list', ApiController\Notification\ListNotificationTplController::class);
$route->get('/notification/tpl/detail', 'notification.tpl.detail', ApiController\Notification\ResourceNotificationTplController::class);
$route->post('/notification/tpl/update', 'notification.tpl.update', ApiController\Notification\UpdateNotificationTplController::class);

$route->get('/cache.delete', 'cache.delete', ApiController\Cache\DeleteCacheController::class);
$route->get('/sequence.list', 'sequence.list', ApiController\Settings\ListSequenceController::class);
$route->post('/sequence.update', 'sequence', ApiController\Settings\UpdateSequenceController::class);
$route->post('/refresh.token', 'refresh.token', ApiController\Oauth2\RefreshTokenController::class);

$route->get('/recommend.users', 'recommend.users', ApiController\Recommend\RecommendedUserListController::class);
$route->get('/recommend.topics', 'recommend.topics', ApiController\Recommend\RecommendedTopicListController::class);

// 判断是否已配置腾讯云  CheckQcloudController
$route->get('/checkQcloud', 'checkQcloud',  ApiController\CheckQcloudController::class);

//邀请朋友生成code
$route->get('/adminInvite.link.create','invite.link.create',ApiController\Invite\CreateInviteLinkAdminController::class);
$route->get('/stopWords/export', 'stopWords.export', ApiController\StopWords\ExportStopWordsController::class);

//监听定时任务
$route->get('/monitor/system/task', 'monitor.system.task', ApiController\System\MonitorSystemTaskController::class);

$route->post('/open.view.count', 'open.view.count', ApiController\Settings\OpenViewCountController::class);

// 表情列表
$route->get('/emoji.list', 'emoji.list', ApiController\Emoji\ListAdminEmojiController::class);
//一键开启/关闭帖子敏感数据
$route->post('/thread.optimize', 'thread.optimize', ApiController\Threads\ThreadOptimizeController::class);

//插件后台控制接口
$route->post('/plugin/settings', 'plugin.settings', ApiController\Plugin\SettingController::class);
$route->post('/plugin/permission.switch', 'plugin.permission.switch', ApiController\Plugin\GroupPermissionController::class);
$route->get('/plugin/permissionlist', 'plugin.permissionlist', ApiController\Plugin\GetGroupPermissionsController::class);
$route->get('/plugin/settinginfo', 'plugin.settinginfo', ApiController\Plugin\GetSettingAdminController::class);
$route->get('/plugin/list', 'plugin.list', ApiController\Plugin\PluginListAdminController::class);
$route->post('/plugin/uploadimage', 'plugin.uploadimage', ApiController\Plugin\PluginUploadImageController::class);
$route->post('/plugin/deleteimage', 'plugin.deleteimage', ApiController\Plugin\PluginDeleteImageController::class);
$route->post('/plugin/upload', 'plugin.upload', ApiController\Plugin\PluginUploadController::class);
$route->post('/plugin/operate', 'plugin.operate', ApiController\Plugin\PluginOperateController::class);

$route->post('/open.api.log', 'open.api.log', ApiController\Settings\OpenApiLogController::class);

$route->get('/thread.stick.sort', 'thread.stick.sort', ApiController\Threads\ThreadStickSortController::class);
$route->post('/stick.sort.set', 'stick.sort.set', ApiController\Threads\ThreadStickSortSetController::class);

$route->get('/purge.cdn.cache', 'purge.cdn.cache', ApiController\Threads\PurgeCdnCacheController::class);
