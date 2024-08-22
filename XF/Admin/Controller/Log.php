<?php

namespace West\UserApiKey\XF\Admin\Controller;

class Log extends XFCP_Log
{
    public function actionWuakUserStore()
    {
        $this->setSectionContext('wuakStoreCheckLog');

        $page = $this->filterPage();
        $perPage = 50;

        $logFinder = $this->finder('West\UserApiKey:StoreCheckLog')
            ->limitByPage($page, $perPage)
            ->order('log_date', 'DESC');


        return $this->view('West\UserApiKey:Log\UserStore\Index', 'wuak_log_store_check', [
            'logs' => $logFinder->fetch(),

            'page' => $page,
            'perPage' => $perPage,
            'total' => $logFinder->total(),
        ]);
    }
}