/**
* Theme: Adminox Dashboard
* Author: Coderthemes
* Dashboard
*/

jQuery(function ($) {

  'use strict';

  var AdminoxAdmin = window.AdminoxAdmin || {};




  /*--------------------------------
   Window Based Layout
   --------------------------------*/
  AdminoxAdmin.dashboardEcharts = function () {


    /*--------------- Chart 1 -------------*/
    if ($("#platform_type_dates_donut").length) {
      var myChart = echarts.init(document.getElementById('platform_type_dates_donut'));

      var idx = 1;
      var option_dt = {

        timeline: {
          show: false,
          data: todayOrderNomalPersentData.time,
          label: {
            formatter: function (s) {
              return s.slice(0, 5);
            }
          },
        },
        options: [
          {
            color: ['#5cbef0', '#687adc', '#83d5ac', '#1abbcc', '#f1a363'],

            tooltip: {
              trigger: 'item',
              formatter: "{a} <br/>{b} : {c} ({d}%)"
            },
            series: [
              {
                name: todayOrderNomalPersentData.time,
                type: 'pie',
                radius: [20, '60%'],
                roseType: 'none',
                center: ['50%', '50%'],
                width: '50%',       // for funnel
                // data: [{ value: 35, name: 'iPhone 10' }, { value: 16, name: 'Windows' }, { value: 27, name: 'Desktop' }, { value: 29, name: 'Mobiles' }, { value: 12, name: 'Others' }]
                data:todayOrderNomalPersentData.data
              }
            ]
          },
        ] // end options object
      };

      myChart.setOption(option_dt);


    }


    /*-------------- Chart 2 ---------------*/
    if ($("#user_type_bar").length) {
      // Initialize after dom ready
      var myChart = echarts.init(document.getElementById('user_type_bar'));

      var option = {

        // Setup grid
        grid: {
          zlevel: 0,
          x: 30,
          x2: 30,
          y: 20,
          y2: 20,
          borderWidth: 0,
          backgroundColor: 'rgba(0,0,0,0)',
          borderColor: 'rgba(0,0,0,0)',
        },

        // Add tooltip
        tooltip: {
          trigger: 'axis',
          axisPointer: {
            type: 'shadow', // line|shadow
            lineStyle: { color: 'rgba(0,0,0,.5)', width: 1 },
            shadowStyle: { color: 'rgba(0,0,0,.1)' }
          }
        },

        // Add legend
        legend: {
          data: []
        },
        toolbox: {
          orient: 'vertical',
          show: true,
          showTitle: true,
          color: ['#bdbdbd', '#bdbdbd', '#bdbdbd', '#bdbdbd'],
          feature: {
            mark: { show: false },
            // dataZoom: {
            //   show: true,
            //   title: {
            //     dataZoom: 'Data Zoom',
            //     dataZoomReset: 'Reset Zoom'
            //   }
            // },
            dataView: { show: false, readOnly: true },

            restore: { show: false },
            //saveAsImage: { show: true, title: 'Save as Image' }
          }
        },

        // Enable drag recalculate
        calculable: true,

        // Horizontal axis
        xAxis: [{
          type: 'category',
          boundaryGap: true,
          data: signDays.days,
          axisLine: {
            show: true,
            onZero: true,
            lineStyle: {
              color: '#ddd',
              type: 'solid',
              width: '1',
            },
          },
          axisTick: {
            show: false,
          },
          splitLine: {
            show: false,
            lineStyle: {
              color: '#fff',
              type: 'solid',
              width: 0,
              shadowColor: 'rgba(0,0,0,0)',
            },
          },
        }],

        // Vertical axis
        yAxis: [{
          type: 'value',
          axisLine: {
            show: true,
            onZero: true,
            lineStyle: {
              color: '#DDD',
              type: 'solid',
              width: '1',
              shadowColor: '#DDD',
            },
          },
        },
      ],

        // Add series
        series: [
          {
            name: '订单数量',
            type: 'bar',
            barWidth: 7,
            itemStyle: {
              normal: {
                color: '#5cbef0',
                borderWidth: 2, borderColor: '#5cbef0',
                areaStyle: { color: '#5cbef0', type: 'default' }
              }
            },

            data: orderDays.all
          },
          {
            name: '有效订单数量',
            type: 'bar',
            barWidth: 7,
            itemStyle: {
              normal: {
                color: '#687adc',
                borderWidth: 2, borderColor: '#687adc',
                areaStyle: { color: '#687adc', type: 'default' }
              }
            },

            data: orderDays.normal
          },
        ]
      };

      // Load data into the ECharts instance
      myChart.setOption(option);

    }




    /*----------------- Chart 4 ------------------*/
    if ($("#page_views_today").length) {

      // {
      //   name: '签收订单',
      //   type: 'line',
      //   smooth: true,
      //   symbol: 'none',
      //   symbolSize: 2,
      //   showAllSymbol: true,
      //   barWidth: 10,
      //   itemStyle: {
      //     normal: {
      //       color: '#64c5b1',
      //       borderWidth: 2, borderColor: '#64c5b1',
      //       areaStyle: { color: 'rgba(100,197,177,0)', type: 'default' }
      //     }
      //   },
      //   data:[2,4,5]
      //   //data: signDays.daysdata
      // }

      var daysdata =  signDays.daysdata
      var series = new Array();

      $(daysdata).each(function(i,v){
        var color = ['#5cbef0', '#687adc', '#83d5ac', '#1abbcc', '#f1a363']
        var json = {
        'type':'line',
        'smooth': true,
        'symbol': 'none',
        'symbolSize': 2,
        'showAllSymbol': true,
        'barWidth': 10,
        'itemStyle': {
          'normal': {
            'color': color[i],
            'borderWidth': 2,
            'borderColor': color[i],
            'areaStyle': { 'color': color[i], 'type': 'default' }
          }
         },
        }
       json.name = signDays.department[i]
        json.data = daysdata[i];
        series.push(json)

      })

      // Initialize after dom ready
      var myChart = echarts.init(document.getElementById('page_views_today'));
      var option = {

        // Setup grid

        grid: {
          zlevel: 0,
          x: 40,
          x2: 40,
          y: 20,
          y2: 20,
          borderWidth: 0,
          backgroundColor: 'rgba(0,0,0,0)',
          borderColor: 'rgba(0,0,0,0)',
        },

        // Add tooltip
        tooltip: {
          trigger: 'axis',
          axisPointer: {
            type: 'shadow', // line|shadow
            lineStyle: { color: 'rgba(0,0,0,.5)', width: 1 },
            shadowStyle: { color: 'rgba(0,0,0,.1)' }
          }
        },

        // Add legend
        legend: {
          data: []
        },
        toolbox: {
          orient: 'vertical',
          show: true,
          showTitle: true,
          color: ['#bdbdbd', '#bdbdbd', '#bdbdbd', '#bdbdbd'],
          feature: {
            mark: { show: false },
            dataView: { show: false, readOnly: true },
            restore: { show: false },
          }
        },

        // Enable drag recalculate
        calculable: true,

        // Horizontal axis
        xAxis: [{
          type: 'category',
          boundaryGap: false,
          data: signDays.days,

          axisTick: {
            show: false,
          },
          splitLine: {
            show: false,
            lineStyle: {
              color: '#fff',
              type: 'solid',
              width: 0,
              shadowColor: 'rgba(0,0,0,0)',
            },
          },
        }],

        // Vertical axis
        yAxis: [{
          type: 'value',
          axisLine: {
            show: true,
            onZero: true,
            lineStyle: {
              color: '#DDD',
              type: 'solid',
              width: '1',
              shadowColor: '#DDD',
            },
          },
        },
      ],

        // Add series
        series: series
      };

      // Load data into the ECharts instance
      myChart.setOption(option);

    }




  }



  /******************************
   initialize respective scripts
   *****************************/
  $(document).ready(function () {
    AdminoxAdmin.dashboardEcharts();
  });

  $(window).load(function () { });

});



!function ($) {
  "use strict";

  var ChartC3 = function () { };

  ChartC3.prototype.init = function () {

    //Donut Chart
    c3.generate({
      bindto: '#donut-chart',
      data: {
        columns: [
          ['Male', 46],
          ['Female', 24]
        ],
        type: 'donut'
      },
      donut: {
        title: "Candidates",
        width: 30,
        label: {
          show: false
        }
      },
      color: {
        pattern: ["#64c5b1", "#ddd"]
      }
    });

    //Pie Chart
    c3.generate({
      bindto: '#pie-chart',
      data: {
        columns: [
          ['Done', 46],
          ['Due', 24],
          ['Hold', 30]
        ],
        type: 'pie'
      },
      color: {
        pattern: ["#dddddd", "#64c5b1", "#e68900"]
      },
      pie: {
        label: {
          show: false
        }
      }
    });

  },
    $.ChartC3 = new ChartC3, $.ChartC3.Constructor = ChartC3

}(window.jQuery),

  //initializing
  function ($) {
    "use strict";
    $.ChartC3.init()
  }(window.jQuery);
