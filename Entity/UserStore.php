<?php

namespace West\UserApiKey\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int $user_id
 * @property string $store_url
 * @property string $status
 * @property string|null $error_code
 * @property int $error_retry_count
 * @property bool $active
 * @property int $checked_at
 *
 * RELATIONS
 * @property \XF\Entity\User $User
 */
class UserStore extends Entity
{
    public function canView()
    {
        return \XF::visitor()->canWuakUseApiKeys();
    }

    public function isValid()
    {
        return $this->status == 'valid' && $this->active;
    }

    public function getStatusPhrase()
    {
        return \XF::phrase('wuak_store_status.' . $this->status);
    }

    public function getStatusExplainPhrase()
    {
        if ($this->status == 'error')
        {
            return $this->getErrorPhrase();
        }

        return \XF::phrase('wuak_store_status_explain.' . $this->status);
    }

    public function getErrorPhrase()
    {
        return \XF::phrase('wuak_store_error.' . $this->error_code);
    }

    protected function _preSave()
    {
        if ($this->getOption('user_edit'))
        {
            if ($this->isChanged('store_url'))
            {
                $this->status = 'validating';
            }

            if ($this->isUpdate() && !in_array($this->status, ['valid', 'validating']))
            {
                $this->status = 'validating';
            }
        }
    }

    protected function _postSave()
    {
        if ($this->status == 'validating')
        {
            \XF::app()->jobManager()->enqueueUnique(
                'wuakStoreCheck-' . $this->user_id,
                'West\UserApiKey:StoreCheckSingle',
                ['userId' => $this->user_id]
            );
        }

        if ($this->isChanged('status') && !$this->getOption('user_edit'))
        {
            /** @var \XF\Repository\UserAlert $alertRepo */
            $alertRepo = $this->repository('XF:UserAlert');
            $alertRepo->alertFromUser(
                $this->User,
                $this->User,
                'wuak_user_store',
                $this->user_id,
                'status_change',
                ['status' => $this->status, 'status_phrase' => $this->getStatusPhrase()]
            );
        }
    }

    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_wuak_user_store';
        $structure->shortName = 'West\UserApiKey:UserStore';
        $structure->primaryKey = 'user_id';
        $structure->columns = [
            'user_id' => ['type' => self::UINT, 'required' => true],
            'store_url' => ['type' => self::STR, 'required' => true, 'maxLength' => 128],
            'status' => ['type' => self::STR, 'required' => true, 'default' => 'validating',
                'allowedValues' => ['valid', 'missing_link', 'validating', 'error']
            ],
            'error_code' => ['type' => self::STR, 'maxLength' => 64, 'nullable' => true, 'default' => null],
            'error_retry_count' => ['type' => self::UINT, 'default' => 3],
            'active' => ['type' => self::BOOL, 'default' => true],
            'checked_at' => ['type' => self::UINT, 'default' => 0]
        ];

        $structure->relations = [
            'User' => [
                'entity' => 'XF:User',
                'type' => self::TO_ONE,
                'conditions' => 'user_id',
                'primary' => true
            ]
        ];

        $structure->options = [
            'user_edit' => true
        ];

        return $structure;
    }
}