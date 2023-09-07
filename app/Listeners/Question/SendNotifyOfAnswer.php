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

namespace App\Listeners\Question;

use App\Events\Question\Saved;
use App\Notifications\Messages\Wechat\QuestionedAnswerWechatMessage;
use App\Notifications\Questioned;

class SendNotifyOfAnswer
{
    public function handle(Saved $event)
    {
        $question = $event->question;
        $actor = $event->actor;

        // Tag 发送通知 (回答后发送回执通知)
        $question->user->notify(new Questioned(QuestionedAnswerWechatMessage::class, $actor, $question));
    }
}