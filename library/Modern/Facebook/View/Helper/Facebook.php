<?php
/**
 * ModernWeb
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.modernweb.pl/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@modernweb.pl so we can send you a copy immediately.
 *
 * @category    Modern
 * @package     Modern_Facebook
 * @subpackage  View
 * @author      Rafał Gałka <rafal@modernweb.pl>
 * @copyright   Copyright (c) 2007-2012 ModernWeb (http://www.modernweb.pl)
 * @license     http://www.modernweb.pl/license/new-bsd     New BSD License
 */

/** @see Zend_View_Helper_HeadScript */
require_once 'Zend/View/Helper/HeadScript.php';

/**
 * Get facebook JS API init code.
 *
 * @category    Modern
 * @package     Modern_Facebook
 * @subpackage  View
 * @author      Rafał Gałka <rafal@modernweb.pl>
 * @copyright   Copyright (c) 2007-2012 ModernWeb (http://www.modernweb.pl)
 */
class Modern_Facebook_View_Helper_Facebook extends Zend_View_Helper_HeadScript
{
    const OPERA_FIX = 'if (window.opera) { FB.XD._transport="postmessage"; FB.XD.PostMessage.init(); }';

    /**
     * Registry key
     *
     * @var string
     */
    protected $_regKey = 'Modern_Facebook_View_Helper_Facebook';

    /**
     * @var Modern_Facebook
     */
    protected $_facebook;

    /**
     * @return Modern_Facebook
     */
    public function facebook()
    {
        $this->_facebook = Modern_Application::getInstance()->getResource('facebook');

        if (!$this->_facebook) {
            throw new Modern_Facebook_Exception('Facebook application resource not initialized');
        }

        return $this;
    }

    /**
     * @param string $indent
     * @todo handle application locale
     */
    public function toString($indent = null)
    {
        $options = $this->_facebook->getOptions();

        $script = '';
        if ($this->_facebook->isForceRedirectTo()) {
            $script .= $this->getForceRedirectScript();
        }

        $request = Zend_Controller_Front::getInstance()->getRequest();
        unset($options['secret']);
        $optionsJson = Zend_Json::encode($options);
        $options['channelUrl'] = '//' . $request->getHttpHost() . '/channel.html';

        $params = array_intersect_key($options, array_flip(array(
            'appId', 'channelUrl', 'status', 'cookie', 'xfbml',
            'frictionlessRequests',
        )));
        $paramsJson = Zend_Json::encode($params);

        $script .= '<div id="fb-root"></div>' . PHP_EOL;
        $script .= '<script>' . PHP_EOL;
        $script .= 'window.fbAsyncInit = function() {' . PHP_EOL;
        $script .= "    FB.init($paramsJson);" . PHP_EOL;
        $script .= '    FB.Canvas.setAutoGrow();' . PHP_EOL;
        $script .= '    ' . self::OPERA_FIX . PHP_EOL;
        $script .= '    if (window.jQuery) { $(document).trigger("facebookReady", ' . $optionsJson . '); }' . PHP_EOL;
        $script .= '};' . PHP_EOL;
        $script .= '' . PHP_EOL;
        $script .= '(function(d){' . PHP_EOL;
        $script .= '    var js, id = "facebook-jssdk", ref = d.getElementsByTagName("script")[0];' . PHP_EOL;
        $script .= '    if (d.getElementById(id)) {return;}' . PHP_EOL;
        $script .= '    js = d.createElement("script"); js.id = id; js.async = true;' . PHP_EOL;
        $script .= '    js.src = "//connect.facebook.net/pl_PL/all.js";' . PHP_EOL;
        $script .= '    ref.parentNode.insertBefore(js, ref);' . PHP_EOL;
        $script .= '}(document));' . PHP_EOL;
        $script .= '</script>' . PHP_EOL;

        return $script;
    }

    /**
     * @param string $url
     * @return string
     */
    public function getForceRedirectScript()
    {
        $target = $this->_facebook->getOption('forceRedirectTo');
        $url = Zend_Uri_Http::fromString($this->_facebook->getForceRedirectTargetUrl());

        $script  = '<script type="text/javascript">' . PHP_EOL;
        $script .= "if(top == self) {" . PHP_EOL;
        $script .= "    var elements = (top.location.href + '').split(/[\/\?]+/);" . PHP_EOL;
        $script .= "    var queryParams = elements.pop().split(/[?&]+/);" . PHP_EOL;
        $script .= "    var path = elements.splice(2).join('/');" . PHP_EOL;
        switch ($target) {
            case 'tab':
                $script .= "    queryParams.unshift('app_data=/' + path);" . PHP_EOL;
                $query = $url->setQuery(array());
                if ($query) {
                    $script .= "    queryParams.unshift('$query');" . PHP_EOL;
                }
                $script .= "    var url = '{$url->getUri()}?' + queryParams.join('&');" . PHP_EOL;
                break;
            case 'canvas':
                // @todo handle canvas redirect url
                $script .= "    var url = 'http://www.google.pl/'" . PHP_EOL;
                break;
        }
        $script .= "    top.location.href = url;" . PHP_EOL;
        $script .= '};' . PHP_EOL;
        $script .= '</script>' . PHP_EOL;

        return $script;
    }

}
