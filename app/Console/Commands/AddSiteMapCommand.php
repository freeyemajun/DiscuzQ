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

namespace App\Console\Commands;

use Carbon\Carbon;
use Discuz\Console\AbstractCommand;

class AddSiteMapCommand extends AbstractCommand
{
    protected $signature = 'add:SiteMap';

    protected $description = '定时增加sitemap站点地图';

    const PAGE_SIZE = 10000;

    const LIMIT = 50000;

    public function handle()
    {
        $db = app('db');
        $this->info('开始生成站点地图sitemap');
        $date = date('Y-m-d', time());
        $site_url = $db->table('settings')->where('key', 'site_url')->value('value');
        if (empty($site_url)) {
            //如果数据库里面没有站点url的话，则不生成sitemap
            return;
        }
        $sitemap_file_path = base_path().'/public/sitemap.xml';
        $sitemaps_dir = base_path().'/public/sitemaps/';

        //查出多少个分类
        $category_ids = $db->table('categories')->where('parentid', 0)->pluck('id')->toArray();
        $categories = [];
        foreach ($category_ids as $val) {
            $sub_c_ids = $db->table('categories')->where('parentid', $val)->orderBy('sort')->orderBy('id')->pluck('id')->toArray();
            if (!empty($sub_c_ids)) {
                $sub_c_ids = implode('_', $sub_c_ids);
                $categories[] = $val.'_'.$sub_c_ids;
            } else {
                $categories[] = $val;
            }
        }

        //创建 sitemaps 目录
        if (!is_dir($sitemaps_dir)) {
            mkdir($sitemaps_dir, 0755);
        }
        //生成 index.xml 文件
        $index_file_path = $sitemaps_dir.'/index.xml';
        $index_file = fopen($index_file_path, 'w');
        //找出所有话题
        $x_index = $this->index($site_url, $date, $categories);
        fwrite($index_file, $x_index);
        fclose($index_file);
        $index_file_path_gz = $index_file_path.'.gz';
        $this->gz_file($index_file_path, $index_file_path_gz);
        $h_month_before = Carbon::now()->subDays(15);
        $h_year_before = Carbon::now()->subDays(180);
        $categories_c = [];
        //生成 categroy_idxxxx.xml 文件
        foreach ($categories as $val) {
            $i = $count = 0;
            $c_ids = explode('_', $val);
            $val_c = $val.'_'.$count;
            $categories_c[] = $val_c;
            $category_id_x_path = $sitemaps_dir.'categroy_id_'.$val_c.'.xml';
            $c_x_path = fopen($category_id_x_path, 'w');
            $pre_xml = '<?xml version="1.0" encoding="UTF-8"?>
                        <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
            $threads = $db->table('threads')->whereIn('category_id', $c_ids)->offset($i * self::PAGE_SIZE)->limit(self::PAGE_SIZE)->orderBy('created_at', 'desc')->get(['id', 'created_at']);
            while (!empty($threads->toArray())) {
                if ($i * self::PAGE_SIZE > self::LIMIT * ($count + 1)) {
                    //先把上一个文件收尾
                    $pre_xml .= '</urlset>';
                    fwrite($c_x_path, $pre_xml);
                    fclose($c_x_path);
                    $category_id_x_path_gz = $category_id_x_path.'.gz';
                    $this->gz_file($category_id_x_path, $category_id_x_path_gz);
                    // 然后开始下一个5万条帖子的文件
                    $count ++;
                    $val_c = $val.'_'.$count;
                    $categories_c[] = $val_c;
                    $category_id_x_path = $sitemaps_dir.'categroy_id_'.$val_c.'.xml';
                    $c_x_path = fopen($category_id_x_path, 'w');
                    $pre_xml = '<?xml version="1.0" encoding="UTF-8"?>
                        <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
                }
                foreach ($threads as $vo) {
                    switch ($vo->created_at) {
                        case $vo->created_at > $h_month_before:
                            $changefreg = 'daily';
                            break;
                        case $vo->created_at < $h_month_before && $vo->created_at > $h_year_before:
                            $changefreg = 'weekly';
                            break;
                        case $vo->created_at < $h_year_before:
                            $changefreg = 'monthly';
                            break;
                        default:
                            $changefreg = 'yearly';
                            break;
                    }
                    $pre_xml .= $this->threads($site_url, $date, [$vo->id], $changefreg);
                }
                $i ++;
                $threads = $db->table('threads')->whereIn('category_id', $c_ids)->offset($i * self::PAGE_SIZE)->limit(self::PAGE_SIZE)->orderBy('created_at', 'desc')->get(['id', 'created_at']);
            }
            $pre_xml .= '</urlset>';
            fwrite($c_x_path, $pre_xml);
            fclose($c_x_path);
            $category_id_x_path_gz = $category_id_x_path.'.gz';
            $this->gz_file($category_id_x_path, $category_id_x_path_gz);
        }
        // user.xml 文件
        $users = $db->table('users')->where('updated_at', '>', $h_year_before)->orderBy('updated_at', 'desc')->get(['id', 'updated_at']);
        $user_file_path = $sitemaps_dir.'/user.xml';
        $user_file = fopen($user_file_path, 'w');
        $x_user = '<?xml version="1.0" encoding="UTF-8"?>
                        <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach ($users as $vo) {
            $update_day = substr($vo->updated_at, 0, 10);
            $x_x_user = "<url>
                          <loc>{$site_url}/user/{$vo->id}</loc>
                          <lastmod>{$update_day}</lastmod>
                          <changefreq>weekly</changefreq>
                          <priority>0.8</priority>
                       </url>";
            $x_user .= $x_x_user;
        }
        $x_user .= '</urlset>';
        fwrite($user_file, $x_user);
        fclose($user_file);
        $user_file_path_gz = $user_file_path.'.gz';
        $this->gz_file($user_file_path, $user_file_path_gz);

        //生成topic.xml
        $topic_ids = $db->table('topics')->pluck('id')->toArray();
        $topic_file_path = $sitemaps_dir.'/topics.xml';
        $topic_file = fopen($topic_file_path, 'w');
        $x_topic = '<?xml version="1.0" encoding="UTF-8"?>
                        <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        if (!empty($topic_ids)) {
            $t_xml = '';
            foreach ($topic_ids as $vt) {
                $t_xml .= "<url>
                         <loc>{$site_url}/topic/topic-detail/{$vt}</loc>
                         <lastmod>{$date}</lastmod>
                         <changefreq>daily</changefreq>
                         <priority>1</priority>
                    </url>";
            }
            $x_topic .= $t_xml;
        }
        $x_topic .= '</urlset>';
        fwrite($topic_file, $x_topic);
        fclose($topic_file);
        $topic_file_path_gz = $topic_file_path.'.gz';
        $this->gz_file($topic_file_path, $topic_file_path_gz);

        //写sitemap文件
        $sitemap_file = fopen($sitemap_file_path, 'w');
        chmod($sitemap_file_path, 0755);
        $x_sitemap = $this->sitemap($site_url, $date, $categories_c);
        fwrite($sitemap_file, $x_sitemap);
        fclose($sitemap_file);
        $this->info('完成生成站点地图sitemap');
    }

