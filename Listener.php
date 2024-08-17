<?php

namespace West\UserApiKey;

use XF\Mvc\Entity\Entity;

class Listener
{
    public static function apiKeyEntityStructure(\XF\Mvc\Entity\Manager $em, \XF\Mvc\Entity\Structure &$structure)
    {
        $structure->relations += [
            'UserApiKey' => [
                'entity' => 'West\UserApiKey:UserApiKey',
                'type' => Entity::TO_ONE,
                'conditions' => 'api_key_id',
                'cascadeDelete' => true
            ]
        ];
    }

    public static function userEntityStructure(\XF\Mvc\Entity\Manager $em, \XF\Mvc\Entity\Structure &$structure)
    {
        $structure->relations += [
            'UserStore' => [
                'entity' => 'West\UserApiKey:UserStore',
                'type' => Entity::TO_ONE,
                'conditions' => 'user_id',
                'primary' => true
            ]
        ];
    }

    public static function appApiValidateRequest(\XF\Http\Request $request, &$result, &$error, &$code)
    {
        $apiKeyValue = $request->getApiKey();
        if (!$apiKeyValue)
        {
            return;
        }

        /** @var \XF\Repository\Api $apiRepo */
        $apiRepo = \XF::repository('XF:Api');
        /** @var \West\UserApiKey\XF\Entity\ApiKey $apiKey */
        $apiKey = $apiRepo->findApiKeyByKey($apiKeyValue, ['UserApiKey', 'UserApiKey.User', 'UserApiKey.User.UserStore']);

        if (!$apiKey->isWuakUserApiKey())
        {
            return;
        }

        $userApiKey = $apiKey->UserApiKey;
        $user = $userApiKey->User;
        if ($userApiKey->isExpired() || !$user->UserStore || !$user->UserStore->isValid())
        {
            $error = 'api_error.api_key_inactive';
            $code = 403;
            $result = false;
        }
    }
}