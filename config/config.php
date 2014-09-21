<?php
/**
 * config
 * Файл конфигурационных параметров плагина Gc
 *
 * @author      Андрей Г. Воронов <andreyv@gladcode.ru>
 * @copyrights  Copyright © 2014, Андрей Г. Воронов
 *              Является частью плагина Gc
 * @version     0.0.1 от 03.09.2014 10:08
 */

/**
 * Таблицы БД плагина
 */
Config::Set('db.table.comment_provider_setting', '___db.table.prefix___social_setting');
Config::Set('db.table.comment_provider_user_token', '___db.table.prefix___comment_token');

/**
 * Роутеры плагина
 */
Config::Set('router.page.commentcaptcha', 'PluginGc_ActionCommentcaptcha');
Config::Set('router.page.gc', 'PluginGc_ActionGc');

/**
 * Параметры плагина
 */
$aConfig = array(

    // Гостевой логин. НЕ МЕНЯТЬ. Если очень хочется, то нужно
    // поменять его здесь и в файле common/plugins/gc/sql/install.sql.
    // Если плагин был уже установлен, то еще и в БД сайта
    'guest_login' => '0B8uAORUAmrIObE9QZ1U5QW8xakE',

    // Режим работы плагина, могут быть следующие варианты
    //  - mail: только при запросе email
    //  - social: только через авторизацию в социальных сетях
    //  - both: оба варианта
    'mode' => 'both',

    'salt' => 'a86aa4e3741832cc695bd7af93eae841104aaf4e6809ee22855f724f4a1ab8bf',

    'providers' => array(
        // Параметры авторизации через Vkontakte
        'vk'     => array(
            'name'           => 'vkontakte',
            'version'        => 2,
            'vk_client_id'   => '',
            'vk_secret_key'  => '',
            'vk_permissions' => array(
            ),
        ),

        // Параметры авторизации через Одноклассники
        // http://apiok.ru/wiki/pages/viewpage.action?pageId=42476652
        // Мои приложения - http://www.odnoklassniki.ru/dk?st.cmd=appsInfoMyDevList
        'od'     => array(
            'name'           => 'odnoklassniki',
            'version'        => 2,
            'od_client_id'   => '',
            'od_public_key'  => '',
            'od_secret_key'  => '',
            'od_permissions' => array(
            ),
        ),

        // Параметры авторизации через Facebook
        'fb'     => array(
            'name'           => 'facebook',
            'version'        => 2,
            'fb_client_id'   => '',
            'fb_secret_key'  => '',
            'fb_group_id' => '',
            'fb_permissions' => array(
                'email',
                'user_about_me',
            ),
        ),

        // Параметры авторизации через Мой Мир
        'mm'     => array(
            'name'           => 'mm',
            'version'        => 2,
            'has-receiver'   => TRUE,
            'mm_client_id'   => '',
            'mm_secret_key'  => '',
            'mm_permissions' => array(
                'stream'
            ),
        ),

        // Параметры авторизации через Yandex
        'ya'     => array(
            'name'           => 'ya',
            'version'        => 2,
            'ya_client_id'   => '',
            'ya_secret_key'  => '',
            'ya_permissions' => array(),
        ),


        // Параметры авторизации через Google
        'g'      => array(
            'name'          => 'google',
            'version'       => 2,
            'g_client_id'   => '',
            'g_secret_key'  => '',
            'g_permissions' => array(
                'https://www.googleapis.com/auth/userinfo.email',
                'https://www.googleapis.com/auth/userinfo.profile',
            ),
        ),

        // Параметры авторизации через Github
        'github' => array(
            'name'               => 'github',
            'version'            => 2,
            'application_name'   => '',
            'github_client_id'   => '',
            'github_secret_key'  => '',
            'github_permissions' => array(
                'user:email',
            ),

        ),

//         Параметры авторизации через linkedin
        'li'     => array(
            'name'           => 'linkedin',
            'version'        => 2,
            'li_client_id'   => '',
            'li_secret_key'  => '',
            'li_permissions' => array(
                'r_fullprofile',
                'r_emailaddress',
            ),
        ),

//         Параметры авторизации через instagramm
        'i'     => array(
            'name'           => 'instagram',
            'version'        => 2,
            'i_client_id'   => '',
            'i_secret_key'  => '',
            'i_permissions' => array(
                'basic',
            ),
        ),
    ),

);

return $aConfig;