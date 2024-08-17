<?php

namespace West\UserApiKey\Admin\Controller;

use XF\Admin\Controller\AbstractController;
use XF\Mvc\ParameterBag;

class UserStore extends AbstractController
{
    protected function preDispatchController($action, ParameterBag $params)
    {
        $this->assertAdminPermission('wuakManage');
    }

    public function actionIndex()
    {
        $page = $this->filterPage();
        $perPage = 50;

        $storeFinder = $this->getUserStoreRepo()->findUserStoresForList();

        $total = $storeFinder->total();
        $this->assertValidPage($page, $perPage, $total, 'wuak-stores');

        $storeFinder->limitByPage($page, $perPage);

        return $this->view('West\UserApiKey:UserStore\Index', 'wuak_user_store_index', [
            'storeList' => $storeFinder->fetch(),

            'page' => $page,
            'perPage' => $perPage,
            'total' => $total
        ]);
    }

    public function actionEdit(ParameterBag $params)
    {
        $store = $this->assertUserStoreExists($params->user_id, 'User');

        return $this->view('West\UserApiKey:UserStore\Edit', 'wuak_user_store_edit', [
            'store' => $store
        ]);
    }

    public function actionToggle()
    {
        /** @var \XF\ControllerPlugin\Toggle $plugin */
        $plugin = $this->plugin('XF:Toggle');
        return $plugin->actionToggle('West\UserApiKey:UserStore');
    }

    public function actionSave(ParameterBag $params)
    {
        /** @var \West\UserApiKey\Entity\UserStore $store */
        $store = $this->assertUserStoreExists($params->user_id);

        $this->storeSaveProcess($store)->run();

        return $this->redirect($this->buildLink('wuak-stores') . $this->buildLinkHash($store->user_id));
    }

    protected function storeSaveProcess(\West\UserApiKey\Entity\UserStore $store)
    {
        $input = $this->filter([
            'store_url' => 'str',
            'active' => 'bool'
        ]);

        $store->setOption('user_edit', false);

        return $this->formAction()->basicEntitySave($store, $input);
    }

    protected function assertUserStoreExists($id, $with = null, $phraseKey = null)
    {
        return $this->assertRecordExists('West\UserApiKey:UserStore', $id, $with, $phraseKey);
    }

    /**
     * @return \XF\Mvc\Entity\Repository|\West\UserApiKey\Repository\UserStore
     */
    protected function getUserStoreRepo()
    {
        return $this->repository('West\UserApiKey:UserStore');
    }
}