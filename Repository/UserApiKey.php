<?php

namespace West\UserApiKey\Repository;

use West\UserApiKey\Entity\UserApiKey as UserApiKeyEntity;
use XF\Mvc\Entity\Repository;

class UserApiKey extends Repository
{
    public function findUserApiKey(int $userId)
    {
        return $this->finder('West\UserApiKey:UserApiKey')
            ->where('user_id', $userId)
            ->with('ApiKey');
    }

    public function createUserApiKey(\XF\Entity\User $user, &$errors = []): ?UserApiKeyEntity
    {
        $apiKey = \XF::em()->create('XF:ApiKey');
        $keyManager = \XF::service('XF:ApiKey\Manager', $apiKey);

        $keyManager->setTitle("User API Key: $user->username ($user->user_id)");
        $keyManager->setScopes(false, ['thread:read']); // TODO: make configurable
        $keyManager->setKeyType('user', $user->username);

        if (!$keyManager->validate($errors))
        {
            return null;
        }

        $keyManager->save();

        /** @var UserApiKeyEntity $userApiKey */
        $userApiKey = \XF::em()->create('West\UserApiKey:UserApiKey');
        $userApiKey->user_id = $user->user_id;
        $userApiKey->api_key_id = $apiKey->api_key_id;
        $userApiKey->expires_at = \XF::$time + (86400 * 30 * 3); // TODO: make configurable

        $userApiKey->save();

        return $userApiKey;
    }

    public function regenerateUserApiKey(UserApiKeyEntity $userApiKey, &$errors = [])
    {
        /** @var \XF\Service\ApiKey\Manager $keyManager */
        $keyManager = \XF::service('XF:ApiKey\Manager', $userApiKey->ApiKey);
        $keyManager->regenerate();

        if (!$keyManager->validate($errors))
        {
            return null;
        }

        $keyManager->save();

        return $userApiKey;
    }
}