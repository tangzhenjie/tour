/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : tour

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2017-03-02 21:23:03
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `yunzhi_hotel`
-- ----------------------------
DROP TABLE IF EXISTS `yunzhi_hotel`;
CREATE TABLE `yunzhi_hotel` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '酒店id',
  `title` varchar(20) DEFAULT '' COMMENT '名称',
  `dress` varchar(30) DEFAULT '' COMMENT '地址',
  `phone` tinyint(11) unsigned DEFAULT NULL COMMENT '电话',
  `star` smallint(10) unsigned DEFAULT NULL COMMENT '酒店星级',
  `img_url` tinyint(30) DEFAULT NULL COMMENT '图片url',
  `content` char(255) DEFAULT '' COMMENT '酒店介绍',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of yunzhi_hotel
-- ----------------------------
