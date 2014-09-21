<?php

require_once __DIR__ . "/../AuthCommentProvider.class.php";

class YaCommentProvider extends AuthCommentProvider {

    public $sName = 'ya';
    public $sAuthUrl = 'https://oauth.yandex.ru/authorize?response_type=code&client_id=%%client_id%%';
    public $sTokenUrl = 'https://oauth.yandex.ru/token?grant_type=authorization_code&code=%%code%%&client_id=%%client_id%%&client_secret=%%secret_key%%';
    public $sUserInfoUrl = 'https://login.yandex.ru/info?format=json&oauth_token=%%access_token%%';

    /**
     * Получение токена пользователя
     *
     * @return PluginGc_ModuleCommentProvider_EntityUserToken
     * @throws Exception
     */
    public function GetUserToken() {

        if (!$aData = $this->LoadTokenData(TRUE)) {
            return FALSE;
        }

        /**
         * Возвратим объект токена
         */
        $oToken = Engine::GetEntity('PluginAr_ModuleAuthProvider_EntityUserToken', array(
            'token_provider_name'    => $this->sName,
            'token_data'             => $aData->access_token,
            'token_expire'           => $aData->expires_in ? time() + $aData->expires_in : 0,
            'token_provider_user_id' => 0,
        ));

        return $oToken;
    }

    public function GetUserData(PluginGc_ModuleCommentProvider_EntityUserToken $oToken) {

        if (!$aData = $this->LoadAdditionalData(
            $oToken,
            array(
                '%%access_token%%' => $oToken->getTokenData(),
                '%%signature%%'    => md5("app_id={$this->sClientId}method=users.getInfosecure=1session_key={$oToken->getTokenData()}{$this->sSecretKey}")
            ), FALSE)
        ) {
            return FALSE;
        }

        // Раскодируем
        $oData = json_decode($aData);

        /**
         * Получили дополнительные данные. Заполним профиль из того, что есть
         */

        return Engine::GetEntity('PluginGc_ModuleCommentProvider_EntityData', array(
            'data_provider_name' => $this->sName,
            'data_login'         => $this->sName . '_' . $oData->id,
            'data_name'          => @$oData->first_name,
            'data_surname'       => @$oData->last_name,
            'data_sex'           => ((@$oData->sex == 'male') ? 'man' : ($oData->sex == 'female' ? 'woman' : 'other')),
            'data_about'         => '',
            'data_page'          => '',
            'data_birthday'      => date('Y-m-d H:i:s', strtotime(@$oData->birthday)),
            'data_mail'          => @$oData->default_email,
            'data_photo'         => '',
        ));

    }


}