<?php

namespace West\UserApiKey\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int $user_id
 * @property int $api_key_id
 * @property int $expires_at
 *
 * RELATIONS
 * @property \XF\Entity\User $User
 * @property \XF\Entity\ApiKey $ApiKey
 */
class UserApiKey extends Entity
{
    public function getApiKey()
    {
        return $this->seen ? $this->ApiKey->api_key_snippet : $this->ApiKey->api_key;
    }

    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_wuak_user_api_key';
        $structure->shortName = 'West\UserApiKey:UserApiKey';
        $structure->primaryKey = ['user_id', 'api_key_id'];
        $structure->columns = [
            'user_id' => ['type' => self::UINT, 'required' => true],
            'api_key_id' => ['type' => self::UINT, 'required' => true],
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
                'conditions' => 'api_key_id'
            ]
        ];

        $structure->getters = [
            'api_key' => true
        ];

        return $structure;
    }
}