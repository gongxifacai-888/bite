<template>
  <div>
    <div class="bgheader bdbheader flex alcenter plr10 ht40">
      <div class="baselight2 ft16 bold mr20">{{ currency_matchInfo.symbol }}</div>
      <div class>
        <div
          class="ft16"
          :class="[currency_matchInfo.currency_quotation.change < 0 ? 'red' : 'green']"
        >{{ currency_matchInfo.currency_quotation.close || "0.00"}}</div>
        <!-- <div
          class="ft12"
        >≈{{ currency_matchInfo.currency_quotation.close *cny_usd || "0.00" | filterDecimals}}CNY</div> -->
      </div>
      <div class="flex1 ft12 tc">
        <div class="flex alcenter">
          <span class="flex1">{{$t('market.gain')}}</span>
          <span class="flex1">{{$t('market.high')}}</span>
          <span class="flex1">{{$t('market.latest')}}</span>
          <span class="flex1">{{$t('market.volume')}}</span>
        </div>
        <div class="flex alcenter">
          <span
            class="flex1"
            :class="[currency_matchInfo.currency_quotation.change < 0 ? 'red' : 'green']"
          >{{ currency_matchInfo.currency_quotation.change || "0.00" | filterDecimals }}%</span>
          <span class="flex1 baselight2">{{ currency_matchInfo.currency_quotation.high || "0.00"}}</span>
          <span class="flex1 baselight2">{{ currency_matchInfo.currency_quotation.low || "0.00"}}</span>
          <span
            class="flex1 baselight2"
          >{{ currency_matchInfo.currency_quotation.volume || "0.00" | filterDecimals}}</span>
        </div>
      </div>
    </div>
    <div id="tv_chart_container" style="width:100%;height: 90%" class></div>
  </div>
