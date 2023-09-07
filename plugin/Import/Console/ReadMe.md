#### 1. 数据导入命令参数说明

```
topic    关键词
number   导入数量
cookie   平台登录态 
auto     是否自动导入，1是，0否
type     自动导入类型，1以年为循环自动导入，2以月为循环，3以周为循环，4以日为循环
interval 循环间隔，1、2、3···
month    月
week     周
day      天
hour     时
minute   分
url      爬取网站的地址(目前仅Discuz! X网站内容爬取在使用)
port     端口
userAgent   http请求头之一，伪造浏览器请求
articleUrl  微信公众号文章链接
```

##### 1.1 cookie平台登录态获取

登录平台（以谷歌浏览器为例）->随意浏览页面->按键F12->点击“network”->点击“Fetch/XHR”->任意点击左侧某一请求->查看“Headers”->获取cookie

![](https://discuz.chat/assets/import_data_cookie_example.png)

##### 1.2 userAgent 获取

登录平台（以谷歌浏览器为例）->随意浏览页面->按键F12->点击“network”->点击“Fetch/XHR”->任意点击左侧某一请求->查看“Headers”->获取User-Agent

![](https://discuz.chat/assets/import_data_ua_example.png)

##### 1.3 articleUrl公众号文章链接获取（仅限微信公众号内容导入）

点击某一篇公众号文章->进入文章详情->点击右上角“···”->再“复制链接”

![](https://discuz.chat/assets/import_data_wx_example.png)

#### 2.  数据导入命令示例

即时导入：`topic，number`为必传参数。

自动导入：`topic，number，auto，type，interval`为必传参数，其他参数根据自己设置的时间分别配置。
````
以天为自动导入周期：topic，number，auto，type，interval，hour为必传。如需具体到分钟，再传minute。
以周为自动导入周期：topic，number，auto，type，interval，week，hour为必传。如需具体到分钟，再传minute。
以月为自动导入周期：topic，number，auto，type，interval，day，hour为必传。如需具体到分钟，再传minute。
以年为自动导入周期：topic，number，auto，type，interval，month，day，hour为必传。如需具体到分钟，再传minute。
````
##### 2.1 微博

```php
(1). 即时导入：php disco importData:insertWeiBoData --topic=xxx --number=5
(2). 自动导入：
  php disco importData:insertWeiBoData --topic=xxx --number=5 --auto=1 --type=4 --interval=1 --hour=10 --minute=15 # 每一天10:15自动导入
   
  php disco importData:insertWeiBoData --topic=xxx --number=5 --auto=1 --type=4 --interval=2 --hour=10 --minute=15 # 每2天10:15自动导入
  
  php disco importData:insertWeiBoData --topic=xxx --number=5 --auto=1 --type=3 --interval=1 --week=1 --hour=10 # 每周一10:00自动导入
  
  php disco importData:insertWeiBoData --topic=xxx --number=5 --auto=1 --type=3 --interval=2 --week=3 --hour=10 # 每2周的周三10:00自动导入
  
  php disco importData:insertWeiBoData --topic=xxx --number=5 --auto=1 --type=2 --interval=1 --day=3 --hour=10 # 每月3号10:00自动导入
  
  php disco importData:insertWeiBoData --topic=xxx --number=5 --auto=1 --type=2 --interval=2 --day=3 --hour=10 # 每2月的3号10:00自动导入
  
  php disco importData:insertWeiBoData --topic=xxx --number=5 --auto=1 --type=1 --interval=1 --month=11 --day=11 --hour=10 # 每年11月11号10:00自动导入
  
  php disco importData:insertWeiBoData --topic=xxx --number=5 --auto=1 --type=1 --interval=2 --month=12 --day=12 --hour=10 # 每2年的12月12号10:00自动导入
```
示例：

```
php disco importData:insertWeiBoData --topic=奥运会 --number=7
```

其他平台设置自动导入的格式基本同“微博”一致，以下不再赘述。

##### 2.2 贴吧

贴吧的防爬机制，导致抓取数据多次后跳转贴吧图片验证，无法继续抓取内容，数据导入将会中断。

即时导入：topic，number，cookie为必传参数。

自动导入：topic，number，cookie，auto，type，interval为必传参数。

```php
(1). 即时导入：php disco importData:insertTieBaData --topic=xxx --number=5 --cookie='登录态'
(2). 自动导入：参考2.1微博“自动导入”命令。另，设置自动导入需考虑cookie的有效性，如cookie失效，自动导入将会中断。
```
示例：
```
php disco importData:insertTieBaData --topic=教师节 --number=5 --cookie='PSTM=1587891258; BIDUPSID=3F79A40ACB264FC2463BD664E4424E43; H_WISE_SIDS=148865_156515_155318_146732_156245_152571_148423_154189_156944_155344_158026_157172_157782_154213_157814_158638_157188_158421_157163_158127_157696_154639_110085; Hm_lvt_287705c8d9e2073d13275b18dbd746dc=1604543192; Hm_lpvt_287705c8d9e2073d13275b18dbd746dc=1604543192; BAIDUID=202EDC53C2B76B87754B7BE99F105E27:FG=1; __yjs_duid=1_ac1b255e774d3ea69599ad50b6ef772e1620219271464; tb_as_data=ed77f9cc998872b5f68d207c69a515d17261ada102e6129e72e2bff15b4ef1250065c6f71bcd64ae8ef1dfba55eb7ec8f8eb1b1fd4d877a2fedb7d76d24118e8496afe971b3673282cbaddd126accaf025be44cfdc3f8bf5908fa6fe6b215700fedb4bb44ebcb67b1ab3b0d0669cc18e; BDORZ=B490B5EBF6F3CD402E515D22BCDA1598; delPer=0; BAIDUID_BFESS=C9C7C07E6E8B0DA8AE529F3D166C12B8:FG=1; BDRCVFR[S4-dAuiWMmn]=I67x6TjHwwYf0; BDRCVFR[feWj1Vr5u3D]=I67x6TjHwwYf0; PSINO=7; H_PS_PSSID=34948_34068_31660_34711_34595_34584_34504_34710_34916_34964_34579_34812_26350_34970_34868_34791; BA_HECTOR=0lag048g25042k84pe1go4u6u0q; Hm_lvt_98b9d8c2fd6608d564bf2ac2ae642948=1635942427; st_key_id=17; BAIDU_WISE_UID=wapp_1635942427440_781; USER_JUMP=-1; video_bubble68000159=1; BCLID=8984529760757669079; BDSFRCVID=JptOJeC62GtCkHcHAAuvhbRgHNpM4lbTH6ORPe6GvpW6LKrgGwN6EG0PfU8g0Ku-F2KSogKK3gOTH4PF_2uxOjjg8UtVJeC6EG0Ptf8g0M5; H_BDCLCKID_SF=tR4q_K_KfCK3fP36q4n2btCyhl-X5-Cs3mTi2hcH0KLKjqjCKMtby-It3MR35qjMLmolah3wJxb1MRLRLx7Hyj3XDJ0HB-JIteOjbp5TtUJDJKnTDMRhqfCOQG3yKMnitKv9-pP2LpQrh459XP68bTkA5bjZKxtq3mkjbPbDfn028DKuD6tWDjO-jN_s543KHDrKBRbaHJOoDDk9yTOcy4LdjG5mBtcNb6c7aPTjXbooOnT4MJKbjPQy3-Aq54RGbbOqP-jW5Ta-nQy0b7JOpkRbUnxy50rQRPH-Rv92DQMVU52QqcqEIQHQT3m5-5bbN3ht6IDtnADVILMfCvHebDk-4QEbbQH-UnLqMkD02OZ0l8Ktt58oxP9-U712t-g-xniaRjxyI6x_-omWIQHDnOCLPnVD4KKJxHxKWeIJo5DcPqjv-hUJiB5JMBan7_UJIXKohJh7FM4tW3J0ZyxomtfQxtNRJ0DnjtpChbC_wDj0Ke5jXepJf-K68a5Qy0nObHJOoDDkmb53cy4LdjGK8ybQrB5r7QlQG2RnDVR3y5R545hD73-Aq54RbBaniol7l-Pc2OxQxj4vkQfbQ0-chqP-jW26abn5CBb7JOpkRbUnxy50rQRPH-Rv92DQMVU52QqcqEIQHQT3m5-5bbN3ht6IDtnu8_IKKtCvbfP0kKn685JtJ-fIX5-RLf2QjLp7F5l8-hl3mLPR1-UkFLqoqaPjN52bwQpC2anoxOKQO0l3qjx-BKbtqWUnH52vf2DQN3KJmexK9bT3v5DuzD4Rg2-biWb7M2MbdJUJP_IoG2Mn8M4bb3qOpBtQmJeTxoUJ25DnJhhCGe4bK-Tr0DH-ftxK; BCLID_BFESS=8984529760757669079; BDSFRCVID_BFESS=JptOJeC6HAAuvhbRgHNpM4lbTH6ORPe6GvpW6LKrgGwN6EG0PfU8g0Ku-F2KSogKK3gOTH4PF_2uxOjjg8UtVJeC6EG0Ptf8g0M5; H_BDCLCKID_SF_BFESS=tR4q_K_KfCK3fP36q4n2btCyhl-X5-Cs3mTi2hcH0KLKjqjCKMtby-It3MR35qjMLmolah3wJxb1MRLRLx7Hyj3XDJ0HB-JIteOjbp5TtUJDJKnTDMRhqfCOQG3yKMnitKv9-pP2LpQrh459XP68bTkA5bjZKxtq3mkjbPbDfn028DKuD6tWDjO-jN_s543KHDrKBRbaHJOoDDk9yTOcy4LdjG5mBtcNb6c7aPTjXbooOnT4MJKbjPQy3-Aq54RGMJuH_l0y3nbSbMj8WjOVQfbQ0bbOqP-jW5Ta-nQy0b7JOpkRbUnxy50rQRPH-Rv92DQMVU52QqcqEIQHQT3m5-5bbN3ht6IDtnADVILMfCvHebDk-4QEbbQH-UnLqMkD02OZ0l8Ktt58oxP9-U712t-g-xniaRjxyI6x_-omWIQHDnOCLPnVDtKu3JQGbTbOLjR4KKJxHxKWeIJo5DcPqjv-hUJiB5JMBan7_UJIXKohJh7FM4tW3J0ZyxomtfQxtNRJ0DnjtpChbC_wDj0Ke5jXepJf-K68a5Qy0nObHJOoDDkmb53cy4LdjGK8ybQrB5r7QlQG2RnDVR3y5R545hD73-Aq54RbBaniol7l-Pc2OxQxj4vkQfbQ0-chqP-jW26abn5CBb7JOpkRbUnxy50rQRPH-Rv92DQMVU52QqcqEIQHQT3m5-5bbN3ht6IDtnu8_IKKtCvbfP0kKn685JtJ-fIX5-RLf2QjLp7F5l8-hl3mLPR1-UkFLqoqaPjN52bwQpC2anoxOKQO0l3qjx-BKbtqWUnH52vf2DQN3KJmexK9bT3v5DuzD4Rg2-biWb7M2MbdJUJP_IoG2Mn8M4bb3qOpBtQmJeTxoUJ25DnJhhCGe4bK-Tr0DH-ftxK; 68000159_FRSVideoUploadTip=1; wise_device=0; video_bubble0=1; bdshare_firstime=1635942544484; BDUSS=nNSZmtseDZvTThuMHFsa3ZXSTN6Tkwzcmg0eUtZQ0xGaFpHZzNsQUdHUFFEcXBoSVFBQUFBJCQAAAAAAAAAAAEAAACfmQ0EYWh1Y2hqbQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAANCBgmHQgYJhN; BDUSS_BFESS=nNSZmtseDZvTThuMHFsa3ZXSTN6Tkwzcmg0eUtZQ0xGaFpHZzNsQUdHUFFEcXBoSVFBQUFBJCQAAAAAAAAAAAEAAACfmQ0EYWh1Y2hqbQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAANCBgmHQgYJhN; STOKEN=6b5fb7e1c0dd6cdfbfee7c75622b269ff984e02fe6eaff870c70cc6bf2e53bcc; IS_NEW_USER=aba520f59334b69910682415; TIEBAUID=5e2d54c3ea5d5c644cd40353; Hm_lpvt_98b9d8c2fd6608d564bf2ac2ae642948=1635942936; ab_sr=1.0.1_NTQ4Yjg3OGZkNGU0MzVjYmYzZTcyZDE0NDg5YTkzZTJhZGFlZDIxYzI3N2QzZjEwMDY2NzQ0NGIxY2RhN2FjYjdjMWUzNDdhODc3ZTgxYWU5ODczNjNmNjA5M2I5MDlkMmE0ZTQ0N2FiZDhiYzc5NDczODA4MjJmN2JhNDViNmNkMTRiYWY4NGEzZTBhN2M1ODZkMjc4ZmZiNDI4OTM3OTIyNzEwZDY3YWE5MTViZjQ2ZGE2Y2QyZDgxMDVkNTkw; st_data=a32f2ce0a40d1c0432958cc63cb2a6368a0be418d60f9092729d9e80148267f789bf5e74896adf184e757d56d420f61ef8ccdb0dcd4732060aebce09f86cd443600368292845001ead0d995f05747bdcf2de24bf23574acaaf66bdf412724e9076c8f0064308a79ab4baa610940acafba5ba7517132036caa3eccd5c016fe369; st_sign=6a117972'
```

##### 2.3 豆瓣

如命令行不带cookie登录态参数，请求多次后将会跳转登录，无法继续抓取内容。

即时导入：topic，number，cookie为必传参数。

自动导入：topic，number，cookie，auto，type，interval为必传参数。

```php
(1). 即时导入：php disco importData:insertDouBanData --topic=xxx --number=5 --cookie='登录态'
(2). 自动导入：参考2.1微博“自动导入”命令。设置自动导入需考虑cookie的有效性，如cookie失效，自动导入将会中断。
```
示例：

```text
php disco importData:insertDouBanData --topic=北京租房 --number=6 --cookie='ll="118282"; bid=kMTo-mW9rpQ; __utma=30149280.1044.1635992155.1; __utmc=30149280; __utmz=30149280155.1.1.utmcsr=baidu|utmccn=(organic)|utmcmd=organic; __utmt=1; ap_v=0,6.0; dbcl2="151692359:qOYgcGTqyyA"; ck=a4KT; push_noty_num=0; push_doumail_num=0; __utmv=30149280.15169; loc-last-index-location-id="118282"; _vwo_uuid_v2=D02992C80026DC857|a153e8b85cfc82fdf9; frodotk="b963a27f868c81e93a5832d"; talionusr="eyJpZCI6ICIxNTE2OTIzNTkiLCAGNcdTgwMDUifQ=="; Hm_lvt_6d4a8cfea88fa457c3127e14fb5fabc2=1635992508; talionnav_show_app="0"; Hm_lpvt_6d4a8cfea88fa457c3127e14fb5fabc2=1635992510; __gads=ID=7d4a74c4f4afeb68-22f9d4ad8cce003b:T=1635992631:RT=1635992631:S=AGkdH7C_kDj9atXg; __utmb=30149280.26.5.1635992632842'
```

##### 2.4 知识星球

即时导入：topic，number，cookie，userAgent为必传参数。

自动导入：topic，number，cookie，userAgent，auto，type，interval为必传参数。

```php
(1). 即时导入：php disco importData:insertLearnStarData --topic=xxx --number=5 --cookie='登录态' --userAgent='模拟浏览器user agent，举例谷歌浏览器(Mac)请求：Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.131 Safari/537.36'
(2). 自动导入：参考2.1微博“自动导入”命令。另，设置自动导入需考虑cookie的有效性，如cookie失效，自动导入将会中断。
```
示例：
```text
php disco importData:insertLearnStarData --topic=学习 --number=9 --cookie="amplitude_id_fef1e872c952688acd962d30aa545b9ezsxq.com=eyJkZXZpY2VJZCI6IjNlZmE0NzViLWQ5OTQtNDMyYy04MmUwLThlOWNjNjQwMmUwNlIiLCJ1c2Vyc3Npb25JZCI6MTYzMTA4MTc4NTM3NiwibGFzdEV2ZW50VGltZSI6MTYzMTA4MTc4NTM3NywiZXZlbnRJZCI6MSwiaWRWVuY2VOdW1iZXIiOjF9; _ga=GA1.2.696784474.1631081785; sensorsdata2015jssdkcross=%7B%22distinct_id%2255525144%22%2C%22first_id%22%3A%2215d629eefb5fa-c343365-1327104-17b7b43630cb03%22%2C%22props%22%3A%7B%7D%2C%22%24devi3630b3fa-085d629eefb5fa-c343365-1327104-13%22%7D; abtest_env=product; zsxq_access_token=978F-A0C7-05B7-7A62-1_F6DE2B0A0F5B4E57" --userAgent="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Safari/537.36"
```

##### 2.5 Discuz! X

Discuz! X内容抓取基于官方版本v3.4-20210926(UTF-8)，不适用于二开站点和安装了多样化插件的站点。

即时导入：topic，number，url，cookie为必传参数。

自动导入：topic，number，url，cookie，auto，type，interval为必传参数，其他参数根据自己设计的时间分别取用。

```php
(1). 即时导入：
php disco importData:insertDiscuzData --topic=xxx --number=30 --url="网站地址" --cookie="登录态" # 80端口站点内容爬取

php disco importData:insertDiscuzData --topic=xxx --number=30 --url="网站地址" --port=30001 --cookie="登录态" # 特殊端口(如30001)站点内容爬取

(2). 自动导入：参考2.1微博“自动导入”命令。另，设置自动导入需考虑cookie的有效性，如cookie失效，自动导入将会中断。
```
示例：

```text
php disco importData:insertDiscuzData --topic=插件 --number=9 --url=https://www.discuz.net/ --cookie='t7asq_4ad6_home_diymode=1; t7asq_4ad6_seccode=18067917.7b323b923a; t7asq_4ad6_saltkey=L2PaHuFJ; t7asq_4ad6_lastvisit=1634804192; _ga=GA1.2.1897667745.1634807794; Hm_lvt_f419227f4346c5f329e596670da39bca=1634807801; Hm_lpvt_f419227f4346c5f329e596670da39bca=1634807864; _gid=GA1.2.1056462707.1635994369; t7asq_4ad6_st_p=0%7C1635994389%7Cd3c945b070f62c1122056759bd64599f; t7asq_4ad6_viewid=tid_3857547; t7asq_4ad6_sendmail=1; t7asq_4ad6_seccodecS=25896240.1fce74ac7434f1eba8; t7asq_4ad6_seccodecSA=258962c3102; t7asq_4ad6_ulastactivity=1635995139%7C0; t7asq_4ad6_auth=06edtyCDWlgAhFEUAp9XdOcR82z8%2FntNkoIFPYpAEuAkZPBEPVe75bQsMb%2BXSQ3PxM0etfaPxUZHn; t7asq_4ad6_lastcheckfeed=3134636%7C1635995139; t7asq_4ad6_checkfollow=1; t7asq_4ad6_lip=113.108.77.54%2C1635995139; t7asq_4ad6_sid=0; t7asq_4ad6_connect_is_bind=0; t7asq_4ad6_checkpm=1; _gat_gtag_UA716312_1=1; t7asq_4ad6_seccodecS0=2589628557ec60c; t7asq_4ad6_lastact=1635995150%09forum.prumdisplay; t7asq_4ad6_st_t=3134636%7C0%7C2ee2dba4c87dad42034523a; t7asq_4ad6_forum_lastvisit=D_995150'
```

##### 2.6 微信公众号文章

公众号文章只能以文章链接逐篇导入，不支持自动导入命令，只支持即时导入命令。多篇文章链接请以`英文逗号 , `隔开。

```php
(1). 即时导入：
php disco importData:insertOfficialAccountArticleData --articleUrl='https://mp.weixin.qq.com/s/M49aEEEcpdzbjB-PEUhyhw'  # 导入单篇文章

php disco importData:insertOfficialAccountArticleData --articleUrl='https://mp.weixin.qq.com/s/eqlaxq6eod2Lpe-p6JFnlQ,https://mp.weixin.qq.com/s/zWqNzA2qzTz78VURZTeFDQ,https://mp.weixin.qq.com/s/Gu6jwVM78-dhrytuclyxLg'  # 导入多篇文章，以英文逗号隔开 

(2). 自动导入：不支持
```

示例：
```text
php disco importData:insertOfficialAccountArticleData --articleUrl='https://mp.weixin.qq.com/s/oPZQRB0uiENrkX-cktaRSg,https://mp.weixin.qq.com/s/-U1QMMYJUZqfxNUk3VeZYg,https://mp.weixin.qq.com/s/H7V38NeFrSHaRPF3nHYs9g'
```

#### 3. 删除/取消自动导入

```
php disco autoImport:stop
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

