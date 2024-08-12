<?php

namespace West\UserApiKey\Cron;

use West\UserApiKey\Entity\UserApiKey;

class DeactivateKeys
{
    public static function performKeyDeactivation()
    {
        /** @var \West\UserApiKey\Repository\UserApiKey $keyRepo */
        $keyRepo = \XF::repository('West\UserApiKey:UserApiKey');

        /** @var UserApiKey[] $expiredKeys */
        $expiredKeys = $keyRepo->findExpiredApiKeys()
            ->fetch();

        foreach ($expiredKeys as $key)
        {
            $key->ApiKey->active = false;
            $key->ApiKey->save();
        }
    }
}