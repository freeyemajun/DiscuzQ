<?php

namespace App\Console\Commands;
use Carbon\Carbon;
use Discuz\Console\AbstractCommand;
use Discuz\Foundation\Application;
use App\Settings\SettingsRepository;
use App\Commands\ChatGpt\ChatGpt;
use App\Models\ChatGptKernel;
use App\Models\ChatGptOffMsg;
use App\Models\Post;

class ChatGptCommand extends AbstractCommand
{
    protected $signature = 'ChatGpt';

    protected $description = 'ChatGpt后台';

    protected $app;
    protected $ChatGpt;

    /**
     * AvatarCleanCommand constructor.
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        parent::__construct();

        $this->app = $app;
        $this->ChatGpt = new ChatGpt();
    }

    public function handle()
    {
        $settings=app(SettingsRepository::class);
        $aiuid = $settings->get('aiuid', 'chatgpt');
        $this->info('开始执行');
        while (true){
            $Kernels = ChatGptKernel::query()->where('status', 0)->orderBy('id', 'desc')->get()->toArray();
            foreach ($Kernels as $do){
                ChatGptKernel::query()->where('id', $do['id'])->update(['status' => 2]);//任务状态改为执行中
                $this->info($do['toid']);
                switch ($do['msg_type']) {
                    case 0:
                        $arr = array();
                        $t = Post::query()->where('thread_id', $do['toid'])->where('is_first', 1)->first();
                        $t['content']  = htmlspecialchars_decode($t['content']);//把一些预定义的 HTML 实体转换为字符
                        $t['content']  = str_replace("&nbsp;","",$t['content']);//将空格替换成空
                        $t['content']  = strip_tags($t['content']);//函数剥去字符串中的 HTML、XML 以及 PHP 的标签,获取纯文本内容
                        $this->info($t['content']);

                        array_push($arr,['role'=> 'user', 'content'=> $t['content']]);
                        var_dump($arr);
                        $this->ChatGpt->retid($do['toid'],$arr,$do['id']);
                    case 1:
                        $arr = array();
                        $postdata = Post::query()->where('thread_id', $do['toid'])->get()->toArray();
                        foreach ($postdata as $v){
                            if ($v['is_approved'] == 1 && $v['deleted_at'] == null) {
                                $v['content'] = htmlspecialchars_decode($v['content']);//把一些预定义的 HTML 实体转换为字符
                                $v['content'] = str_replace("&nbsp;","",$v['content']);//将空格替换成空
                                $v['content'] = strip_tags($v['content']);//函数剥去字符串中的 HTML、XML 以及 PHP 的标签,获取纯文本内容
                                $this->info($v['content']);

                                if ($v['user_id'] == $aiuid){
                                    array_push($arr,['role'=> 'assistant', 'content'=> $v['content']]);
                                }else{
                                    array_push($arr,['role'=> 'user', 'content'=> $v['content']]);
                                }
                            }
                        };
                        $this->ChatGpt->retid($do['toid'],$arr,$do['id']);
                    case 2:
                        $arr = array();
                        $postdata = ChatGptOffMsg::query()->where('toid', $do['toid'])->get()->toArray();
                        foreach ($postdata as $v){
                            $this->info($v['msg']);
                            array_push($arr,['role'=> $v['role'], 'content'=> $v['msg']]);
                        }
                        $this->info(json_encode($v));
                        $this->ChatGpt->reoff($do['toid'],$arr,$do['id']);
                }

                ChatGptKernel::query()->where('id', $do['id'])->update(['status' => 1]);//任务状态改为完成
            }
            sleep(5);
        }
        $this->info('处理结束~');
    }
}
