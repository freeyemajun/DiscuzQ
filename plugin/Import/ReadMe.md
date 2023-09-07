### **Discuz! Q 爬虫技术文档**

#### **1. 开发背景**

依据需求，将不同平台的内容导入Discuz! Q站点（以下简称站点），用以丰富站点。

#### **2. 开发步骤**

##### 2.1	 平台调研

平台在进行内容搜索时，是否强制登录了？如强制登录，那DZQ在进行该平台数据抓取时要备有有效的登录态。  

平台在进行内容搜索时，是否有限制搜索频率？如短时间内搜索十次，平台提示“过于频繁，稍后重试”这类的。达到限频后是仅禁用搜索接口？还是禁用发起搜索的ip？还是禁用当前使用的平台账号？限频影响DZQ抓取平台数据数量的上限。  

通过curl可以抓取到平台的哪些内容？拥有登陆态的curl是不是可以获取更多的内容？  

需了解平台内容搜索的分页规则、搜索结果展示规则（按热度/按时间…）等，方便抓取更多的、符合期望的数据。  

##### 2.2	新建平台内容抓取命令行文件

在插件目录下新建一个平台内容抓取任务文件（命令行逻辑）

````
├── plugin -------- 插件目录

│	├──── Import --------爬取数据插件

│	├─────── Console --------命令行逻辑

│			├────────── ImportWeiBoDataCommands.php --------爬取微博平台数据命令

│			├────────── ImportXXXXDataCommands.php --------爬取XXXX平台数据命令

│			├────────── Kernel.php --------命令行配置文件  

│	├─────── Platform --------平台抓取文件

│			├────────── Weibo.php --------微博平台数据抓取逻辑文件

│			├────────── XXXX.php --------XXXX平台数据抓取逻辑文件

│	├─────── config.json --------插件配置文件

│	├─────── ReadMe.md --------操作文档
````

##### 2.3	编写平台抓取逻辑

以`ImportWeiBoDataCommands.php`为例：

接收命令参数：

```php
$optionData = [
  	'topic' => $this->option('topic'),
  	'number' => (int) $this->option('number'),
  	'auto' => $this->option('auto'),
  	'type' => $this->option('type') ?? 0,
  	'interval' => $this->option('interval') ?? 0,
  	'month' => $this->option('month') ?? 0,
  	'week' => $this->option('week') ?? 0,
 	'day' => $this->option('day') ?? 0,
  	'hour' => $this->option('hour') ?? 0,
  	'minute' => $this->option('minute') ?? 0
]; // 基础参数注解详见2.5

/* 如平台需要cookie/token，或者其他，那么需要接收这些值
	 $optionData就要增加参数：
	 'cookie' => $this->option('cookie') ?? '',
	 'token' => $this->option('token') ?? ''
*/
```

抓取平台参数：

```php
public function getPlatformData($parameter)
{
  	$platform = new Weibo();  // Weibo()抓取逻辑详见2.4
  	$data = $platform->main($parameter['topic'], $parameter['number']);
  	return $data; // 这里的 $data 有格式规定，详见2.6.1
}

/* 如平台需要cookie/token，或者其他，那么需要向平台抓取方法中传递参数
	 $data = $platform->main($parameter['topic'], $parameter['number'], $parameter['cookie']);
	 与上一点对应。
*/
```

##### 2.4	数据抓取

通过执行命令，获取到相关数据（关键词、数量等），需封装一个平台数据抓取方法，将关键词等传入方法，进行数据抓取和简单数据排列处理，可参考`plugin/Import/Platform/Weibo.php`（微博内容抓取）。

