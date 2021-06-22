<?php

Route::namespace('Admin')->middleware('operation_log')->group(function () {

    Route::redirect('/', '/admin/index/index');

    //管理登录
    Route::prefix('admin')->group(function () {
        Route::get('/login', 'AdminController@login')->name('登陆页');
        Route::post('/login', 'AdminController@doLogin')->middleware(['google_auth'])->name('登陆接口');
        Route::get('/logout', 'AdminController@logout')->name('注销');
    });

    Route::middleware(['auth_admin', 'auth_admin_role'])->group(function () {
        //管理登录
        Route::prefix('index')->group(function () {
            Route::get('/index', 'IndexController@index')->name('后台页面');
            Route::get('/home', 'IndexController@home')->name('后台页面首页');
        });
        //获取安全验证码
        Route::prefix('common')->group(function () {
            Route::get('auth_code', 'IndexController@authCode')->name('获取安全验证码');
        });
        //系统用户管理
        Route::prefix('admin')->middleware('demo_forbidden')->group(function () {
            rest_rule('AdminController', '系统用户');
        });
        //角色管理
        Route::prefix('role')->group(function () {
            rest_rule('AdminRoleController', '角色管理');
        });
        //权限管理
        Route::prefix('module')->group(function () {
            rest_rule('AdminModuleController', '权限管理');
        });
        //权限分类管理
        Route::prefix('module_type')->group(function () {
            rest_rule('AdminModuleTypeController', '权限分类');
        });
        //机器人
        Route::prefix('robot')->group(function () {
            rest_rule('RobotController', '机器人');
        });

        //上传文件
        Route::post('/upload/layui_upload', 'UploadController@layuiUpload')->name('上传文件');

        Route::prefix('account_log')->group(function () {
            Route::get('change', 'AccountLogController@change')->name('币币账户日志页面');
            Route::get('lever', 'AccountLogController@lever')->name('全仓合约账户日志页面');
            Route::get('legal', 'AccountLogController@legal')->name('法币账户日志页面');
            Route::get('micro', 'AccountLogController@micro')->name('极速合约账户日志页面');
            Route::get('financial', 'AccountLogController@financial')->name('理财账户日志页面');
            Route::get('iso', 'AccountLogController@iso')->name('逐仓合约账户日志页面');
            Route::get('recharge', 'AccountLogController@recharge')->name('充币日志页面');
            Route::get('change_list', 'AccountLogController@changeList')->name('币币账户日志列表');
            Route::get('lever_list', 'AccountLogController@leverList')->name('全仓合约账户日志列表');
            Route::get('legal_list', 'AccountLogController@legalList')->name('法币账户日志列表');
            Route::get('micro_list', 'AccountLogController@microList')->name('极速合约账户日志列表');
            Route::get('financial_list', 'AccountLogController@financialList')->name('理财账户日志列表');
            Route::get('recharge_list', 'AccountLogController@rechargeList')->name('充币日志列表');
            Route::get('iso_list', 'AccountLogController@isoList')->name('逐仓合约账户日志列表');
        });

        //币种管理
        Route::prefix('currency')->group(function () {
            rest_rule('CurrencyController');
            Route::post('execute_currency', 'CurrencyController@executeCurrency');
        });
        //行情管理
        Route::prefix('currency_quotation')->group(function () {
            rest_rule('CurrencyQuotationController', '行情');
            Route::get('clear_quotation', 'CurrencyQuotationController@clearQuotation')->name('交易概要归零');
            Route::get('clear_kline', 'CurrencyQuotationController@clearKline')->name('导入k线');
            Route::get('import_kline', 'CurrencyQuotationController@importKline')->name('清除k线');
        });
        //交易对管理
        Route::prefix('currency_match')->group(function () {
            rest_rule('CurrencyMatchController', '交易对');
        });
        //交易哈希
        Route::prefix('tx_hash')->group(function () {
            rest_rule('TxHashController', '交易哈希');
        });
        //币币交易卖出单
        Route::prefix('tx_in')->group(function () {
            rest_rule('TxInController', '币币交易买入');
        });
        //币币交易买单
        Route::prefix('tx_out')->group(function () {
            rest_rule('TxOutController', '币币交易卖出');
        });
        //全站交易
        Route::prefix('tx_complete')->group(function () {
            rest_rule('TxCompleteController', '全站交易');
        });
        //撮合内存池
        Route::prefix('tx')->group(function () {
            rest_rule('TxController', '撮合内存池');
        });

        //资产管理
        Route::prefix('account')->group(function () {
            rest_rule('AccountController', '资产');
            Route::post('update', 'AccountController@update')->middleware(['google_auth'])->name('更改资产');
        });
        //用户管理
        Route::prefix('user')->group(function () {
            rest_rule('UserController', '用户');
        });
        //用户实名认证
        Route::prefix('user_real')->group(function () {
            rest_rule('UserRealController', '用户实名');
            Route::post('review_status', 'UserRealController@reviewStatus')->name('用户认证');

        });
        //提币申请
        Route::prefix('account_draw')->group(function () {
            rest_rule('AccountDrawController', '用户提币');
            Route::get('confirm', 'AccountDrawController@confirm');      //通过
            Route::get('reject', 'AccountDrawController@reject');        //驳回
        });

        //钱包管理
        Route::prefix('chain_wallet')->group(function () {
            rest_rule('ChainWalletController', '链上钱包');
            Route::get('refresh_balance', 'ChainWalletController@refreshOnlineBalance')
                ->name('刷新链上钱包余额');//刷新余额
            Route::any('transfer_fee', 'ChainWalletController@transferFee')
                ->name('向链上钱包打入手续费');//打入手续费
            Route::any('collect', 'ChainWalletController@collect')
                ->name('链上钱包归拢');//归拢
        });

        //区块链协议
        Route::prefix('chain_protocol')->group(function () {
            rest_rule('ChainProtocolController', '区块链协议');
        });
        //币种区块链协议
        Route::prefix('currency_protocol')->group(function () {
            rest_rule('CurrencyProtocolController', '币种区块链协议');
            Route::get('edit_in_address', 'CurrencyProtocolController@editInAddress')
                ->name('编辑入金总账号');
            Route::post('update_in_address', 'CurrencyProtocolController@updateInAddress')
                ->name('更新入金总账号');
            Route::get('edit_out_address', 'CurrencyProtocolController@editOutAddress')
                ->name('编辑出金总账号');
            Route::post('update_out_address', 'CurrencyProtocolController@updateOutAddress')
                ->name('更新出金总账号');
        });
        //账户类型
        Route::prefix('account_type')->group(function () {
            rest_rule('AccountTypeController', '账户类型');
            Route::get('update_recharge', 'AccountTypeController@updateRecharge')->name('账户是否可用充币');
            Route::get('update_display', 'AccountTypeController@updateDisplay')->name('账户是否显示');
        });

        //系统设置分类
        Route::prefix('setting_type')->group(function () {
            rest_rule('SettingTypeController', '系统设置分类');
        });
        //划转记录
        Route::prefix('transferred')->group(function () {
            rest_rule('TransferredLogController', '划转记录');
        });
        //系统设置
        Route::prefix('setting')->group(function () {
            rest_rule('SettingController', '系统设置');
        });

        //国家设置
        Route::prefix('areas')->group(function () {
            rest_rule('AreasController', '国家设置');
        });
        //语言设置
        Route::prefix('lang')->group(function () {
            rest_rule('LangController', '语言设置');
            Route::post('status', 'LangController@status');//1启动，0禁用
        });
        //新闻管理
        Route::prefix('news')->group(function () {
            rest_rule('NewsController', '新闻');
        });
        //新闻分类管理
        Route::prefix('news_category')->group(function () {
            rest_rule('NewsCategoryController', '新闻分类');
        });

        //杠杆
        Route::prefix('lever_transaction')->group(function () {
            rest_rule('LeverTransactionController', '杠杆');
            Route::any('Leverdeals_show', 'LeverTransactionController@Leverdeals_show');
            Route::any('close', 'LeverTransactionController@close');
            Route::any('list', 'LeverTransactionController@Leverdeals');//杠杆交易 团队所有订单
            Route::get('csv', 'LeverTransactionController@csv');        //导出杠杆交易 团队所有订单
        });
        Route::prefix('hazard_rate')->group(function () {
            rest_rule('HazardRateController');
            Route::get('total', 'HazardRateController@total');
            Route::get('total_lists', 'HazardRateController@totalLists');
            Route::get('handle', 'HazardRateController@handle');
            Route::post('handle', 'HazardRateController@postHandle');
        });
        //杠杆参数设置
        Route::prefix('lever_multiple')->group(function () {
            rest_rule('LeverMultipleController');
        });

        //otc交易
        Route::prefix('otc')->group(function () {
            Route::get('seller', 'OtcController@seller');
            Route::get('seller_list', 'OtcController@sellerList');
            Route::get('seller_edit', 'OtcController@sellerEdit');
            Route::post('seller_edit', 'OtcController@postSellerEdit');
            Route::get('seller_currency', 'OtcController@sellerCurrency');
            Route::get('seller_currency_list', 'OtcController@sellerCurrencyList');
            Route::get('seller_currency_edit', 'OtcController@sellerCurrencyEdit');
            Route::post('seller_currency_edit', 'OtcController@postSellerCurrencyEdit');
            Route::get('master', 'OtcController@master');
            Route::get('master_list', 'OtcController@masterList');
            Route::get('master_edit', 'OtcController@masterEdit');
            Route::post('master_edit', 'OtcController@postMasterEdit');
            Route::get('detail', 'OtcController@detail');
            Route::get('detail_lists', 'OtcController@detailList');
            Route::get('detail_edit', 'OtcController@detailEdit');
            Route::post('detail_edit', 'OtcController@postDetailEdit');
            Route::get('seller_apply', 'OtcController@sellerApply');//商家申请
            Route::get('seller_apply_list', 'OtcController@sellerApplyList');
            Route::post('seller_apply_operation', 'OtcController@sellerApplyOperation');
            Route::get('seller_currency_apply', 'OtcController@sellerCurrencyApply');//商家币种权限
            Route::get('seller_currency_apply_list', 'OtcController@sellerCurrencyApplyList');
            Route::post('seller_currency_apply_operation', 'OtcController@sellerCurrencyApplyOperation');
            Route::get('detail_actions', 'OtcController@actions');
            Route::get('action_lists', 'OtcController@actionList');
        });

        //表单模型管理
        Route::prefix('form_model')->group(function () {
            rest_rule('FormModelController');
        });

        //模型组管理
        Route::prefix('model_group')->group(function () {
            rest_rule('ModelGroupController');
        });

        //字段属性管理
        Route::prefix('field_property')->group(function () {
            rest_rule('FieldPropertyController');
        });
        //表单字段管理
        Route::prefix('field')->group(function () {
            rest_rule('FieldController');
        });
        //日志
        Route::prefix('operation')->group(function () {
            rest_rule('OperationController');
        });
        //用户反馈管理
        Route::prefix('feedback')->group(function () {
            rest_rule('FeedbackController');
            Route::get('reply', 'FeedbackController@reply');
            Route::post('answer', 'FeedbackController@answer');       //回复
            Route::post('is_display', 'FeedbackController@isDisplay');//是否展示
        });
        //反馈类别管理
        Route::prefix('feedback_type')->group(function () {
            rest_rule('FeedbackTypeController');
        });

        //币种管理
        Route::prefix('route')->group(function () {
            rest_rule('RouteLogController', '路由是否添加日志');
            Route::post('add_log', 'RouteLogController@addLog')->name('路由是否添加日志');;
        });

        //谷歌验证器
        Route::prefix('google_auth')->group(function () {
            Route::get('/index', 'GoogleAuthController@index')->name('谷歌验证器页面');
            Route::post('/qr_code', 'GoogleAuthController@qrCode')->name('重新生成谷歌验证器');
        });
        Route::prefix('app_version')->group(function () {
            rest_rule('AppVersionController');
        });

        Route::group([], function () {
            //秒合约数量设置
            Route::get('/micro_number_index', function () {
                return view('admin.micro.index');
            });
            Route::get('/micro_number_add', 'MicroController@add');     //添加设置
            Route::post('/micro_number_add', 'MicroController@postAdd');//添加设置
            Route::get('/micro_numbers_list', 'MicroController@lists');
            Route::post('/micro_number_del', 'MicroController@del');

            //秒合约秒数设置
            Route::get('/micro_seconds_index', function () {
                return view('admin.micro.seconds_index');
            });

            Route::get('/micro_seconds_add', 'MicroController@secondsAdd');     //添加设置
            Route::post('/micro_seconds_add', 'MicroController@secondsPostAdd');//添加设置
            Route::get('/micro_seconds_list', 'MicroController@secondsLists');
            Route::post('/micro_seconds_status', 'MicroController@secondsStatus');
            Route::post('/micro_seconds_del', 'MicroController@secondsDel');
            //秒合约日志
            Route::get('/micro_order', 'MicroController@order');
            Route::get('/micro_order_list', 'MicroController@orderList');
            Route::get('/micro_order_edit', 'MicroController@edit');
            Route::post('/micro_order_edit', 'MicroController@editPost');
            Route::post('/micro/batch_risk', 'MicroController@batchRisk');
            // 按用户进行风控
            Route::get('/micro/users/index', 'MicroController@usersIndex');
            Route::get('/micro/users/list', 'MicroController@usersList');
            Route::post('/micro/users/batch_risk', 'MicroController@batchUsersRisk');
        });

        Route::prefix('reward_candy')->group(function () {
            rest_rule('RewardCandyController');
        });
        Route::prefix('trading_candy')->group(function () {
            rest_rule('TradingCandyController');
        });
        Route::prefix('storage_currency')->group(function () {
            rest_rule('StorageCurrencyController');
        });
        Route::prefix('storage_currency_history')->group(function () {
            rest_rule('StorageCurrencyHistoryController');
        });
    });
});
