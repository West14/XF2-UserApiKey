<?php

namespace West\UserApiKey\XF\Entity;

class User extends XFCP_User
{
    public function canWuakUseApiKeys()
    {
        return $this->hasPermission('general', 'wuakUseApiKeys');
    }
}