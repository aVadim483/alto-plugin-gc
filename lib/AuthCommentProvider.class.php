<?php

/**
 * Класс провайдера авторизации
 *
 * Class AuthCommentProvider
 */
abstract class AuthCommentProvider {

    /**
     * Версия OAuth
     * @var int
     */
    public $iVersion = 2;


    /**
     * Наименование провайдера. Например 'vk', 'fb', 'tw'
     * @var string
     */
    public $sName = '';

    /**
     * Идентификатор приложения
     * @var string
     */
    protected $sClientId = '';

    /**
     * Секретный ключ приложения
     * @var string
     */
    protected $sSecretKey = '';

    /**
     * Код ошибки
     * @var bool|int
     */
    private $iLastErrorCode = FALSE;

    /**
     * Урл авторизации
     * @var string
     */
    public $sAuthUrl = '';
    /**
     * Урл получения токена
     * @var string
     */
    public $sTokenUrl = '';

    /**
     * Урл получения дополнительной информации о пользователе
     *
     * @var string
     */
    public $sUserInfoUrl = '';

    /**
     * Использовать курл или нет
     * @var bool
     */
    private $bUseCurl = TRUE;

    /**
     * Права, которые разрешает пользователь
     * @var array
     */
    private $aPermissions = array();

    /**
     * Url возврата
     * @var string
     */
    protected $sRedirect = '';

    /**
     * Идентификатор группы
     *
     * @var bool|string
     */
    public $sGroupId = FALSE;

    public $sPermissionsGutter = ',';

    /**
     * Объект токена пользователя
     * @var bool|PluginGc_ModuleCommentProvider_EntityUserToken
     */
    protected $oToken = FALSE;

    /**
     * Геттер токена
     *
     * @return bool|PluginGc_ModuleCommentProvider_EntityUserToken
     */
    public function getToken() {
        if ($this->oToken) {
            return $this->oToken;
        }

        $this->oToken = $this->GetUserToken();

        return $this->oToken;
    }

    /**
     * Устанавливает токен пользователя
     *
     * @param PluginGc_ModuleCommentProvider_EntityUserToken $oToken
     */
    public function setToken($oToken) {
        $this->oToken = $oToken;
    }

    /**
     * Энкодирует строки и массивы
     *
     * @param $data
     * @return array|mixed|string
     */
    protected function _urlencode_rfc3986($data) {
        if (is_array($data)) {
            return array_map(array(__CLASS__, '_urlencode_rfc3986'), $data);
        } elseif (is_scalar($data)) {
            return str_replace(array(
                '+',
                '~',
                '!',
                '*',
                "'",
                '(',
                ')'
            ), array(
                ' ',
                '%7E',
                '%21',
                '%2A',
                '%27',
                '%28',
                '%29'
            ), rawurlencode($data));
        } else {
            return '';
        }
    }

//    protected function _urlencode_rfc3986($input) {
//        if (is_array($input)) {
//            return array_map(array(__CLASS__, '_urlencode_rfc3986'), $input);
//        } else if (is_scalar($input)) {
//            return str_replace('+', ' ', str_replace('%7E', '~', rawurlencode($input)));
//        } else {
//            return '';
//        }
//    }


    /**
     * Получение ошибки в виде массива [code, description]
     * @return bool|int
     */
    public function getLastError() {

        if ($this->iLastErrorCode) {

            return Config::Get(
                'plugin.ar.error_code_' . $this->iLastErrorCode,
                array(
                    'provider_name' => $this->sName
                )
            );

        }

        return FALSE;
    }

    /**
     * @param bool|int $iLastErrorCode
     */
    public function setLastErrorCode($iLastErrorCode) {
        $this->iLastErrorCode = $iLastErrorCode;
    }


