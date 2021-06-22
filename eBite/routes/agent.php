<?php

Route::namespace('Agent')->group(function () {

    //代理商
    Route::get('/', 'MemberController@login');

    Route::post('/login', 'MemberController@doLogin');
    Route::post('/logout', 'MemberController@logout');

    Route::get('/index', function () {
        return view('agent.index');
    });

    Route::middleware(['agent_auth'])->group(
        function () {
            Route::get('home', 'MemberController@home');//主页

            Route::prefix('user')->group(function () {
                //用户

                Route::get('index', 'UserController@index');//用户管理列表
                Route::post('get_user_num', 'UserController@getUserNum');
                Route::any('lists', 'UserController@lists');//用户列表

                Route::get('users_wallet', 'CapitalController@wallet');
                Route::get('users_wallet_total', 'CapitalController@wallettotalList');

                Route::any('users_excel', 'OrderController@user_excel');//导出用户记录

                //用户订单
                Route::get('lever_order', 'OrderController@userLeverIndex');
                Route::get('lever_order_list', 'OrderController@userLeverList');

            });

            Route::prefix('salesmen')->group(function () {
                //代理商
                Route::get('index', 'UserController@salesmenIndex');//代理商管理列表
                Route::get('add', 'UserController@salesmenAdd');//添加代理商页面
                Route::any('lists', 'MemberController@lists');//代理商列表
                Route::post('addagent', 'MemberController@addAgent');//添加代理商
                Route::post('addsonagent', 'MemberController@addSonAgent');//给代理商添加代理商
                Route::post('update', 'MemberController@updateAgent');//添加代理商
                Route::post('searchuser', 'MemberController@searchuser');//查询用户
                Route::post('search_agent_son', 'MemberController@search_agent_son');//查询用户

            });

            Route::prefix('order')->group(function () {
                //杠杆
                Route::get('/micro_order', 'MicroController@order');
                Route::get('/micro_order_list', 'MicroController@orderList');
                Route::get('lever_index', 'OrderController@leverIndex');//杠杆订单页面
                Route::get('micro_index', 'OrderController@microIndex');//杠杆订单页面

                Route::get('list', 'OrderController@orderList');//杠杆订单
                Route::get('micro_list', 'OrderController@microList');//秒合约订单页面

                Route::get('info', 'OrderController@orderInfo');//订单详情

                Route::get('get_order_account', 'OrderController@getOrderAccount');

                Route::any('order_excel', 'OrderController@orderExcel');//导出订单记录Excel

                Route::get('otc_index', 'OrderController@otcIndex');//otc订单页面
                Route::get('otc_list', 'OrderController@detailList');//otc

            });

            Route::prefix('tx_complete')->group(function () {
                //撮合完成
                Route::get('index', 'OrderController@transactionIndex');

                Route::get('list', 'OrderController@transactionList');
                Route::get('history_index', 'OrderController@transactionHistoryIndex');

                Route::get('history_list', 'OrderController@transactionHistoryList');

            });

            Route::prefix('jie')->group(function () {
                //杠杆结算
                Route::get('index', 'OrderController@jieIndex');

                Route::post('list', 'OrderController@jieList');//团队所有结算
                Route::post('info', 'OrderController@jieInfo');//结算详情

                Route::any('dojie', 'OrderController@dojie');

                //结算 提现到账
                Route::post('wallet_out/done', 'OrderController@walletOut');

            });
            Route::prefix('micro_jie')->group(function () {
                //秒合约
                Route::get('index', 'MicroOrderController@jieIndex');

                Route::post('list', 'MicroOrderController@jieList');//团队所有结算
                Route::post('info', 'MicroOrderController@jieInfo');//结算详情

                Route::any('dojie', 'MicroOrderController@dojie');

                //结算 提现到账
                Route::post('wallet_out/done', 'MicroOrderController@walletOut');

            });

            Route::prefix('money')->group(function () {
                //出入金
                Route::any('recharge_index', 'CapitalController@rechargeIndex');
                Route::any('withdraw_index', 'CapitalController@withdrawIndex');
                Route::get('recharge', 'CapitalController@rechargeList');
                Route::get('withdraw', 'CapitalController@withdrawList');

            });

            Route::prefix('set')->group(function () {
                //设置
                Route::get('password', 'MemberController@setPas');//修改密码
                Route::get('info', 'MemberController@setInfo');//基本信息

                Route::post('change_password', 'MemberController@changePWD');//修改密码
                Route::post('save_user_info', 'MemberController@saveUserInfo');


            });

            Route::prefix('report')->group(function () {
                //统计信息
                Route::post('day', 'ReportController@day');
            });


        }

    );


});
