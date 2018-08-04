// pages/info/info.js
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    infoList:{},
    hosturl: app.use.hostUrl
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    wx.hideShareMenu();//取消转发
        var userId = app.use.userId;
        var that = this;
        wx.request({
          url: app.use.hostUrl + '/wx/User/about_us',
          method: 'post',
          data: { uid: userId },
          header: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          success: function (res) {
            var status = res.data.status,
              list = res.data.data;
            console.log(list);
            if (status != 1) {
              wx.showToast({
                title: res.data.msg,
                duration: 2000
              })
              return false;
            }
            that.setData({
              infoList: list,
            })
          },
          fail: function (e) {
            wx.showToast({
              title: '网络异常！',
              duration: 2000
            });
          }
        })
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
  
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
  
  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {
  
  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {
  
  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {
  
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
  
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
  
  }
})