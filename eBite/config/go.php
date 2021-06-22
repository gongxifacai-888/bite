<?php

return [
    //中间件通讯ip
    'go_middleware_host' => env('GO_MIDDLEWARE_HOST', ''),

    //中间件通讯ip
    'go_middleware_key' => env('GO_MIDDLEWARE_KEY', ''),

    //app链上名称
    'project_name' => env('GO_PROJECT_NAME', 'NULL'),

    //区块链节点服务器ip
    'node_server_host' => env('GO_NODE_SERVER_HOST', 'lbxchain.ganzjv.cn:82'),

    //加密私钥的地址,一般是节点服务器ip:89
    'encrypt_host' => env('GO_ENCRYPT_HOST', '127.0.0.1:89'),

];
