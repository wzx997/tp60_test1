<?php

return [
    // 默认磁盘
    'default' => env('filesystem.driver', 'local'),
    // 磁盘列表
    'disks'   => [
        'local'  => [
            'type' => 'local',
            'root' => app()->getRuntimePath() . 'storage',
        ],
        'public' => [
            // 磁盘类型
            'type'       => 'local',
            // 磁盘路径
            'root'       => app()->getRootPath() . 'public/storage',
            // 磁盘路径对应的外部URL路径
            'url'        => '/storage',
            // 可见性
            'visibility' => 'public',
        ],
        // 更多的磁盘配置信息
        /*   配置上传的磁盘，磁盘路径用配置文件指定，上传文件的时候选择到该磁盘,
        即配置disk()方法选择到该磁盘，为了不对原框架产生影响，这里指定默认磁盘
        而是通过该方法选择。
        */
        'upload'  => [
            'type' => 'local',
            // 读取配置文件，如果找不到就放在runtime目录下
            'root' => env('filesystem.uploadpath', app()->getRuntimePath()) . 'upload/image',
        ],
    ],
];
