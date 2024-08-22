<?php

namespace West\UserApiKey\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int $log_id
 * @property int $user_id
 * @property string $store_url
 * @property string $status
 * @property string|null $error_code
 * @property int $log_date
 *
 * RELATIONS
 * @property \West\UserApiKey\Entity\Store $Store
 * @property \XF\Entity\User $User
 */
class StoreCheckLog extends Entity
{
    public function getStatusPhrase()
    {
        return \XF::phrase('wuak_store_status.' . $this->status);
    }

    public function getErrorPhrase()
    {
        return \XF::phrase('wuak_store_error.' . $this->error_code);
    }

    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_wuak_store_check_log';
        $structure->shortName = 'West\UserApiKey:StoreCheckLog';
        $structure->primaryKey = 'log_id';
        $structure->columns = [
            'log_id' => ['type' => self::UINT, 'autoIncrement' => true],
            'user_id' => ['type' => self::UINT, 'required' => true],
            'store_url' => ['type' => self::STR, 'required' => true, 'maxLength' => 128],
            'status' => ['type' => self::STR, 'required' => true,
                'allowedValues' => ['valid', 'missing_link', 'validating', 'error']
            ],
            'error_code' => ['type' => self::STR, 'maxLength' => 64, 'nullable' => true, 'default' => null],
            'log_date' => ['type' => self::UINT, 'default' => \XF::$time]
        ];

        $structure->relations = [
            'Store' => [
                'entity' => 'West\UserApiKey:Store',
                'type' => self::TO_ONE,
                'conditions' => 'user_id',
                'primary' => true
            ],

            'User' => [
                'entity' => 'XF:User',
                'type' => self::TO_ONE,
                'conditions' => 'user_id',
                'primary' => true
            ]
        ];

        return $structure;
    }
}