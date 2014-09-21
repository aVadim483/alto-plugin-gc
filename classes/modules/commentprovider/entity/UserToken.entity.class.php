<?php

/**
 * Token.entity.class.php
 * Файл сущности для модуля CommentProvider плагина Gc
 *
 * @author      Андрей Г. Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Г. Воронов
 *
 * @method int getTokenId
 * @method string getTokenData
 * @method string getTokenExpire
 * @method string getTokenUserId
 * @method string getTokenProviderName
 * @method string getTokenProviderUserId
 * @method string getTokenDataSecret
 *
 * @method void setTokenId
 * @method void setTokenData
 * @method void setTokenExpire
 * @method void setTokenUserId
 * @method void setTokenProviderName
 * @method void setTokenProviderUserId
 * @method void setTokenDataSecret
 */
class PluginGc_ModuleCommentProvider_EntityUserToken extends EntityORM {
    protected $aRelations = array(
        'user' => array(EntityORM::RELATION_TYPE_BELONGS_TO, 'ModuleUser_EntityUser', 'token_user_id'),
    );

    public function getUserByTokenUserId($iUserId) {
        return $this->User_GetUserById($iUserId);
    }


}