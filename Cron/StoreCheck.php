<?php

namespace West\UserApiKey\Cron;

class StoreCheck
{
    public static function performStoreCheck()
    {
        \XF::app()->jobManager()->enqueueUnique(
            'wuakStoreCheck',
            'West\UserApiKey:StoreCheck',
            [],
            false
        );
    }
}