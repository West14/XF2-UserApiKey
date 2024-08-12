<?php

namespace West\UserApiKey\XF\Service\ApiKey;

class Manager extends XFCP_Manager
{
    protected $isAutomated = false;

    public function setIsAutomated(bool $isAutomated = true)
    {
        $this->isAutomated = $isAutomated;
    }

    protected function contactSuperAdmins()
    {
        if ($this->isAutomated)
        {
            return;
        }

        parent::contactSuperAdmins();
    }
}