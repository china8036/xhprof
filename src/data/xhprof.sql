/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * Author:  wang
 * Created: 2018-3-9
 */

CREATE TABLE `xhprof` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `path` char(255) NOT NULL DEFAULT '' COMMENT 'path',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT 'url',
  `post_data` varchar(255) NOT NULL DEFAULT '' COMMENT 'post data',
  `expended_time` char(20) NOT NULL DEFAULT '' COMMENT 'expended_time',
  `project` char(20) NOT NULL DEFAULT '' COMMENT 'project name',
  `create_at` int(11) NOT NULL COMMENT 'create_at',
  PRIMARY KEY (`id`),
  KEY (`path`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='xhprof信息表';


CREATE TABLE `xdetail` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `xid` int(10) unsigned NOT NULL COMMENT 'xhprof id',
  `content` mediumtext COMMENT '内容',
  `create_at` int(11) NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`),
  INDEX(`xid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='xhprof信息存储内容';
