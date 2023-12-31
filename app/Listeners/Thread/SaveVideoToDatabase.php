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

namespace App\Listeners\Thread;

use App\Commands\Thread\CreateThreadVideo;
use App\Events\Thread\Created;
use App\Models\Thread;
use App\Models\ThreadVideo;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface;

class SaveVideoToDatabase
{
    /**
     * @var ServerRequestInterface
     */
    public $request;

    /**
     * @var Dispatcher
     */
    public $bus;

    public function __construct(ServerRequestInterface $request, Dispatcher $bus)
    {
        $this->request = $request;
        $this->bus = $bus;
    }

    /**
     * @param Created $event
     */
    public function handle(Created $event)
    {
        $thread = $event->thread;
        $actor = $event->actor;

        $ServerParamsData = $this->request->getServerParams();
        $requestUrl = $ServerParamsData['REQUEST_URI'];
        $data = [];
        if (strpos($requestUrl, 'v2') !== false) {
            $getJsonData = json_decode(file_get_contents('php://input'), true);
            $data = [
                'type' => 'threads',
                'relationships' =>  [
                    'category' =>  [
                        'data' =>  [
                            'type' => 'categories',
                            'id' => $getJsonData['categoriesId']
                        ]
                    ],
                ]
            ];
            $data['attributes']['content'] =$getJsonData['content'];
            $data['attributes']['type'] =$getJsonData['type'];
            if (!empty($getJsonData['fileId'])) {
                $data['attributes']['file_id'] =$getJsonData['fileId'];
            }
            if (!empty($getJsonData['fileName'])) {
                $data['attributes']['file_name'] =$getJsonData['fileName'];
            }
            if (!empty($getJsonData['mediaUrl'])) {
                $data['attributes']['media_url'] =$getJsonData['mediaUrl'];
            }
            if (!empty($getJsonData['coverUrl'])) {
                $data['attributes']['cover_url'] =$getJsonData['coverUrl'];
            }
        } else {
            $data = Arr::get($this->request->getParsedBody(), 'data', []);
        }

        $fileId = Arr::get($data, 'attributes.file_id', '');

        if (! $fileId) {
            return;
        }

        switch ($thread->type) {
            // 视频帖
            case Thread::TYPE_OF_VIDEO:
                $video = $this->bus->dispatch(
                    new CreateThreadVideo($actor, $thread, ThreadVideo::TYPE_OF_VIDEO, $data)
                );

                $thread->setRelation('threadVideo', $video);
                break;

            // 语音帖
            case Thread::TYPE_OF_AUDIO:
                $audio = $this->bus->dispatch(
                    new CreateThreadVideo($actor, $thread, ThreadVideo::TYPE_OF_AUDIO, $data)
                );

                $thread->setRelation('threadAudio', $audio);
                break;
        }
    }
}
