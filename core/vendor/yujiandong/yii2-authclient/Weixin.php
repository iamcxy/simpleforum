<?php

namespace yujiandong\authclient;

use yii\authclient\OAuth2;
use yii\web\HttpException;
use Yii;

/**
 * Weixin(Wechat) allows authentication via Weixin(Wechat) OAuth.
 *
 * In order to use Weixin(Wechat) OAuth you must register your application at <https://open.weixin.qq.com/>.
 *
 * Example application configuration:
 *
 * ~~~
 * 'components' => [
 *     'authClientCollection' => [
 *         'class' => 'yii\authclient\Collection',
 *         'clients' => [
 *             'weixin' => [
 *                 'class' => 'yujiandong\authclient\Weixin',
 *                 'clientId' => 'weixin_appid',
 *                 'clientSecret' => 'weixin_appkey',
 *             ],
 *         ],
 *     ]
 *     ...
 * ]
 * ~~~
 *
 * @see https://open.weixin.qq.com/
 * @see https://open.weixin.qq.com/cgi-bin/showdocument?action=dir_list&t=resource/res_list&verify=1&lang=zh_CN
 *
 * @author Jiandong Yu <flyyjd@gmail.com>
 */
class Weixin extends OAuth2
{

    /**
     * {@inheritdoc}
     */
    public $authUrl = 'https://open.weixin.qq.com/connect/qrconnect';
    /**
     * {@inheritdoc}
     */
    public $tokenUrl = 'https://api.weixin.qq.com/sns/oauth2/access_token';
    /**
     * {@inheritdoc}
     */
    public $apiBaseUrl = 'https://api.weixin.qq.com';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if ($this->scope === null) {
            $this->scope = implode(' ', [
                'snsapi_login',
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultNormalizeUserAttributeMap()
    {
        return [
            'id' => 'openid',
            'username' => 'nickname',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function buildAuthUrl(array $params = [])
    {
        $params['appid'] = $this->clientId;
        return parent::buildAuthUrl($params);
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAccessToken($authCode, array $params = [])
    {
        $params['appid'] = $this->clientId;
        $params['secret'] = $this->clientSecret;
        return parent::fetchAccessToken($authCode, $params);
    }

    /**
     * {@inheritdoc}
     */
    protected function initUserAttributes()
    {
        return $this->api('sns/userinfo');
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultReturnUrl()
    {
        $params = $_GET;
        unset($params['code']);
        unset($params['state']);
        $params[0] = Yii::$app->controller->getRoute();

        return Yii::$app->getUrlManager()->createAbsoluteUrl($params);
    }

    /**
     * Generates the auth state value.
     * @return string auth state value.
     */
    protected function generateAuthState()
    {
        return sha1(uniqid(get_class($this), true));
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultName()
    {
        return 'weixin';
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultTitle()
    {
        return '微信';
    }

    /**
     * {@inheritdoc}
     */
    protected function defaultViewOptions()
    {
        return [
            'popupWidth' => 800,
            'popupHeight' => 500,
        ];
    }

    /**
     * {{@inheritdoc}}
     */
    public function applyAccessTokenToRequest($request, $accessToken)
    {
        $data = $request->getData();
        $data['openid'] = $accessToken->getParam('openid');
        $data['access_token'] = $accessToken->getToken();
        $request->setData($data);
    }

}
