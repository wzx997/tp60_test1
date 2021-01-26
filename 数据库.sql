-- 用户表
CREATE TABLE `tp_user`(
    `id`          int(10)   NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `username`    varchar(32)        DEFAULT '' COMMENT '用户名',
    `mobile`      varchar(11)        DEFAULT '' COMMENT '手机号',
    `email`       varchar(100)       DEFAULT '' COMMENT '电子邮箱',
    `password`    varchar(100)       DEFAULT '' COMMENT '密码',
    `is_del`      tinyint(4)         DEFAULT '0' COMMENT '是否删除。0：有效，1：删除',
    `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`),
    KEY `username` (`username`),
    KEY `email` (`email`),
    KEY `mobile` (`mobile`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = utf8 COMMENT ='用户表';

-- 登录日志表
CREATE TABLE `tp_login_log`(
    `id`          int(10)   NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `user_id`     int(100)           DEFAULT '0' COMMENT '登录id',
    `token`       varchar(32)        DEFAULT '' COMMENT '用户token',
    `is_del`      tinyint(4)         DEFAULT '0' COMMENT '是否删除。0：有效，1：删除',
    `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间(登录时间)',
    `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    KEY `token` (`token`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = utf8 COMMENT ='登录日志表';

-- 验证码记录表
CREATE TABLE `tp_user_code`(
    `id`          int(10)   NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `user_id`     int(10)            DEFAULT '0' COMMENT '用户ID',
    `code`        varchar(132)       DEFAULT '' COMMENT '验证码',
    `expire_time` int(10)            DEFAULT '0' COMMENT '到期时间',
    `is_del`      tinyint(4)         DEFAULT '0' COMMENT '是否删除。0：有效，1：删除',
    `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = utf8 COMMENT ='找回密码验证码记录表';