    public function sitemap($site_url, $date, $categories)
    {
        $xml = "<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">
                   <!-- 首页主入口的xml、用户users.xml 为预设，只需每日更新lastmod值 -->
                   <sitemap>
                      <loc>{$site_url}/sitemaps/index.xml.gz</loc>
                      <lastmod>{$date}</lastmod>
                   </sitemap>
                   <sitemap>
                      <loc>{$site_url}/sitemaps/users.xml.gz</loc>
                      <lastmod>{$date}</lastmod>
                   </sitemap>";

        foreach ($categories as $val) {
            /* 循环输出分类的xml */
            $c_xml = "<sitemap>
                          <loc>{$site_url}/sitemaps/categroy_id_{$val}.xml.gz</loc>
                          <lastmod>{$date}</lastmod>
                       </sitemap>";
            $xml .= $c_xml;
        }
        //增加话题xml
        $xml .= "<sitemap>
                      <loc>{$site_url}/sitemaps/topics.xml.gz</loc>
                      <lastmod>{$date}</lastmod>
                   </sitemap>";
        //连上结尾
        $xml .= '</sitemapindex>';
        return $xml;
    }

    public function index($site_url, $date, $categories)
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                    <urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">
                    <!-- 首页 -->
                    <url>
                     <loc>{$site_url}/</loc>
                     <lastmod>{$date}</lastmod>
                     <changefreq>daily</changefreq>
                     <priority>1</priority>
                    </url>
                    <!-- 潮流话题 -->
                    <url>
                     <loc>{$site_url}/search/result-topic</loc>
                     <lastmod>{$date}</lastmod>
                     <changefreq>daily</changefreq>
                     <priority>0.8</priority>
                    </url>
                    <!-- 热门内容 -->
                    <url>
                     <loc>{$site_url}/search/result-post</loc>
                     <lastmod>{$date}</lastmod>
                     <changefreq>daily</changefreq>
                     <priority>0.8</priority>
                    </url>
                    <!-- 发现页 -->
                    <url>
                     <loc>{$site_url}/search</loc>
                     <lastmod>{$date}</lastmod>
                     <changefreq>weekly</changefreq>
                     <priority>0.5</priority>
                    </url>
                    <!-- 活跃用户 -->
                    <url>
                     <loc>{$site_url}/search/result-user</loc>
                     <lastmod>{$date}</lastmod>
                     <changefreq>weekly</changefreq>
                     <priority>0.5</priority>
                    </url>
                    <!-- ↑↑站点的主入口链接相对固定，所以上面部分是模板中固定的部分↑↑ -->
                    <!-- ↓↓以下是需要拼装的部分，遍历所有分类URL，循环输出；所有分类的评率设为daily，权重设为1↓↓ -->
                    <url>
                     <loc>{$site_url}/?categoryId=all&amp;sequence=0</loc>
                     <lastmod>{$date}</lastmod>
                     <changefreq>daily</changefreq>
                     <priority>1</priority>
                    </url>";
        foreach ($categories as $val) {
            $c_xml = "<url>
                         <loc>{$site_url}/?categoryId={$val}&amp;sequence=0</loc>
                         <lastmod>{$date}</lastmod>
                         <changefreq>daily</changefreq>
                         <priority>1</priority>
                    </url>";
            $xml .= $c_xml;
        }
        $xml .= '</urlset>';
        return  $xml;
    }

    public function threads($site_url, $date, $thread_ids, $changefreg)
    {
        $xml = '';
        foreach ($thread_ids as $vo) {
            $t_xml = "<url>
                          <loc>{$site_url}/thread/{$vo}</loc>
                          <lastmod>$date</lastmod>
                          <changefreq>{$changefreg}</changefreq>
                          <priority>0.5</priority>
                       </url>";
            $xml .= $t_xml;
        }
        return  $xml;
    }

    /*将文件添加至GZ文件*/
    public function gz_file($file, $gz_name)
    {
        $fp = gzopen($gz_name, 'w9');
        gzwrite($fp, file_get_contents($file));
        gzclose($fp);
        chmod($gz_name, 0755);
    }
}