    /**
     * Конструктор класса
     *
     * @param string $sName Имя провайдера
     * @param array $aConfig Секретные ключи
     * @param bool $bUseCurl Использовать курл или нет
     */
    public function __construct($sName, $aConfig, $bUseCurl) {
        $this->sName = $sName;

        /**
         * Легкая валидация данных. Только на наличие
         */
        // Проверим идентификатор приложения
        if (!isset($aConfig[$this->sName . '_' . 'client_id']) || empty($aConfig[$this->sName . '_' . 'client_id'])) {
            $this->iLastErrorCode = 1;

            return;
        }

        // Проверим секретный ключ
        if (!isset($aConfig[$this->sName . '_' . 'secret_key']) || empty($aConfig[$this->sName . '_' . 'secret_key'])) {
            $this->iLastErrorCode = 2;

            return;
        }


        /**
         * Установим секретные параметры
         */
        $this->sClientId = $aConfig[$this->sName . '_' . 'client_id'];
        $this->sSecretKey = $aConfig[$this->sName . '_' . 'secret_key'];
        $this->sGroupId = isset($aConfig[$this->sName . '_' . 'group_id']) ? $aConfig[$this->sName . '_' . 'group_id'] : FALSE;

        $this->iVersion = $aConfig['version'];
        if ($aConfig[$this->sName . '_' . 'permissions']) {
            $this->aPermissions = $aConfig[$this->sName . '_' . 'permissions'];
        }

        $this->bUseCurl = $bUseCurl;

        $this->sRedirect = Router::GetPath('gc') . $this->sName;

        $this->Init();
    }

    /**
     * Получение массива прав
     * @return array
     */
    public function getPermissions() {
        return $this->aPermissions;
    }

    /**
     * Отрезает 140 символов текста твита
     *
     * @param $sText
     * @param string $sPostfix
     * @param int $iLength
     * @return mixed
     */
    protected function CropText($sText, $sPostfix = '...', $iLength = 140) {

        $nLen = mb_strlen($sText, 'UTF-8');
        if ($iLength && $nLen > $iLength) {
            $sText = F::TruncateText($sText, $iLength, $sPostfix, TRUE);
        }

        return $sText;

    }


    /**
     * Получение массива прав
     * @return array
     */
    public function getStringPermissions() {
        return implode($this->sPermissionsGutter, $this->aPermissions);
    }

    /**
     * Подставляет параметры в url
     *
     * @param string $sUrl
     * @param array $aAdditionalData
     * @return mixed
     */
    public function EvalUrl($sUrl, $aAdditionalData = array()) {
        return str_replace(
            array_merge(array('%%client_id%%', '%%secret_key%%', '%%permissions%%', '%%redirect%%'), array_keys($aAdditionalData)),
            array_merge(array($this->sClientId, $this->sSecretKey, $this->getStringPermissions(), urlencode($this->sRedirect)), array_values($aAdditionalData)),
            $sUrl
        );
    }

    /**
     * Инициализация класса провайдера
     */
    public function Init() {

        /**
         * В группу репостят только админы
         */
        if (!E::IsAdmin()) {
            $this->sGroupId = FALSE;
            $this->aRepostRights['group'] = FALSE;
        }

        /**
         * Сформируем url авторизации и токена
         */
        $this->sAuthUrl = $this->EvalUrl($this->sAuthUrl);

        if ($this->iVersion == 1) {
            $this->sAuthUrl = Config::Get('path.root.web') . 'auth/1/' . $this->sName;
        }

    }

