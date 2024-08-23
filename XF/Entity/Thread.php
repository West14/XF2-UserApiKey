<?php

namespace West\UserApiKey\XF\Entity;

use West\UserApiKey\Entity\WebhookTrait;

class Thread extends XFCP_Thread
{
    use WebhookTrait;

    protected function shouldSendWebhook()
    {
        if ($this->app() instanceof \XF\Api\App)
        {
            return false;
        }

        if (!in_array($this->node_id, \XF::options()->wuakSellerForums))
        {
            return false;
        }

        if (!$this->User->hasValidStore())
        {
            return false;
        }

        return $this->isChanged('prefix_id') || $this->isChanged('sv_prefix_ids');
    }

    protected function _postSave()
    {
        parent::_postSave();

        if ($this->shouldSendWebhook())
        {
            $this->enqueueWebhook('thread', $this->thread_id, $this->user_id);
        }
    }
}