![平台数据爬取流程图](https://dzqtest-cjw-1300044465.cos.ap-guangzhou.myqcloud.com/picture.png)

##### 2.5	设计导入命令

以`ImportWeiBoDataCommands.php`中的命令为例，`importData:insertWeiBoData`是自定义的命令语句，不可与其他平台抓取命令重复。

```php
php disco importData:insertWeiBoData {--topic=} {--number=} {--auto=} {--type=} {--interval=} {--month=} {--week=} {--day=} {--hour=} {--minute=}

// 以上是基础参数，如某些平台需要传入登录态才能抓取数据，那就要增加类似参数：{--cookie=} {--token=}，增加的参数以平台搜索需求为准，请自行设计
```

基础命令参数注解：

```
topic    关键词
number   导入数量
auto     是否自动导入，1是，0否
type     自动导入类型，1以年为循环自动导入，2以月为循环，3以周为循环，4以日为循环
interval 循环间隔
month    月
week     周
day      天
hour     时
minute   分
```

基础命令参数数值规范：

```
number   1~100的正整数
auto   	 0,1
type     1,2,3,4
interval 大于0的正整数
month    1～12的正整数
week     1～7的正整数，对应星期一～星期日
day      1～31的正整数
hour     1～24的正整数
minute   0～60的正整数
```

命令示例：

```
(1). 即时导入：php disco importData:insertWeiBoData --topic=xxx --number=5
(2). 设置自动导入：
  php disco importData:insertWeiBoData --topic=xxx --number=5 --auto=1 --type=4 --interval=1 --hour=10 --minute=15 # 每一天10:15自动导入
	
  php disco importData:insertWeiBoData --topic=xxx --number=5 --auto=1 --type=4 --interval=2 --hour=10 --minute=15 # 每2天10:15自动导入
  
  php disco importData:insertWeiBoData --topic=xxx --number=5 --auto=1 --type=3 --interval=1 --week=1 --hour=10 # 每周一10:00自动导入
  
  php disco importData:insertWeiBoData --topic=xxx --number=5 --auto=1 --type=3 --interval=2 --week=3 --hour=10 # 每2周的周三10:00自动导入
  
  php disco importData:insertWeiBoData --topic=xxx --number=5 --auto=1 --type=2 --interval=1 --day=3 --hour=10 # 每月3号10:00自动导入
  
  php disco importData:insertWeiBoData --topic=xxx --number=5 --auto=1 --type=2 --interval=2 --day=3 --hour=10 # 每2月的3号10:00自动导入
  
  php disco importData:insertWeiBoData --topic=xxx --number=5 --auto=1 --type=1 --interval=1 --month=11 --day=11 --hour=10 # 每年11月11号10:00自动导入
  
  php disco importData:insertWeiBoData --topic=xxx --number=5 --auto=1 --type=1 --interval=2 --month=12 --day=12 --hour=10 # 每2年的12月12号10:00自动导入
```

如之前设置了定时导入，现在想停止定时导入，可执行下方的命令：

```
php disco autoImport:stop
```



##### 2.6     数据写入

通过各平台抓取方法抓到平台数据，排列组成和既定的形式，传入公共方法`insertCrawlerData`（`app/Import/ImportDataTrait.php`）中，处理成Discuz内容。`insertCrawlerData`方法需要的数据来自各位开发者编写的命令行文件，来自方法`getPlatformData`，该方法名字不可更改。

###### 2.6.1  getPlatformData返参规范

`getPlatformData`方法返参格式应为：

```json
[
    {
        "user " : { #文章作者信息
            "nickname" : "作者昵称，要去掉空格"
            "avatar" : "作者头像"
        },
        "forum" : { #文章主体内容
            "createdAt" : "创建时间"
            "text" : {
                "title" : "标题"
                "text" : "文章内容，混排中的图片不做特殊处理，无需解析到images字段中，话题应展示为：#话题/标签#"
                "position" : "定位位置"
                "topicList" : "数组，['话题名', '话题名']"
            }
            "images" : "数组，非图文混排的图片链接"
            "media" : {
                "video" : "视频链接"
                "audio" : "音频链接"
            }
            "contentMedia" : {
                "videos" : "数组，内容混排中iframe的视频链接"
                "audio" : "数组，内容混排中iframe的音频链接"
            }
            "attachments" : "数组，附件链接"
        },
        "comment": [
            {
                "comment" : { #评论主体内容
                    "text" : {
                        "text" : "评论内容"
                    }
                    "images" : "数组，评论图片链接"
                    "createdAt" : "评论创建时间"
                },
                "user" : { #评论用户信息
                    "nickname" : "昵称，要去掉空格"
                    "avatar" : "头像"
                }
            }
        ]
    }
]
```

返参示例：

```json
{
    "user": {
        "avatar": "https:\/\/wx2.sinaimg.cn\/orj480\/75dda851ly8glmk1rj3mmj2050050dfn.jpg",
        "nickname": "央视网快看"
    },
    "forum": {
        "id": "4686530583725817",
        "mid": "4686530583725817",
        "nickname": "央视网快看",
        "text": {
            "text": "【专家解析！#风力小燃煤缺致东北拉闸限电#】华北电力大学能源互联网研究中心主任曾鸣分析，东北拉闸限电原因：①东北风力减小，风电产出力明显不足；②煤价高企，火电厂存煤不足，还需保障供热燃煤，火电产出力下降。为保障电力系统不崩溃，限制居民用电。#央视网评拉闸限电背后的大棋论# 央视网快看的微博视频",
            "position": "",
            "topicList": ["风力小燃煤缺致东北拉闸限电", "央视网评拉闸限电背后的大棋论"]
        },
        "createdAt": "2021-09-28 22:30:25",
        "images": [],
        "media": {
            "video": "https:\/\/locallimit.us.sinaimg.cn\/ya4djbatlx07QbrASOGs010412005rOM0E010.mp4?label=mp4_ld&template=640x360.25.0&trans_finger=6006a648d0db83b7d9951b3cee381a9c&ori=0&ps=1BVp4ysnknHVZu&Expires=1632885368&ssig=GFyBI3xMRe&KID=unistore,video"
            "audio": ""          
        }
        "contentMedia": {
            "videos": [],
            "audio": []
        }
    },
    "comment": [{
        "comment": {
            "id": "4686662359056426",
            "rootid": "4686662359056426",
            "forumId": "4686530583725817",
            "nickname": "白驼山08",
            "created_at": "2021-09-29 07:14:02",
            "text": {
                "text": "发改委说7月进入下行通道，现在涨成这样。他们水平这么差的？全国用煤量估不到？而且这么缺还不放开煤管票？人民日报和央广网不说说供给侧的情况？没人敢说发改委？",
                "position": "",
                "topicList": []
            }
        },
        "user": {
            "avatar": "https:\/\/wx2.sinaimg.cn\/orj480\/007q8xRBly4gg30q7k9ecj303o03ojr8.jpg",
            "nickname": "白驼山08"
        }
    }, {
        "comment": {
            "id": "4686545611655109",
            "rootid": "4686545611655109",
            "forumId": "4686530583725817",
            "nickname": "fatpig格格",
            "created_at": "2021-09-28 23:30:08",
            "text": {
                "text": "电力能源分配不足。",
                "position": "",
                "topicList": []
            }
        },
        "user": {
            "avatar": "https:\/\/wx1.sinaimg.cn\/orj480\/006w3sxRly8gdqn3y0zh8j30ru0rudgo.jpg",
            "nickname": "fatpig格格"
        }
    }]
}
```

#### **3.	XXXX平台命令行文件示例** 

```php
<?php

namespace Plugin\Import\Console;

use App\Import\ImportDataTrait;
use Discuz\Base\DzqCommand;
use Plugin\Import\Platform\XXXX;

class ImportXXXXDataCommands extends DzqCommand
{
    use ImportDataTrait;
    protected $signature = 'importData:insertXXXXData {--topic=} {--number=} {--auto=} {--type=} {--interval=} {--month=} {--week=} {--day=} {--hour=} {--minute=} {--cookie=} {--userAgent=}';
    protected $description = '执行一个脚本命令,控制台执行[php disco importData:insertXXXXData]';

    protected function main()
    {
        $optionData = [
            'topic' => $this->option('topic'),
            'number' => (int) $this->option('number'),
            'auto' => $this->option('auto'),
            'type' => $this->option('type') ?? 0,
            'interval' => $this->option('interval') ?? 0,
            'month' => $this->option('month') ?? 0,
            'week' => $this->option('week') ?? 0,
            'day' => $this->option('day') ?? 0,
            'hour' => $this->option('hour') ?? 0,
            'minute' => $this->option('minute') ?? 0,
            'cookie' => $this->option('cookie') ?? '',
            'userAgent' => $this->option('userAgent') ?? '',
        ];
        $this->importDataMain($optionData);
        exit;
    }

    public function getPlatformData($parameter)
    {
        $platform = new XXXX(); // 可参考Plugin\Import\Platform\WeiBo
        $data = $platform->main($parameter['topic'], $parameter['number'], $parameter['cookie'], $parameter['userAgent']);
        return $data;
    }
}
```

#### 4. 数据导入命令注意点

即时导入：为避免CPU负载过高、服务器宕机，一次只允许执行一个导入进程。

```
进程占用提示：
----The content import process has been occupied,You cannot start a new process.----
```

自动导入：只能设置一个自动任务，如前后执行了不同的自动导入命令，只保留最后一次执行的自动导入命令参数。

```
自动导入命令参数覆盖提示：
----The automatic import task is written successfully,and overwrites the previous task.----
```

#### 5. 文件进程锁机制

执行导入进程时，将修改文件锁`public/importDataLock.conf`中的导入状态status，当状态为占用中，就不可执行新的导入进程。当前导入进程结束后，状态变更，变更后允许执行新的进程。

自动导入参数记录在文件`public/autoImportDataLock.conf`中，多次执行自动导入命令就是在刷新文件内容，文件中只保留最后一次写入的相关参数。

开发者在撰写平台数据抓取逻辑时，如需要抛出相关报错、中断进程，那么抛错之前需要删除文件锁`importDataLock.conf`(释放进程)，否则进程状态将一直处于占用中，无法开启下一进程。删除文件锁可调用`App\Import\PlatformTrait`中的公共方法`deleteImportLockFile()`。调用形式如下：

```php
namespace Plugin\Import\Platform;

use App\Import\PlatformTrait;

class XXXPlatform
{
		use PlatformTrait;
  
  	public function main()
  	{
    	/* 省略爬取过程代码 */
    	if (empty($data)) {
      	$this->deleteImportLockFile(); // 释放该导入进程
      	throw new \Exception('未获取到数据.');
    	}
      
      return $data;
  	}
}
```

