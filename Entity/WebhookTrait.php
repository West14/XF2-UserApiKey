<?php

namespace West\UserApiKey\Entity;

trait WebhookTrait
{
    protected function enqueueWebhook(string $contentType, $contentId, int $ownerUserId = null)
    {
        return \XF::app()->jobManager()->enqueue('West\UserApiKey:Webhook', [
            'content_type' => $contentType,
            'content_id' => $contentId,
            'owner_user_id' => $ownerUserId
        ]);
    }
}