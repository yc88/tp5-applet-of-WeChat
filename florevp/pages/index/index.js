//index.js
//获取应用实例
const app = getApp()
Page({
  data: {
    isIpx: app.globalData.isIpx,
    imgUrls:[],
    bread:[],
    dessert:[],
    autoplay:true,
    url: app.use.hostImg,
    currentTab: '',
    indicatorDots: true,
    interval: 5000,
    duration: 1000,
    circular:true,
    menuTapCurrent:0,
  },
  /*** 滑动切换tab***/
  bindChange: function (e) {
    var that = this;
    that.setData({ currentTab: e.detail.current });
  },
  /*** 点击tab切换***/
  swichNav: function (e) {
    var that = this;
    that.setData({
      currentTab: e.target.dataset.current
    });
  },
  onLoad: function () {
    this.get_list();
  },
  get_list:function(){
    var that = this;
    wx.request({
      url: app.use.hostUrl + '/wx/Index/index', //
      header: {
        'content-type': 'application/json' // 默认值
      },
      success: function (res) {
        that.setData({
          imgUrls: res.data.data.banner,
          bread: res.data.data.bread,
          dessert: res.data.data.dessert
        })
      },
      fail: function (e) {
        wx.showToast({
          title: '网络异常！',
          duration: 2000
        });
      }
      , complete: function (e) {
        wx.hideNavigationBarLoading() //完成停止加载
        wx.stopPullDownRefresh() //停止下拉刷新
      }
    })
  },
  /**
  * 页面相关事件处理函数--监听用户下拉动作
  */
  onPullDownRefresh: function () {
    this.get_list();
  },
  /**
  * 用户点击右上角分享
  */
  onShareAppMessage: function () {

  }
})
