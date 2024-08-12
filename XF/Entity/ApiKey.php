<?php

namespace West\UserApiKey\XF\Entity;

use West\UserApiKey\Entity\UserApiKey;

/**
 * RELATIONS
 *
 * @property-read UserApiKey $UserApiKey
 */
class ApiKey extends XFCP_ApiKey
{
    public function hasScope($scope)
    {
        if ($this->isWuakUserApiKey())
        {
            $scopeOptions = \XF::options()->wuakAllowedScopes;
            if ($scopeOptions['allow_all_scopes'])
            {
                return true;
            }

            return !empty($scopeOptions['scopes'][$scope]);
        }

        return parent::hasScope($scope);
    }

    public function isWuakUserApiKey(): bool
    {
        return $this->UserApiKey != null;
    }
}