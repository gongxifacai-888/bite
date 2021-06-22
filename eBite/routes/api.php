<?php

Route::namespace('Api')->middleware(['xss_filter'])->group(function () {
    Route::middleware('auth_api')->group(function () {
        // 法币交易
        Route::prefix('otc')->group(function () {
            // 商家相关操作
            Route::middleware('check_seller')->group(function () {
                Route::get('seller_currencies', 'OtcController@sellerCurrencies');           // 获取商家支持的OTC币种
                Route::post('post_master', 'OtcController@postMaster')
                    ->middleware(['tx_auth', 'real_name_auth']);                             // 发布一个挂单(广告)
                Route::post('start_master', 'OtcController@startMaster');                    // 开启挂单交易
                Route::post('pause_master', 'OtcController@pauseMaster');                    // 暂停挂单交易(暂停后不会再被用户匹配)
                Route::post('cancel_master', 'OtcController@cancelMaster');                  // 撤回挂单(限挂单下没有进行中的交易)
                Route::get('seller_masters', 'OtcController@sellerMasters');                 // 商家挂单查询
                Route::get('master_details', 'OtcController@masterDetails');                 // 查挂单下所有交易明细
                Route::post('seller_currency_apply', 'SellerController@applySellerCurrency');//商家申请某币种权限
            });

            // 用户相关操作
            Route::get('masters', 'OtcController@masters');              // 市场所有挂单
            Route::get('detail', 'OtcController@detail');                // 按id查交易
            Route::get('detail_actions', 'OtcController@detailActions'); // 交易明细状态追踪
            Route::get('user_details', 'OtcController@userDetails');     // 用户的交易明细

            Route::post('match_master', 'OtcController@matchMaster')->middleware(['tx_auth', 'real_name_auth']); // 匹配交易
            Route::post('pay', 'OtcController@pay');                                                             // 确认支付(须上传凭证)
            Route::post('confirm', 'OtcController@confirm');                                                     // 确认交易
            Route::post('cancel', 'OtcController@cancel');                                                       // 取消交易(限买家)
            Route::post('defer', 'OtcController@defer');                                                         // 延期确认
            Route::post('arbitrate', 'OtcController@arbitrate');                                                 //申请维权仲裁

            //商家申请
            Route::post('seller_apply', 'SellerController@applySeller');
        });
        //日志相关
        Route::prefix('account_log')->group(function () {
            Route::get('change', 'AccountLogController@change');
            Route::get('legal', 'AccountLogController@legal');
            Route::get('lever', 'AccountLogController@lever');
            Route::get('micro', 'AccountLogController@micro');
            Route::get('financial', 'AccountLogController@financial');
        });
        //用户实名
        Route::prefix('user_real')->group(function () {
            Route::get('center', 'UserController@userCenter');   //个人中心
            Route::post('real_name', 'UserController@realName'); //身
        });

        Route::prefix('user')->group(function () {
            Route::post('amend_pay_password', 'UserController@amendPayPassword'); //修改支付密码
            Route::post('amend_password', 'UserController@amendPassword');        //修改密码
            Route::get('info', 'UserController@info');                            //账号详情
            Route::post('logout', 'UserController@logout');                       //注销

            Route::get('authorization_code', 'UserController@authCode');//添加代理商时用户的授权码
        });
        //用户反馈
        Route::prefix('feedback')->group(function () {
            Route::get('type', 'FeedbackController@type');          //反馈类型
            Route::get('list', 'FeedbackController@list');          //用户反馈列表
            Route::post('feedback', 'FeedbackController@feedback'); //用户反馈
        });

        Route::prefix('account')->group(function () {
            Route::post('transfer', 'AccountController@transfer');       //划转
            Route::get('transfer_log', 'AccountController@transferLog'); //划转日志

            Route::post('draw', 'AccountController@draw')
                ->middleware(['check_pay_password']);             //提币
            Route::get('draw_list', 'AccountController@drawList');//提币列表
            Route::post('convert', 'AccountController@convert');//兑换
            Route::get('detail', 'AccountController@detail');     //钱包详情
            Route::get('list', 'AccountController@list');
        });

        //币币交易买入
        Route::prefix('tx_in')->group(function () {
            Route::post('add', 'TxInController@add')->middleware(['tx_auth', 'real_name_auth']);
            Route::get('cancel', 'TxInController@cancel');
            Route::get('detail', 'TxInController@detail');
            Route::get('list', 'TxInController@list');
        });
        //币币交易卖出
        Route::prefix('tx_out')->group(function () {
            Route::post('add', 'TxOutController@add')->middleware(['tx_auth', 'real_name_auth']);
            Route::get('cancel', 'TxOutController@cancel');
            Route::get('detail', 'TxOutController@detail');
            Route::get('list', 'TxOutController@list');
        });
        //币币交易挂单历史
        Route::prefix('tx_history')->group(function () {
            Route::get('list', 'TxHistoryController@list');
        });
        //链上钱包
        Route::prefix('wallet')->group(function () {
            Route::get('wallets', 'WalletController@wallets');
            Route::get('wallet', 'WalletController@wallet');
        });
        //存币宝
        Route::prefix('storage_currency')->group(function () {
            Route::get('lock_list', 'StorageCurrencyController@lockList');
            Route::get('current_setting', 'StorageCurrencyController@currentSetting');
            Route::get('history', 'StorageCurrencyController@lockHistory');
            Route::post('submit_lock', 'StorageCurrencyController@submitLock');
        });
        Route::prefix('lever')->group(function () {
            Route::post('deal', 'LeverController@deal');                                                               //杠杆deal
            Route::post('dealall', 'LeverController@dealAll');                                                         //杠杆全部
            Route::post('submit', 'LeverController@submit')->middleware(['tx_auth', 'real_name_auth']);                //杠杆下单
            Route::post('close', 'LeverController@close');                                                             //杠杆平仓
            Route::post('cancel', 'LeverController@cancelTrade');                                                      //撤销委托(取消)
            Route::post('batch_close', 'LeverController@batchCloseByType');                                            //一键平仓
            Route::post('setstop', 'LeverController@setStopPrice');                                                    //设置止盈止损价
            Route::post('my_trade', 'LeverController@myTrade');                                                        //我的交易记录
        });

        //秒合约路由
        Route::prefix('microtrade')->group(function () {
            Route::get('payable_currencies', 'MicroOrderController@getPayableCurrencies');                   //可支付的币种列表
            Route::get('seconds', 'MicroOrderController@getSeconds');                                        //到期时间
            Route::post('submit', 'MicroOrderController@submit')->middleware(['tx_auth', 'real_name_auth']); //提交下单
            Route::get('lists', 'MicroOrderController@lists');                                               //下单记录
        });
    });

    Route::get('test', 'DefaultController@test'); // 测试
    /** 无需登录的 */
    // 法币商家
    Route::prefix('otc')->group(function () {
        Route::get('currencies', 'OtcController@otcCurrencies'); // 法币列表
    });
    Route::prefix('lever')->group(function () {
        Route::get('currency_lever_matches', 'LeverController@currencyLeverMatches'); //设置
    });
    Route::prefix('common')->group(function () {
        Route::post('image_upload', 'DefaultController@imageUpload')->middleware(['throttle:10,1']);
    });
    //新闻
    Route::prefix('news')->group(function () {
        Route::get('list', 'NewsController@list');
        Route::get('info', 'NewsController@info');
        Route::get('categories', 'NewsController@categories');
    });

    Route::prefix('user')->group(function () {
        Route::post('register', 'UserController@register')->middleware('limit_request');                       //注册
        Route::post('login', 'UserController@login');                                                          //登录
        Route::post('forget_password', 'UserController@forgetPassword');                                       //忘记密码
    });
    Route::prefix('notify')->group(function () {
        Route::post('send_email', 'NotifyController@sendEmailCode'); //发送邮件
        Route::post('send_sms', 'NotifyController@sendSmsCode');     //发送短信
        Route::post('check_code', 'NotifyController@checkCode');     //验证吗检验
    });
    Route::prefix('default')->group(function () {
        Route::get('area_list', 'DefaultController@areaList');       //国家列表
        Route::get('lang_list', 'DefaultController@langList');       //国家列表
        Route::get('setting', 'DefaultController@setting');          //查询系统设置
        Route::get('check_update', 'DefaultController@checkUpdate'); //查询系统设置
    });
    Route::prefix('market')->group(function () {
        Route::get('quotation', 'MarketController@quotation');
        Route::get('kline', 'MarketController@kline');
        Route::get('deal', 'MarketController@deal');
        Route::get('currency_matches', 'MarketController@currencyMatches');
    });
    //支付方式
    Route::prefix('pay_method')->group(function () {
        Route::get('methods', 'PayMethodController@methods');                   //获取用户已有支付方式列表
        Route::post('save', 'PayMethodController@saveMethod');                  //保存支付方式
        Route::get('types', 'PayMethodController@getMethodType');               //获取支付方式类型
        Route::get('get_method_by_type', 'PayMethodController@getMethodByType');//获取支付方式类型
    });
    Route::prefix('block_chain')->group(function () {
        Route::post('recharge', 'BlockChainController@apiRecharge');               //API充币接口
        Route::get('chain_protocols', 'BlockChainController@chainProtocols');      //区块链协议接口
        Route::get('currency_protocols', 'BlockChainController@currencyProtocols');//区块链协议接口
    });
});
