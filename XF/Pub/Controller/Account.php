<?php

namespace West\UserApiKey\XF\Pub\Controller;

use West\UserApiKey\Repository\UserApiKey;

class Account extends XFCP_Account
{
    public function actionApiKey()
    {
        $visitor = \XF::visitor();
        if (!$visitor->canWuakUseApiKeys())
        {
            return $this->noPermission();
        }

        /** @var UserApiKey $repo */
        $repo = $this->repository('West\UserApiKey:UserApiKey');
        $userApiKey = $repo->findUserApiKey($visitor->user_id)
            ->fetchOne();

        return $this->view('West\UserApiKey:Account\UserApiKey', 'wuak_account_api_key', [
            'userApiKey' => $userApiKey
        ]);
    }

    public function actionApiKeyGenerate()
    {
        $this->assertPostOnly();

        $visitor = \XF::visitor();
        if (!$visitor->canWuakUseApiKeys())
        {
            return $this->noPermission();
        }

        $repo = $this->getUserApiKeyRepo();

        /** @var \West\UserApiKey\Entity\UserApiKey $userApiKey */
        $userApiKey = $repo->findUserApiKey($visitor->user_id)->fetchOne();
        if ($userApiKey)
        {
            $repo->regenerateUserApiKey($userApiKey, $errors);
        }
        else
        {
            $repo->createUserApiKey(\XF::visitor(), $errors);
        }

        if (!empty($errors))
        {
            return $this->error($errors);
        }
        return $this->redirect($this->buildLink('account/api-key'));
    }

    /**
     * @return UserApiKey|\XF\Mvc\Entity\Repository
     */
    protected function getUserApiKeyRepo(): UserApiKey
    {
        return $this->repository('West\UserApiKey:UserApiKey');
    }
}