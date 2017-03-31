'use strict';

/**
 * @ngdoc filter
 * @name wechatApp.filter:minvalue
 * @function
 * @description
 * # minvalue
 * Filter in the wechatApp.
 */
angular.module('wechatApp')
  .filter('minvalue', function () {
  	// 求一个数组中的最小值
    return function (values) {
     var minValue = 10000000;
            angular.forEach(values, function(value) {
                if (value.money <  minValue) {
                    minValue = value.money;
                }
            });
            return minValue;
    };
  });