</template>
<script>
import {mapState} from 'vuex'
export default {
  props: ["cny_usd", "currency_matchInfo"],
  data() {
    return {
      widget: null,
      symbolInfo: null,
      feed: null,
      wsEx: null,
      ws: null,
      lists: [],
      newData: "",
      // symbol:'BTC/USDT',
      priceScale: 10000,
      histime: "",
      loctheme: "",
      lang: "",
      nowprice: "",
      change: "",
      maxprice: "",
      minprice: "",
      volume: "",
      lastPrice: ""
    };
  },
  computed:{
    ...mapState(['theme']),
  },
  watch: {
    theme:{
        handler(){
            var t = this.theme;
            this.loctheme = t;
            this.createWidget();
        },
        // deep: true,
        immediate: true
        // return this.$store.theme;
    },
    currency_matchInfo: {
      handler(newInfo, oldInfo) {
        if(oldInfo){
        //   console.log((oldInfo.symbol))
          this.unbindSocket(oldInfo.symbol);
          this.createWidget();
        }
        
      },
      // 代表在wacth里声明了firstName这个方法之后立即先去执行handler方法
      immediate: true
    }
  },
  mounted() {
    let lang = localStorage.getItem('lang');
    if(lang=='hk'){
        lang = 'zh_TW'
    }else if(lang=='jp'){
        lang = 'ja'
    }
    this.lang= lang || 'zh';
    localStorage.setItem("leverPeriod", "1min"); //默认为1分钟;
    this.createWidget();
  },
  destroyed(){
    this.removeWidget();
  },
  beforeRouteLeave(to, from, next){
    next();
    this.unbindSocket(this.currency_matchInfo.symbol); 
  },
  methods: {
    adjustScale() {
      var that = this;
      var str = that.currency_matchInfo.currency_quotation.close;
      if (str) {
        var lengths = str.toString().split(".")[1].length;
        var nums = 1;
        for (var i = 0; i < lengths; i++) {
          nums = nums * 10;
        }
        // let decimal=base.padEnd(nums,0)-0;
        let decimal = Number(nums);
        return decimal;
      }
    },
    init(froms, tos, periods) {
      var that = this;
      var arr = [];
      this.$http
        .initDataToken(
          {
            url: "market/kline",
            data: {
              from: froms,
              to: tos,
              symbol: that.currency_matchInfo.symbol,
              period: periods,
              currency_match_id:
                that.currency_matchInfo.currency_quotation.currency_match_id
            }
          },
          false
        )
        .then(res => {
          arr = res;
          return arr;
        });
    },
    // k线socket
    connect(real) {
      //封装推送数据
      var that = this;
      that.$socket.connected(socket => {
        socket.send({
          type: "sub",
          params: "KLINE." + that.currency_matchInfo.symbol
        });
        socket.on("KLINE", msg => {
          var klineType = localStorage.getItem("leverPeriod");
          if (
            klineType == msg.kline.period &&
            msg.currency_match_id ==
              that.currency_matchInfo.currency_quotation.currency_match_id
          ) {
            var obj = {};
            obj.open = Number(msg.kline.open);
            obj.low = Number(msg.kline.low);
            obj.high = Number(msg.kline.high);
            obj.close = Number(msg.kline.close);
            obj.volume = Number(msg.kline.volume);
            obj.time = Number(msg.kline.time);
            // that.currency_matchInfo.currency_quotation.high = msg.kline.high;
            // that.currency_matchInfo.currency_quotation.close = msg.kline.close;
            // that.currency_matchInfo.currency_quotation.low = msg.kline.low;
            // that.currency_matchInfo.currency_quotation.volume =
            //   msg.kline.volume;
            // that.currency_matchInfo.currency_quotation.change =
            //   msg.kline.change;
            // that.currency_matchInfo.currency_quotation.high = msg.kline.high;
            real(obj);
          }
        });
      });
    },
    unbindSocket(symbol){
      let that = this;
      this.$socket.send([{type: "unsub",params: "KLINE."+symbol}]);
    }, 
    // k线
    createWidget() {
            let that = this;
            this.$nextTick(function () {
                let cssfile = this.loctheme=='dark'?'bundles/new_dark.css':'bundles/new.css';
                let widget =that.widget= new TradingView.widget({
                    symbol:that.currency_matchInfo.symbol,
                    interval: 1,
                    debug: true,
                    fullscreen: false,
                    autosize: true,
                    container_id: "tv_chart_container",
                    // datafeed: new Datafeeds.UDFCompatibleDatafeed("http://demo_feed.tradingview.com"),
                    datafeed:that.createFeed(),
                    library_path: "./static/tradeview/charting_library/",
                    // library_path: "../../../static/tradeview/charting_library/",

                    custom_css_url: cssfile,
                    locale: that.lang,
                    width: "100%",
                    height: 516,
                    drawings_access: { type: 'black', tools: [ { name: "Regression Trend" } ] },
                    disabled_features: [  //  禁用的功能
                        'left_toolbar', 'header_saveload', 'compare_symbol', 'display_market_status',
                        'go_to_date', 'header_chart_type', 'header_compare', 'header_interval_dialog_button',
                        'header_resolutions', 'header_screenshot', 'header_symbol_search', 'header_undo_redo',
                        'legend_context_menu', 'show_hide_button_in_legend', 'show_interval_dialog_on_key_press',
                        'snapshot_trading_drawings', 'symbol_info', 'timeframes_toolbar', 'use_localstorage_for_settings',
                        'volume_force_overlay','widget_logo'
                    ],
                    enabled_features: [ //  启用的功能（备注：disable_resolution_rebuild 功能用于控制当时间范围为1个月时，日期刻度是否都是每个月1号
                        'dont_show_boolean_study_arguments', 'hide_last_na_study_output', 'move_logo_to_main_pane',
                        'same_data_requery', 'side_toolbar_in_fullscreen_mode', 'disable_resolution_rebuild'
                    ],
                    charts_storage_url: 'http://saveload.tradingview.com',
                    charts_storage_api_version: "1.1",
                    toolbar_bg: "transparent",
                    timezone: "Asia/Shanghai",
                    studies_overrides: {
                        'volume.precision': '1000'
                    },
                    overrides: that.overrides()
                });

                widget.MAStudies = [];
                widget.selectedIntervalButton = null;
                // widget.setLanguage('en')
                widget.onChartReady(function() { //图表方法
                    // document.getElementById('trade-view').childNodes[0].setAttribute('style', 'display:block;width:100%;height:100%;');
                    //let that =this

                    widget.chart().createStudy('Moving Average', false, true, [15,'close', 0],null,{'Plot.color':'#e843da'});
                    widget.chart().createStudy("MA Cross", false, false, [10, 20]);
                    let buttonArr = [
                        {
                            value: "1min",
                            period: "1",
                            text: "Time",
                            chartType: 3,
                            type:"1min"
                        },
                        {
                            value: "1",
                            period: "1m",
                            text: "1min",
                            chartType:1,
                            type:"1min"
                        },
                        {
                            value: "5",
                            period: "5m",
                            text: "5min",
                            chartType: 1,
                            type:"5min"
                        },
                        {
                            value: "15",
                            period: "15m",
                            text: "15min",
                            chartType: 1,
                            type:"15min"
                        },
                        {
                            value: "30",
                            period: "30m",
                            text: "30min",
                            chartType:1,
                            type:"30min"
                        },
                        {
                            value: "60",
                            period: "60分钟",
                            text: "1hour",
                            chartType: 1,
                            type:"60min"
                        },
                        {
                            value: "1D",
                            period: "1D",
                            text: "1day",
                            chartType: 1,
                            type:"1day"
                        },
                        {
                            value: "1W",
                            period: "1W",
                            text: "1week",
                            chartType:1,
                            type:"1week"
                        }, {
                            value: "1M",
                            period: "1M",
                            text: "1mon",
                            chartType: 1,
                            type:"1mon"
                        }
                    ];
                    let btn = {};
                    let nowTime = '';
                    buttonArr.forEach((v, i) => {
                        // console.log(v)
                        let  button=widget.createButton()
                        button.attr('title',v.text)
                            .addClass("my2")
                            .text(v.text)

                        if(v.text=='1min'){
                            if(that.loctheme=='light'){
                                button.css({"background-color":"#e5ebf5",'color':'#9ca9b5'})
                            }else{
                                button.css({"background-color":"#344568",'color':'#ffffff'})
                            }
                            // localStorage.setItem('tim','1')  //默认为1分钟
                            localStorage.setItem('leverPeriod','1min')  //默认为1分钟
                        }

                        // console.log($(this),'999999')
                        btn = button.on("click", function (e) {
                            $(this).parents(".left").children().find(".my2").removeAttr("style"); //去掉1分钟的
                            handleClick(e, v.value,v.type);
                            widget.chart().setChartType(v.chartType); //改变K线类型
                        });

                    });
                    let handleClick = (e, value,type) => {
                        that.setSymbol = function(symbol,value){
                            gh.chart().setSymbol(symbol,value);
                        };
                        // localStorage.setItem('tim',value)
                        localStorage.setItem('leverPeriod',type)
                        widget.chart().setResolution(value,function onReadyCallback(){});  //改变分辨率

                        $(e.target)
                            .addClass("mydate")
                            .closest("div.space-single")
                            .siblings("div.space-single")
                            .find("div.button")
                            .removeClass("mydate")
                    };

                });


                that.widget = widget;
            })
        },
        createFeed(){
            let  that= this;
            let Datafeed = {};


            Datafeed.DataPulseUpdater = function(datafeed, updateFrequency) {
                this._datafeed = datafeed;
                this._subscribers = {};

                this._requestsPending = 0;
                var that = this;

                var update = function() {
                    if (that._requestsPending > 0) {
                        return;
                    }

                    for (var listenerGUID in that._subscribers) {
                        var subscriptionRecord = that._subscribers[listenerGUID];
                        var resolution = subscriptionRecord.resolution;

                        var datesRangeRight = parseInt((new Date().valueOf()) / 1000);
                        

                        //	BEWARE: please note we really need 2 bars, not the only last one
                        //	see the explanation below. `10` is the `large enough` value to work around holidays
                        var datesRangeLeft = datesRangeRight - that.periodLengthSeconds(resolution, 10);

                        that._requestsPending++;

                        (function(_subscriptionRecord) { // eslint-disable-line
                            that._datafeed.getBars(_subscriptionRecord.symbolInfo, resolution, datesRangeLeft, datesRangeRight, function(bars) {
                                    that._requestsPending--;

                                    //	means the subscription was cancelled while waiting for data
                                    if (!that._subscribers.hasOwnProperty(listenerGUID)) {
                                        return;
                                    }

                                    if (bars.length === 0) {
                                        return;
                                    }

                                    var lastBar = bars[bars.length - 1];
                                    if (!isNaN(_subscriptionRecord.lastBarTime) && lastBar.time < _subscriptionRecord.lastBarTime) {
                                        return;
                                    }

                                    var subscribers = _subscriptionRecord.listeners;

                                    //	BEWARE: this one isn't working when first update comes and this update makes a new bar. In this case
                                    //	_subscriptionRecord.lastBarTime = NaN
                                    var isNewBar = !isNaN(_subscriptionRecord.lastBarTime) && lastBar.time > _subscriptionRecord.lastBarTime;

                                    //	Pulse updating may miss some trades data (ie, if pulse period = 10 secods and new bar is started 5 seconds later after the last update, the
                                    //	old bar's last 5 seconds trades will be lost). Thus, at fist we should broadcast old bar updates when it's ready.
                                    if (isNewBar) {
                                        if (bars.length < 2) {
                                            throw new Error('Not enough bars in history for proper pulse update. Need at least 2.');
                                        }

                                        var previousBar = bars[bars.length - 2];
                                        for (var i = 0; i < subscribers.length; ++i) {
                                            subscribers[i](previousBar);
                                        }
                                    }

                                    _subscriptionRecord.lastBarTime = lastBar.time;

                                    for (var i = 0; i < subscribers.length; ++i) {
                                        subscribers[i](lastBar);
                                    }
                                },

                                //	on error
                                function() {
                                    that._requestsPending--;
                                });
                        })(subscriptionRecord);
                    }
                };

                if (typeof updateFrequency != 'undefined' && updateFrequency > 0) {
                    setInterval(update, updateFrequency);
                }
            };

            Datafeed.DataPulseUpdater.prototype.periodLengthSeconds = function(resolution, requiredPeriodsCount) {
                var daysCount = 0;

                if (resolution === 'D') {
                    daysCount = requiredPeriodsCount;
                } else if (resolution === 'M') {
                    daysCount = 31 * requiredPeriodsCount;
                } else if (resolution === 'W') {
                    daysCount = 7 * requiredPeriodsCount;
                } else {
                    daysCount = requiredPeriodsCount * resolution / (24 * 60);
                }

                return daysCount * 24 * 60 * 60;
            };

            Datafeed.DataPulseUpdater.prototype.subscribeDataListener = function(symbolInfo, resolution, newDataCallback, listenerGUID) {
                this._datafeed._logMessage('Subscribing ' + listenerGUID);

                if (!this._subscribers.hasOwnProperty(listenerGUID)) {
                    this._subscribers[listenerGUID] = {
                        symbolInfo: symbolInfo,
                        resolution: resolution,
                        lastBarTime: NaN,
                        listeners: []
                    };
                }

                this._subscribers[listenerGUID].listeners.push(newDataCallback);
            };

            Datafeed.DataPulseUpdater.prototype.unsubscribeDataListener = function(listenerGUID) {
                this._datafeed._logMessage('Unsubscribing ' + listenerGUID);
                delete this._subscribers[listenerGUID];
            };

            Datafeed.Container = function(updateFrequency){
                this._configuration = {
                    supports_search: false,
                    supports_group_request: false,
                    supported_resolutions: ['1', '3', '5', '15', '30', '60', '120', '240', '360', '720', '1D', '3D', '1W', '1M'],
                    supports_marks: true,
                    supports_timescale_marks: true,
                    exchanges: ['gh']
                };

                this._barsPulseUpdater = new Datafeed.DataPulseUpdater(this, updateFrequency || 10 * 1000);
                // this._quotesPulseUpdater = new Datafeed.QuotesPulseUpdater(this);

                this._enableLogging = true;
                this._callbacks = {};

                this._initializationFinished = true;
                this._fireEvent('initialized');
                this._fireEvent('configuration_ready');
            };

            Datafeed.Container.prototype._fireEvent = function(event, argument) {
                if (this._callbacks.hasOwnProperty(event)) {
                    var callbacksChain = this._callbacks[event];
                    for (var i = 0; i < callbacksChain.length; ++i) {
                        callbacksChain[i](argument);
                    }

                    this._callbacks[event] = [];
                }
            };

            Datafeed.Container.prototype._logMessage = function(message) {
                if (this._enableLogging) {
                    var now = new Date();
                    // console.log("CHART LOGS: "+now.toLocaleTimeString() + '.' + now.getMilliseconds() + '> ' + message);
                }
            };

            Datafeed.Container.prototype.on = function(event, callback) {
                if (!this._callbacks.hasOwnProperty(event)) {
                    this._callbacks[event] = [];
                }

                this._callbacks[event].push(callback);
                return this;
            };

            Datafeed.Container.prototype.onReady = function(callback) {
                let that = this;
                if (this._configuration) {
                    setTimeout(function() {
                        callback(that._configuration);
                    }, 0);
                }
                else {
                    this.on('configuration_ready', function() {
                        callback(that._configuration);
                    });
                }
            };

            Datafeed.Container.prototype.resolveSymbol = function(symbolName, onSymbolResolvedCallback, onResolveErrorCallback) {
                this._logMessage("GOWNO :: resolve symbol "+ symbolName);
                Promise.resolve().then(() => {
                    onSymbolResolvedCallback({
                        "name": that.currency_matchInfo.symbol,
                        "timezone": "Asia/Shanghai",
                        "pricescale": that.adjustScale(),
                        "minmov": 1, //minmov(最小波动), pricescale(价格精度), minmove2, fractional(分数)
                        "minmov2": 0,//这是一个神奇的数字来格式化复杂情况下的价格。
                        "ticker": that.currency_matchInfo.symbol,
                        "description": "",
                        "type": "bitcoin",
                        "volume_precision": 8,
                        // "exchange-traded": "sdt",
                        // "exchange-listed": "sdt",
                        //现在，这两个字段都为某个交易所的略称。将被显示在图表的图例中，以表示此商品。目前此字段不用于其他目的。
                        "has_intraday": true,
                        "has_weekly_and_monthly": true,
                        "has_no_volume": false, //布尔表示商品是否拥有成交量数据。
                        'session': '24x7',
                        'supported_resolutions': ['1', '3', '5', '15', '30', '60', '120', '240', '360', '720', '1D', '3D', '1W', '1M']

                    });
                })
            };


            //初始化数据
            Datafeed.Container.prototype.getBars = function(symbolInfo, resolution, rangeStartDate, rangeEndDate, onHistoryCallback, onErrorCallback) {
                // if (rangeStartDate > 0 && (rangeStartDate + '').length > 10) {
                //   throw new Error(['Got a JS time instead of Unix one.', rangeStartDate, rangeEndDate]);
                // }
                if(resolution.indexOf('D')==-1&&resolution.indexOf('W')==-1&&resolution.indexOf('M')==-1){
                    resolution=resolution+'min'
                }else if(resolution.indexOf('W')!=-1||resolution.indexOf('M')!=-1){
                    resolution=resolution
                }
                if(resolution=='1D'){
                    resolution='1day'
                }
                if(resolution=='1W'){
                     resolution='1week'
                }
                if(resolution=='1M'){
                     resolution='1mon'
                }
                that.$http.initDataToken({url:'market/kline',
                    data:{from:rangeStartDate,to:rangeEndDate,symbol:symbolInfo.name,period:resolution,currency_match_id:that.currency_matchInfo.currency_quotation.currency_match_id}
                },false)
                .then(res=>{
                    if(res.length>0){
                         res.forEach((item,i)=>{
                            item.open=Number(item.open)
                            item.close=Number(item.close)
                            item.high=Number(item.high)
                            item.low=Number(item.low)
                        })
                        onHistoryCallback(res,{noData:false})
                        onHistoryCallback([],{noData:true})
                    }else{
                        onHistoryCallback([],{noData:true})
                    }
                })
               

            };
            //实时数据
            Datafeed.Container.prototype.subscribeBars = function(symbolInfo, resolution, onRealtimeCallback, listenerGUID, onResetCacheNeededCallback) {
                that.connect(onRealtimeCallback)

                //this._barsPulseUpdater.subscribeDataListener(symbolInfo, resolution, onRealtimeCallback, listenerGUID, onResetCacheNeededCallback);
            };

            Datafeed.Container.prototype.unsubscribeBars = function(listenerGUID) {

                this._barsPulseUpdater.unsubscribeDataListener(listenerGUID);

            };

            return new Datafeed.Container;
        },
        updateData(data) {
            if (data) {
                this.$emit('real-time', data);
            }
        },

        updateWidget(item) {
            this.symbolInfo = {
                name: item,
                ticker: item,
                description: "",
                session: "24x7",
                supported_resolutions: ['1', '5', '15', '30', '60', '240', '1D', '3D', '1W', '1M'],
                has_intraday: true,
                has_daily: true,
                has_weekly_and_monthly: true,
                timezone: "UTC",
            }

            this.removeWidget();
            this.createWidget();
        },
        removeWidget() {
            if (this.widget) {
                // this.widget.remove();
                this.widget = null;
            }
        },
        overrides() {
            let style;
           if(this.loctheme == 'dark'){
                style = {
                    up: "#589065",
                    down: "#AE4E54",
                    bg: "#171b2b",
                    grid: "#1E2740",
                    cross: "#1E2740",
                    border: "#4e5b85",
                    text: "#61688A",
                    areatop: "rgba(122, 152, 247, .1)",
                    areadown: "rgba(122, 152, 247, .02)"
                };
            }else{
                style = {
                    up: "#589065",
                    down: "#AE4E54",
                    bg: "#ffffff",
                    grid: "#f9f9fa",
                    cross: "#f9f9fa",
                    border: "#b1b7be",
                    text: "#61688A",
                    areatop: "rgba(122, 152, 247, .1)",
                    areadown: "rgba(122, 152, 247, .02)"
                };
            }
            return{
                'volumePaneSize': "medium", //large, medium, small, tiny
                'paneProperties.topMargin':'20',
                "scalesProperties.lineColor": style.text,
                "scalesProperties.textColor": style.text,
                "paneProperties.background": style.bg,//改变背景色的重要代码
                "paneProperties.vertGridProperties.color": style.grid,
                "paneProperties.horzGridProperties.color": style.grid,
                "paneProperties.crossHairProperties.color": style.cross,
                "paneProperties.legendProperties.showLegend": true,
                "paneProperties.legendProperties.showStudyArguments": true,
                "paneProperties.legendProperties.showStudyTitles": true,
                "paneProperties.legendProperties.showStudyValues": true,
                "paneProperties.legendProperties.showSeriesTitle": true,
                "paneProperties.legendProperties.showSeriesOHLC": true,
                "mainSeriesProperties.candleStyle.upColor": style.up,
                "mainSeriesProperties.candleStyle.downColor": style.down,
                "mainSeriesProperties.candleStyle.drawWick": true,
                "mainSeriesProperties.candleStyle.drawBorder": true,
                "mainSeriesProperties.candleStyle.borderColor": style.border,
                "mainSeriesProperties.candleStyle.borderUpColor": style.up,
                "mainSeriesProperties.candleStyle.borderDownColor": style.down,
                "mainSeriesProperties.candleStyle.wickUpColor": style.up,
                "mainSeriesProperties.candleStyle.wickDownColor": style.down,
                "mainSeriesProperties.candleStyle.barColorsOnPrevClose": false,
                "mainSeriesProperties.hollowCandleStyle.upColor": style.up,
                "mainSeriesProperties.hollowCandleStyle.downColor": style.down,

                "mainSeriesProperties.hollowCandleStyle.drawWick": true,
                "mainSeriesProperties.hollowCandleStyle.drawBorder": true,
                "mainSeriesProperties.hollowCandleStyle.borderColor": style.border,
                "mainSeriesProperties.hollowCandleStyle.borderUpColor": style.up,
                "mainSeriesProperties.hollowCandleStyle.borderDownColor": style.down,
                "mainSeriesProperties.hollowCandleStyle.wickColor": style.line,
                "mainSeriesProperties.haStyle.upColor": style.up,
                "mainSeriesProperties.haStyle.downColor": style.down,
                "mainSeriesProperties.haStyle.drawWick": true,
                "mainSeriesProperties.haStyle.drawBorder": true,
                "mainSeriesProperties.haStyle.borderColor": style.border,
                "mainSeriesProperties.haStyle.borderUpColor": style.up,
                "mainSeriesProperties.haStyle.borderDownColor": style.down,
                "mainSeriesProperties.haStyle.wickColor": style.border,
                "mainSeriesProperties.haStyle.barColorsOnPrevClose": false,
                "mainSeriesProperties.barStyle.upColor": style.up,
                "mainSeriesProperties.barStyle.downColor": style.down,
                "mainSeriesProperties.barStyle.barColorsOnPrevClose": false,
                "mainSeriesProperties.barStyle.dontDrawOpen": false,
                "mainSeriesProperties.lineStyle.color": style.border,
                "mainSeriesProperties.lineStyle.linewidth": 1,
                "mainSeriesProperties.lineStyle.priceSource": "close",
                "mainSeriesProperties.areaStyle.color1": style.areatop,
                "mainSeriesProperties.areaStyle.color2": style.areadown,
                "mainSeriesProperties.areaStyle.linecolor": style.border,
                "mainSeriesProperties.areaStyle.linewidth": 1,
                "mainSeriesProperties.areaStyle.priceSource": "close"
            }
            // this.sty2()
        },


        chose(){
            // console.log(this.widget,'000')
            this.widget.setLanguage('en') //设置语言

            // this.$store.commit('symbol','sdt')
            // this.$store.state.symbol='sdt6456' //切换交易对
        }
  }
};
</script>
