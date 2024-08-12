<?php

namespace West\UserApiKey\Repository;

use West\UserApiKey\Entity\UserApiKey as UserApiKeyEntity;
use West\UserApiKey\XF\Service\ApiKey\Manager;
use XF\Entity\User;
use XF\Mvc\Entity\Finder;
use XF\Mvc\Entity\Repository;
use XF\PrintableException;
use XF\Repository\UserAlert;

class UserApiKey extends Repository
{
    public function findUserApiKey(int $userId): Finder
    {
        return $this->finder('West\UserApiKey:UserApiKey')
            ->where('user_id', $userId)
            ->with('ApiKey');
    }

    public function findExpiredApiKeys(int $cutOff = null)
    {
        return $this->finder('West\UserApiKey:UserApiKey')
            ->where('expires_at', '<=', $cutOff ?? \XF::$time);
    }

    /**
     * @throws PrintableException
     */
    public function createUserApiKey(User $user, &$errors = []): ?UserApiKeyEntity
    {
        /** @var \XF\Entity\ApiKey $apiKey */
        $apiKey = \XF::em()->create('XF:ApiKey');

        /** @var Manager $keyManager */
        $keyManager = \XF::service('XF:ApiKey\Manager', $apiKey);

        $keyManager->setTitle("User API Key: $user->username ($user->user_id)");
        $keyManager->setKeyType('user', $user->username);

        if (!$keyManager->validate($errors))
        {
            return null;
        }

        $keyManager->setIsAutomated();
        $keyManager->save();

        /** @var UserApiKeyEntity $userApiKey */
        $userApiKey = \XF::em()->create('West\UserApiKey:UserApiKey');
        $userApiKey->user_id = $user->user_id;
        $userApiKey->api_key_id = $apiKey->api_key_id;
        $userApiKey->expires_at = $this->getNewKeyExpirationTime();

        $userApiKey->save();

        return $userApiKey;
    }

    public function regenerateUserApiKey(UserApiKeyEntity $userApiKey, &$errors = []): ?UserApiKeyEntity
    {
        /** @var Manager $keyManager */
        $keyManager = \XF::service('XF:ApiKey\Manager', $userApiKey->ApiKey);
        $keyManager->regenerate();

        if (!$keyManager->validate($errors))
        {
            return null;
        }

        $keyManager->setIsAutomated();
        $keyManager->save();

        $userApiKey->expires_at = $this->getNewKeyExpirationTime();
        $userApiKey->seen = false;
        $userApiKey->save();

        $this->removeAlerts($userApiKey);

        return $userApiKey;
    }

    public function getNewKeyExpirationTime()
    {
        return \XF::$time + $this->options()->wuakKeyLifeTime * 86400;
    }

    public function sendExpirationAlerts()
    {
        /** @var UserApiKeyEntity[] $keyList */
        $keyList = $this->findExpiredApiKeys(\XF::$time + (86400 * 7))->fetch();

        $alertRepo = $this->getUserAlertRepo();
        foreach ($keyList as $key)
        {
            $alertRepo->alertFromUser(
                $key->User,
                $key->User,
                'wuak_user_api_key',
                $key->api_key_id,
                'expiration',
                [],
                ['dependsOnAddOnId' => 'West/UserApiKey']
            );
        }
    }

    public function removeAlerts(UserApiKeyEntity $apiKey)
    {
        $this->getUserAlertRepo()->fastDeleteAlertsForContent('wuak_user_api_key', $apiKey->api_key_id);
    }

    /**
     * @return UserAlert|Repository
     */
    protected function getUserAlertRepo()
    {
        return $this->repository('XF:UserAlert');
    }
}