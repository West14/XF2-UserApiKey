<?php

namespace West\UserApiKey\Cron;

class ExpirationAlert
{
    public static function sendExpirationAlerts()
    {
        \XF::repository('West\UserApiKey:UserApiKey')->sendExpirationAlerts();
    }
}