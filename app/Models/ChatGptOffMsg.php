<?php
namespace App\Models;


use Discuz\Base\DzqModel;


/**
 * Models a post-user state record in the database.
 *
 */
class ChatGptOffMsg extends DzqModel
{
    public $timestamps = false;
    protected $table = 'chatgptoffmsgs';
}

