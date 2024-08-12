<?php

namespace West\UserApiKey\XF\Pub\Controller;

use West\UserApiKey\Repository\UserApiKey;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\Exception;
use XF\PrintableException;

class Account extends XFCP_Account
{
    /**
     * @throws Exception
     */
    protected function preDispatchController($action, ParameterBag $params)
    {
        parent::preDispatchController($action, $params);

        if (in_array(strtolower($action), ['apikey', 'apikeygenerate', 'apikeyhelp']))
        {
            $this->assertPasswordVerified(1800);

            if (!\XF::visitor()->canWuakUseApiKeys())
            {
                throw $this->exception($this->noPermission());
            }
        }
    }

    public function actionApiKey()
    {
        $visitor = \XF::visitor();

        /** @var UserApiKey $repo */
        $repo = $this->repository('West\UserApiKey:UserApiKey');

        /** @var \West\UserApiKey\Entity\UserApiKey $userApiKey */
        $userApiKey = $repo->findUserApiKey($visitor->user_id)
            ->with('ApiKey')
            ->fetchOne();

        if ($userApiKey && !$userApiKey->seen)
        {
            $userApiKey->setOption('force_show_token', true);
            $userApiKey->seen = true;
            $userApiKey->save();
        }

        $view = $this->view('West\UserApiKey:Account\UserApiKey', 'wuak_account_api_key', [
            'userApiKey' => $userApiKey,
            'showToken' => $userApiKey ? $userApiKey->getOption('force_show_token') : false,
            'extraPhrases' => json_encode($this->getExtraPhrases())
        ]);

        return $this->addAccountWrapperParams($view, 'wuak-api-key');
    }

    /**
     * @throws Exception
     * @throws PrintableException
     */
    public function actionApiKeyGenerate()
    {
        $this->assertPostOnly();
        $repo = $this->getUserApiKeyRepo();

        $visitor = \XF::visitor();

        /** @var \West\UserApiKey\Entity\UserApiKey $userApiKey */
        $userApiKey = $repo->findUserApiKey($visitor->user_id)->fetchOne();
        if ($userApiKey)
        {
            $repo->regenerateUserApiKey($userApiKey, $errors);
        }
        else
        {
            $repo->createUserApiKey($visitor, $errors);
        }

        if (!empty($errors))
        {
            return $this->error($errors);
        }
        return $this->redirect($this->buildLink('account/api-key'));
    }

    public function actionApiKeyHelp()
    {
        return $this->view('West\UserApiKey:Account\ApiKeyHelp', 'wuak_account_api_key_help', [
            'noTimer' => $this->filter('noTimer', 'bool')
        ]);
    }

    /**
     * @return UserApiKey|\XF\Mvc\Entity\Repository
     */
    protected function getUserApiKeyRepo(): UserApiKey
    {
        return $this->repository('West\UserApiKey:UserApiKey');
    }

    protected function getExtraPhrases(): array
    {
        $extraPhrases = [];
        foreach ($this->getExtraPhraseList(['day', 'hour', 'minute', 'second']) as $phraseName)
        {
            $extraPhrases[$phraseName] = \XF::phrase($phraseName)->render('json');
        }

        return $extraPhrases;
    }

    protected function getExtraPhraseList(array $unitList): array
    {
        return array_reduce($unitList, function (array $acc, string $unit)
        {
            return array_merge($acc, [
                'wuak_x_' . $unit,
                'wuak_x_' . $unit . 's_few',
                'wuak_x_' . $unit . 's_many',
            ]);
        }, []);
    }
}