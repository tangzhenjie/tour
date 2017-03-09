'use strict';

/**
 * @ngdoc service
 * @name wechatApp.config
 * @description
 * # config
 * Constant in the wechatApp.
 */
angular.module('wechatApp')
  .constant('config', {
    appId:'sfdfdfdsfsdf',
    version:'0.0.1',
    cookies: {
        prefix: 'wechat_',        // cookie 名称前缀
        expire:  0,               // cookie 保存时间
    },
    oauthUrl: 'http://127.0.0.1/tour/public/index.php/wechat/OAuth/index',         // autho认证文件
    apiUrl: 'http://127.0.0.1/tour/public/index.php/api/',
    siteUrl: 'http://127.0.0.1/tour/',
  });
