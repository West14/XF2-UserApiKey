<?php

namespace West\UserApiKey\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int $user_id
 * @property int $api_key_id
 * @property int $expires_at
 * @property bool $seen
 *
 * GETTERS
 * @property mixed $token
 * @property mixed $token_snippet
 *
 * RELATIONS
 * @property \XF\Entity\User $User
 * @property \XF\Entity\ApiKey $ApiKey
 */
class UserApiKey extends Entity
{
    public function canView()
    {
        return \XF::visitor()->canWuakUseApiKeys();
    }

    public function getToken()
    {
        return !$this->seen || $this->getOption('force_show_token')
            ? $this->ApiKey->api_key
            : $this->token_snippet;
    }

    public function getTokenSnippet()
    {
        $token = $this->ApiKey->api_key;

        return substr($token, 0, 10) . str_repeat('*', 22);
    }

    public function isExpired()
    {
        return \XF::$time >= $this->expires_at;
    }

    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_wuak_user_api_key';
        $structure->shortName = 'West\UserApiKey:UserApiKey';
        $structure->contentType = 'wuak_user_api_key';
        $structure->primaryKey = ['user_id', 'api_key_id'];
        $structure->columns = [
            'user_id' => ['type' => self::UINT, 'required' => true],
            'api_key_id' => ['type' => self::UINT, 'required' => true, 'unique' => true],
            'expires_at' => ['type' => self::UINT, 'required' => true],
            'seen' => ['type' => self::BOOL, 'default' => false]
        ];

        $structure->relations = [
            'User' => [
                'entity' => 'XF:User',
                'type' => self::TO_ONE,
                'conditions' => 'user_id'
            ],
            'ApiKey' => [
                'entity' => 'XF:ApiKey',
                'type' => self::TO_ONE,
                'conditions' => 'api_key_id',
                'cascadeDelete' => true
            ]
        ];

        $structure->getters = [
            'token' => true,
            'token_snippet' => true
        ];

        $structure->options = [
            'force_show_token' => false
        ];

        return $structure;
    }
}