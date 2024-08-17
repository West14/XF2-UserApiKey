<?php

namespace West\UserApiKey\XF\Entity;

use West\UserApiKey\Entity\UserStore;

/**
 * @property UserStore $UserStore
 */
class User extends XFCP_User
{
    public function canWuakUseApiKeys()
    {
        return $this->hasPermission('general', 'wuakUseApiKeys');
    }

    public function hasValidStore()
    {
        $userStore = $this->UserStore;

        return $userStore && $userStore->isValid();
    }
}