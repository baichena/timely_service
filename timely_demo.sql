/*
Navicat MySQL Data Transfer

Source Server         : 192.168.88.133
Source Server Version : 50726
Source Host           : 192.168.88.133:3306
Source Database       : timely_demo

Target Server Type    : MYSQL
Target Server Version : 50726
File Encoding         : 65001

Date: 2019-10-30 18:34:05
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for chat_log
-- ----------------------------
DROP TABLE IF EXISTS `chat_log`;
CREATE TABLE `chat_log` (
  `log_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '日志id',
  `from_id` varchar(32) NOT NULL COMMENT '发送者id',
  `from_name` varchar(55) NOT NULL COMMENT '发送者名称',
  `from_avatar` varchar(255) NOT NULL COMMENT '发送者头像',
  `to_id` varchar(55) NOT NULL COMMENT '接收方id',
  `to_name` varchar(55) NOT NULL COMMENT '接受者名称',
  `to_avatar` varchar(255) NOT NULL COMMENT '接收者头像',
  `message` text NOT NULL COMMENT '发送的内容',
  `send_status` tinyint(1) DEFAULT '1' COMMENT '发送状态 1发送成功  2发送失败   可以重发',
  `read_flag` tinyint(1) DEFAULT '1' COMMENT '是否已读 1 未读 2 已读',
  `create_time` datetime NOT NULL COMMENT '记录时间',
  PRIMARY KEY (`log_id`),
  KEY `from_id` (`from_id`) USING BTREE,
  KEY `to_id` (`to_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=124 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of chat_log
-- ----------------------------
INSERT INTO `chat_log` VALUES ('120', '225db94641ddafe', '游客225db94641ddafe', '/static/common/images/visitor.jpg', 'KF_kefu5db8eafbe332f', '尔耳', '/static/common/images/kefu.jpg', '尔特人', '1', '1', '2019-10-30 18:28:48');
INSERT INTO `chat_log` VALUES ('121', '225db946744b2ca', '游客225db946744b2ca', '/static/common/images/visitor.jpg', 'KF_kefu5db8eafbe332f', '尔耳', '/static/common/images/kefu.jpg', '尔特人', '1', '1', '2019-10-30 18:28:53');
INSERT INTO `chat_log` VALUES ('122', '225db946744b2ca', '游客225db946744b2ca', '/static/common/images/visitor.jpg', 'KF_kefu5db8eafbe332f', '尔耳', '/static/common/images/kefu.jpg', '1212', '1', '1', '2019-10-30 18:29:23');
INSERT INTO `chat_log` VALUES ('123', '225db94641ddafe', '游客225db94641ddafe', '/static/common/images/visitor.jpg', 'KF_kefu5db8eafbe332f', '尔耳', '/static/common/images/kefu.jpg', '是的发生的', '1', '1', '2019-10-30 18:29:30');

-- ----------------------------
-- Table structure for kefu_info
-- ----------------------------
DROP TABLE IF EXISTS `kefu_info`;
CREATE TABLE `kefu_info` (
  `kefu_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '客服id',
  `kefu_code` varchar(32) NOT NULL COMMENT '客服唯一标识',
  `kefu_name` varchar(55) NOT NULL COMMENT '客服名称',
  `kefu_avatar` varchar(55) NOT NULL COMMENT '客服头像',
  `kefu_password` varchar(32) NOT NULL COMMENT '客服密码',
  `kefu_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '客服状态 1激活  2禁用',
  `online_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '在线状态 1 在线 0 离线',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '更新时间',
  `client_id` varchar(32) DEFAULT NULL COMMENT '客服登录标示',
  PRIMARY KEY (`kefu_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of kefu_info
-- ----------------------------
INSERT INTO `kefu_info` VALUES ('21', 'kefu5db8128b0553e', '客服啊', '/static/common/images/kefu.jpg', 'e10adc3949ba59abbe56e057f20f883e', '1', '0', '2019-10-29 18:20:59', '2019-10-29 18:20:59', '');
INSERT INTO `kefu_info` VALUES ('22', 'kefu5db8eafbe332f', '尔耳', '/static/common/images/kefu.jpg', 'e10adc3949ba59abbe56e057f20f883e', '1', '1', '2019-10-30 09:44:27', '2019-10-30 09:44:27', '56');

-- ----------------------------
-- Table structure for visitor
-- ----------------------------
DROP TABLE IF EXISTS `visitor`;
CREATE TABLE `visitor` (
  `vid` int(11) NOT NULL AUTO_INCREMENT,
  `visitor_id` varchar(32) NOT NULL COMMENT '访客id',
  `visitor_name` varchar(55) NOT NULL COMMENT '访客名称',
  `visitor_avatar` varchar(155) NOT NULL COMMENT '访客头像',
  `visitor_ip` varchar(15) DEFAULT NULL COMMENT '访客ip',
  `client_id` varchar(32) NOT NULL COMMENT '客户端标识',
  `online_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0 离线 1 在线',
  `create_time` datetime NOT NULL COMMENT '访问时间',
  PRIMARY KEY (`vid`),
  KEY `visiter` (`visitor_id`) USING BTREE,
  KEY `time` (`create_time`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=194 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of visitor
-- ----------------------------
INSERT INTO `visitor` VALUES ('192', '225db94641ddafe', '游客225db94641ddafe', '/static/common/images/visitor.jpg', '127.0.0.1', '50', '1', '2019-10-30 18:28:34');
INSERT INTO `visitor` VALUES ('193', '225db946744b2ca', '游客225db946744b2ca', '/static/common/images/visitor.jpg', '127.0.0.1', '53', '1', '2019-10-30 18:28:40');

-- ----------------------------
-- Table structure for visitor_queue
-- ----------------------------
DROP TABLE IF EXISTS `visitor_queue`;
CREATE TABLE `visitor_queue` (
  `qid` int(11) NOT NULL AUTO_INCREMENT COMMENT '队列id',
  `visitor_id` varchar(32) NOT NULL COMMENT '访客id',
  `visitor_name` varchar(55) NOT NULL COMMENT '访客名称',
  `visitor_avatar` varchar(155) NOT NULL COMMENT '访客头像',
  `visitor_ip` varchar(15) DEFAULT NULL COMMENT '访客ip',
  `client_id` varchar(32) NOT NULL COMMENT '客户端标识',
  `create_time` datetime NOT NULL COMMENT '访问时间',
  `reception_status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '接待状态  0 等待接待中 1 接待中  2接待完成',
  `kefu_code` varchar(100) DEFAULT NULL,
  `kefu_client_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`qid`),
  UNIQUE KEY `id` (`visitor_id`) USING BTREE,
  KEY `visiter` (`visitor_id`) USING BTREE,
  KEY `time` (`create_time`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of visitor_queue
-- ----------------------------
INSERT INTO `visitor_queue` VALUES ('8', '225db94641ddafe', '游客225db94641ddafe', '/static/common/images/visitor.jpg', '127.0.0.1', '50', '2019-10-30 18:28:34', '1', 'kefu5db8eafbe332f', '49');
INSERT INTO `visitor_queue` VALUES ('9', '225db946744b2ca', '游客225db946744b2ca', '/static/common/images/visitor.jpg', '127.0.0.1', '53', '2019-10-30 18:28:40', '1', 'kefu5db8eafbe332f', '52');

-- ----------------------------
-- Table structure for visitor_service_log
-- ----------------------------
DROP TABLE IF EXISTS `visitor_service_log`;
CREATE TABLE `visitor_service_log` (
  `vsid` int(11) NOT NULL AUTO_INCREMENT COMMENT '服务编号',
  `visitor_id` varchar(55) NOT NULL COMMENT '访客id',
  `client_id` varchar(32) NOT NULL COMMENT '访客的客户端标识',
  `visitor_name` varchar(55) NOT NULL COMMENT '访客名称',
  `visitor_avatar` varchar(155) NOT NULL COMMENT '访客头像',
  `visitor_ip` varchar(15) NOT NULL COMMENT '访客的ip',
  `kefu_id` int(11) NOT NULL,
  `kefu_code` varchar(32) NOT NULL DEFAULT '0' COMMENT '接待的客服标识',
  `start_date` datetime NOT NULL COMMENT '开始服务时间',
  `end_date` datetime DEFAULT NULL COMMENT '结束服务时间',
  `connect_stauts` tinyint(3) NOT NULL DEFAULT '1' COMMENT '连接状态  1 正在连接  2 关闭连接',
  PRIMARY KEY (`vsid`),
  KEY `user_id,client_id` (`visitor_id`,`client_id`) USING BTREE,
  KEY `kf_id,start_time,end_time` (`kefu_code`,`start_date`,`end_date`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3089 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of visitor_service_log
-- ----------------------------
INSERT INTO `visitor_service_log` VALUES ('3071', '225db94641ddafe', '2', '游客225db94641ddafe', '/static/common/images/visitor.jpg', '127.0.0.1', '22', 'kefu5db8eafbe332f', '2019-10-30 16:13:54', null, '1');
INSERT INTO `visitor_service_log` VALUES ('3072', '225db946744b2ca', '5', '游客225db946744b2ca', '/static/common/images/visitor.jpg', '127.0.0.1', '22', 'kefu5db8eafbe332f', '2019-10-30 16:14:44', null, '1');
INSERT INTO `visitor_service_log` VALUES ('3073', '225db946744b2ca', '6', '游客225db946744b2ca', '/static/common/images/visitor.jpg', '127.0.0.1', '22', 'kefu5db8eafbe332f', '2019-10-30 16:14:50', null, '1');
INSERT INTO `visitor_service_log` VALUES ('3074', '225db94641ddafe', '49', '游客225db94641ddafe', '/static/common/images/visitor.jpg', '127.0.0.1', '22', 'kefu5db8eafbe332f', '2019-10-30 16:35:18', null, '1');
INSERT INTO `visitor_service_log` VALUES ('3075', '225db94641ddafe', '109', '游客225db94641ddafe', '/static/common/images/visitor.jpg', '127.0.0.1', '22', 'kefu5db8eafbe332f', '2019-10-30 17:40:40', null, '1');
INSERT INTO `visitor_service_log` VALUES ('3076', '225db94641ddafe', '2', '游客225db94641ddafe', '/static/common/images/visitor.jpg', '127.0.0.1', '22', 'kefu5db8eafbe332f', '2019-10-30 17:42:07', null, '1');
INSERT INTO `visitor_service_log` VALUES ('3077', '225db94641ddafe', '4', '游客225db94641ddafe', '/static/common/images/visitor.jpg', '127.0.0.1', '22', 'kefu5db8eafbe332f', '2019-10-30 17:42:10', null, '1');
INSERT INTO `visitor_service_log` VALUES ('3078', '225db94641ddafe', '6', '游客225db94641ddafe', '/static/common/images/visitor.jpg', '127.0.0.1', '22', 'kefu5db8eafbe332f', '2019-10-30 17:44:51', null, '1');
INSERT INTO `visitor_service_log` VALUES ('3079', '225db94641ddafe', '8', '游客225db94641ddafe', '/static/common/images/visitor.jpg', '127.0.0.1', '22', 'kefu5db8eafbe332f', '2019-10-30 17:46:21', null, '1');
INSERT INTO `visitor_service_log` VALUES ('3080', '225db94641ddafe', '10', '游客225db94641ddafe', '/static/common/images/visitor.jpg', '127.0.0.1', '22', 'kefu5db8eafbe332f', '2019-10-30 17:46:49', null, '1');
INSERT INTO `visitor_service_log` VALUES ('3081', '225db94641ddafe', '12', '游客225db94641ddafe', '/static/common/images/visitor.jpg', '127.0.0.1', '22', 'kefu5db8eafbe332f', '2019-10-30 17:56:04', null, '1');
INSERT INTO `visitor_service_log` VALUES ('3082', '225db946744b2ca', '13', '游客225db946744b2ca', '/static/common/images/visitor.jpg', '127.0.0.1', '22', 'kefu5db8eafbe332f', '2019-10-30 17:56:09', null, '1');
INSERT INTO `visitor_service_log` VALUES ('3083', '225db94641ddafe', '19', '游客225db94641ddafe', '/static/common/images/visitor.jpg', '127.0.0.1', '22', 'kefu5db8eafbe332f', '2019-10-30 18:02:00', null, '1');
INSERT INTO `visitor_service_log` VALUES ('3084', '225db94641ddafe', '28', '游客225db94641ddafe', '/static/common/images/visitor.jpg', '127.0.0.1', '22', 'kefu5db8eafbe332f', '2019-10-30 18:06:28', null, '1');
INSERT INTO `visitor_service_log` VALUES ('3085', '225db94641ddafe', '31', '游客225db94641ddafe', '/static/common/images/visitor.jpg', '127.0.0.1', '22', 'kefu5db8eafbe332f', '2019-10-30 18:07:27', null, '1');
INSERT INTO `visitor_service_log` VALUES ('3086', '225db94641ddafe', '41', '游客225db94641ddafe', '/static/common/images/visitor.jpg', '127.0.0.1', '22', 'kefu5db8eafbe332f', '2019-10-30 18:18:51', null, '1');
INSERT INTO `visitor_service_log` VALUES ('3087', '225db94641ddafe', '50', '游客225db94641ddafe', '/static/common/images/visitor.jpg', '127.0.0.1', '22', 'kefu5db8eafbe332f', '2019-10-30 18:28:34', null, '1');
INSERT INTO `visitor_service_log` VALUES ('3088', '225db946744b2ca', '53', '游客225db946744b2ca', '/static/common/images/visitor.jpg', '127.0.0.1', '22', 'kefu5db8eafbe332f', '2019-10-30 18:28:40', null, '1');
