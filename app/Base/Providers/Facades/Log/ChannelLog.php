<?php

namespace App\Base\Providers\Facades\Log;

use Illuminate\Support\Facades\Facade;

class ChannelLog extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'channellog';
    }
}
