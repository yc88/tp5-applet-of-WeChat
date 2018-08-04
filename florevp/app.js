//app.js
App({
  use: {
    hostUrl: 'https://wx.florevp.com',
    hostImg: 'https://wx.florevp.com',
    hostVideo: 'https://wx.florevp.com',
    userId: ''
  },
  onLaunch: function () {
    //调用API从本地缓存中获取数据
    var logs = wx.getStorageSync('logs') || []
    logs.unshift(Date.now())
    wx.setStorageSync('logs', logs);
    var that = this;
    that.getUserInfo();
    wx.getSystemInfo({
      success: function (res) {
        var model = res.model; //手机型号 iPhone X
        if (model == 'iPhone X') {
          that.globalData.isIpx = true;
          // console.log(that.globalData.isIpx);
        }
      }
    })
  },
  getUserInfo: function (cb) {
    var that = this
    if (this.globalData.userInfo) {
      // 用户信息存在的时候
      typeof cb == "function" && cb(this.globalData.userInfo)
    } else {
      //调用登录接口
      wx.login({
        success: function (res) {
          var code = res.code;
          that.getUserSessionKey(code);
        }
      });
    }
  },
  getUserSessionKey: function (code) {
    //用户的状态
    var that = this;
    wx.request({
      url: that.use.hostUrl + '/wx/Login/getWxSessionKey',
      method: 'post',
      data: {
        code: code
      },
      header: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      success: function (res) {
        //--init data
        var data = res.data;
        // that.globalData.userInfo['sessionId'] = data.session_key;
        // console.log(that.globalData);
        if (data.status == 0) {
          wx.showToast({
            title: data.msg,
            duration: 2000
          });
          return false;
        }
        that.globalData.userInfo = data;
        that.onLoginUser();
      },
      fail: function (e) {
        wx.showToast({
          title: '网络异常!',
          duration: 2000
        });
      },
    });
  },
  onLoginUser: function () {
    var that = this;
    var user = that.globalData.userInfo;
    wx.request({
      url: that.use.hostImg + '/wx/Login/authLogin',
      method: 'post',
      data: {
        SessionId: user.sessionId,
        // gender: user.gender,
        // nickName: user.nickName,
        // avatarUrl: user.avatarUrl,
        openid: user.openid
      },
      header: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      success: function (res) {
        var data = res.data.data;
        var status = res.data.status;
        if (status != 1) {
          wx.showToast({
            title: data.msg,
            duration: 3000
          });
          return false;
        }
        that.globalData.userInfo['id'] = data.id;
        that.globalData.userInfo['user_name'] = data.user_name ? data.user_name : '';
        that.globalData.userInfo['user_photo'] = data.user_photo ? data.user_photo : '';
        var userId = data.id;
        if (!userId) {
          wx.showToast({
            title: '登录失败！',
            duration: 3000
          });
          return false;
        }
        that.use.userId = userId;
      },
      fail: function (e) {
        wx.showToast({
          title: '网络异常2！err:authlogin',
          duration: 2000
        });
      },
    });
  },
  // 获取手机信息
  globalData: {
    userInfo: null,
    isIpx:false,
    access_token: null,
  }
})