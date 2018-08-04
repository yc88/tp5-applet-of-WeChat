// pages/user/user.js
var app = getApp();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    isIpx: app.globalData.isIpx,
    userInfo:{},
    user_radio:1,
    nickName:'',
    userList:[
      {
        url:'../listDetail/mycourse?uid=',
      img1: '../../images/3x/3x1.png',
      name: '我的课程',
      img2: '../../images/x_right.png'
      },
      {
        url: '../listDetail/myquestion?uid=',
        img1: '../../images/3x/3x2.png',
        name: '我的提问',
        img2: '../../images/x_right.png'
      },
      {
        url: '../info/info?uid=',
        img1: '../../images/3x/3x3.png',
        name: '关于我们',
        img2: '../../images/x_right.png'
      },
      // {
      //   url: '',
      //   img1: '../../images/3x/3x4.png',
      //   name: '咨询客服',
      //   img2: '../../images/x_right.png'
      // }
    ],
  },
  // 拨打电话
  goPhone: function (e) {
    var phone = e.currentTarget.dataset.phone;
    wx.makePhoneCall({
      phoneNumber: phone
    })
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    wx.hideShareMenu();//取消转发
    var that = this;
    //调用应用实例的方法获取全局数据
    app.getUserInfo(function(userInfo) {
      //更新数据
      userInfo = app.globalData.userInfo;
      if (userInfo['user_name'] && userInfo['user_photo']){
        that.setData({
          user_radio: 2,
        })
      }
      that.setData({
        userInfo: userInfo,
      })
    });
  },
  //获取用户信息
  getUserInfo: function (e) {
    var that = this;
    var uid = app.use.userId;
    var errMsg = e.detail.errMsg;
    if (errMsg == "getUserInfo:ok") { //用户同意授权
      // console.log(e);
      var userInfo = e.detail.userInfo;
      // console.log(uid);
      wx.request({
        url: app.use.hostUrl + '/wx/Login/editUser',
        method: 'post',
        data: {
          nickName: userInfo.nickName,
          gender: userInfo.gender,
          avatarUrl: userInfo.avatarUrl,
          uid: uid
        },
        header: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        success: function (res) {
          var status = res.data.status;
          var msg = res.data.msg;
          app.globalData.userInfo['user_name'] = userInfo.nickName;
          app.globalData.userInfo['user_photo'] = userInfo.avatarUrl;
          that.onPullDownRefresh();
           that.setData({
             user_radio: 2,
             nickName: userInfo.nickName
          })
          wx.switchTab({
            url: './user'
          });
        },
        fail: function (e) {
          wx.showToast({
            title: '网络异常！',
            duration: 2000
          });
        }
      })
    } else {
      wx.switchTab({
        url: './user'
      });
      return false;
    }

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
     var userId = app.use.userId;
     if(!userId){
      // wx.switchTab({
      //   url: '../index/index'
      // });
     }
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
    var that = this;
    //调用应用实例的方法获取全局数据
    app.getUserInfo(function (userInfo) {
      //更新数据
      userInfo = app.globalData.userInfo;
      if (userInfo['user_name'] && userInfo['user_photo']) {
        that.setData({
          user_radio: 2,
        })
      }
      that.setData({
        userInfo: userInfo,
      })
      wx.hideNavigationBarLoading() //完成停止加载
      wx.stopPullDownRefresh() //停止下拉刷新
    });
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