    /**
     * Отправляет запрос авторизации серверу
     *
     * @param string $sUrl Урл запроса
     * @param bool $bPost
     * @param bool|array $aHeaders
     * @return bool|stdClass
     */
    protected function SendRequest($sUrl, $bPost = TRUE, $aHeaders = FALSE) {

        if ($this->bUseCurl) {

            if ($bPost) {
                list($sUrl, $sParams) = explode('?', $sUrl);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $sUrl);
                curl_setopt($ch, CURLOPT_POST, TRUE);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $sParams);
            } else {
                $ch = curl_init($sUrl);

            }

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            if ($aHeaders) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeaders);
            }

            $sQueryResult = @curl_exec($ch);
            $err = curl_errno($ch);
            $errmsg = curl_error($ch);
            curl_close($ch);

        } else {
            $sQueryResult = @file_get_contents($sUrl);
        }

        return $sQueryResult;
    }

    protected function isJson($string) {
        json_decode($string);

        return (json_last_error() == JSON_ERROR_NONE);
    }

    protected function DecodeGetString($string) {
        $query = explode('&', $string);
        $params = array();

        foreach ($query as $param) {
            list($name, $value) = explode('=', $param);
            $params[urldecode($name)] = urldecode($value);
        }

        return (object)$params;
    }

    /**
     * Получение данных о токене от провайдера
     *
     * @param bool $bPost
     * @param string $sCodeParamName
     * @param bool|array $aHeaders
     * @param bool|array $aAdditionalData
     * @return bool
     */
    protected function LoadTokenData($bPost = TRUE, $sCodeParamName = 'code', $aHeaders = FALSE, $aAdditionalData = FALSE) {

        if (getRequest('error') || !($sCode = getRequest($sCodeParamName, FALSE))) {
            $this->setLastErrorCode(3);

            return FALSE;
        }

        $this->sTokenUrl = $this->EvalUrl($this->sTokenUrl, (!$aAdditionalData ? array("%%{$sCodeParamName}%%" => $sCode) : array_merge(array("%%{$sCodeParamName}%%" => $sCode), $aAdditionalData)));
        $aData = $this->SendRequest($this->sTokenUrl, $bPost, $aHeaders);

        // Если пришла ошибка получения токена
        if (!$aData) {
            $this->setLastErrorCode(3);

            return FALSE;
        }

        if ($this->isJson($aData)) {
            $aData = json_decode($aData);
        } else {
            $aData = $this->DecodeGetString($aData);
        }


        if (@$aData->error || !@$aData->access_token) {
            $this->setLastErrorCode(3);

            return FALSE;
        }

        return $aData;
    }

    /**
     * Получение дополнительных данных от провайдера
     *
     * @param $oToken
     * @param $aParam
     * @param bool $bPost
     * @param bool|array $aHeaders
     * @return bool
     */
    function LoadAdditionalData($oToken, $aParam, $bPost = TRUE, $aHeaders = FALSE) {
        // Токен не получен :(
        if (!$oToken) {
            return FALSE;
        }

        $this->sUserInfoUrl = $this->EvalUrl($this->sUserInfoUrl, $aParam);

        $aData = $this->SendRequest($this->sUserInfoUrl, $bPost, $aHeaders);
        if (!$aData) {
            $this->setLastErrorCode(3);

            return FALSE;
        }

        return $aData;
    }

    /**
     * Преобразует в stdObj из json и строки get-запроса
     * @param $aData
     * @return mixed|object
     */
    protected function EvalData($aData) {
        if ($this->isJson($aData)) {
            $aData = json_decode($aData);
        } else {
            $aData = $this->DecodeGetString($aData);
        }

        return $aData;
    }

    /**
     * Получает доп. данные пользователя
     *
     * @param PluginGc_ModuleCommentProvider_EntityUserToken $oToken
     * @return PluginGc_ModuleCommentProvider_EntityData
     */
    abstract public function GetUserData(PluginGc_ModuleCommentProvider_EntityUserToken $oToken);

    abstract public function GetUserToken();

    public function PrepareAuthPath() {
        return '/';
    }

    /**
     * Обновляет токен, если это нужно
     *
     * @param PluginGc_ModuleCommentProvider_EntityUserToken $oToken
     */
    public function RefreshToken($oToken) {

        if ($oToken->getTokenExpire() < time()) {
            Engine::getInstance()->Message_AddErrorSingle(
                Engine::getInstance()->Lang_Get('plugin.ar.repost_error_token_expire')
            );
        }

    }


}