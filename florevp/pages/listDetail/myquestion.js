// pages/listDetail/mycourse.js
var app = getApp();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    question_status: '',
    question_status1: 1,
    status1:1,
    existQuestion:{},
    returnQuestion:{},
    page : 2
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
     wx.hideShareMenu();//取消转发
     var userId = options.uid; //options.uid
        // console.log(userId);
        var that = this;
        wx.request({
          url: app.use.hostUrl + '/wx/User/my_question',
          method: 'post',
          data: { uid: userId },
          header: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          success: function (res) {
              var status = res.data.status,
                  answer = res.data.data.answer,
                  no_answer = res.data.data.no_answer;
              if (status != 1 ){
                    wx.showToast({
                      title: res.data.msg,
                      duration: 2000
                    })
                    return false;
                  }
                  //切换是否已经提问
                  if (no_answer.data.length < 1 && answer.data.length < 1){
                      that.setData({
                        question_status: '',
                        question_status1: 1
                      });
                  }else{
                    that.setData({
                      question_status: 1,
                      question_status1: ''
                    });
                  }
                  that.setData({
                    existQuestion: no_answer.data,
                    returnQuestion: answer.data
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
        },
        fail: function (e) {
          wx.showToast({
            title: '网络异常！',
            duration: 2000
          });
        }
      })
    } else {
      wx.navigateTo({
        url: '../listDetail/myquestion?uid=' + app.use.userId
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
  onPullDownRefresh: function (res) {
    var userId = app.use.userId;
    var that = this;
    wx.request({
      url: app.use.hostUrl + '/wx/User/my_question',
      method: 'post',
      data: { uid: userId },
      header: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      success: function (res) {
        var status = res.data.status,
          answer = res.data.data.answer.data,
          no_answer = res.data.data.no_answer.data;
        if (status != 1) {
          wx.showToast({
            title: res.data.msg,
            duration: 2000
          })
          return false;
        }
        if (answer.length > 0 || no_answer.length > 0){
            that.setData({
              question_status: 1,
              question_status1: ''
            });
        }else{
          that.setData({
            question_status: '',
            question_status1: 1
          });
        }
        that.setData({
          existQuestion: no_answer,
          returnQuestion: answer,
          page:2
        })
      },
      fail: function (e) {
        wx.showToast({
          title: '网络异常！',
          duration: 2000
        });
      },
      complete: function (e) {
        wx.hideNavigationBarLoading() //完成停止加载
        wx.stopPullDownRefresh() //停止下拉刷新
      }
    })
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
    var that = this;
    var userId = app.use.userId;
    var page = that.data.page;
    wx.request({
      url: app.use.hostUrl + '/wx/User/my_question',
      method: 'post',
      data: { page: page, uid: userId},
      header: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      success: function (res) {
        var status = res.data.status,
          answer = res.data.data.answer,
          no_answer = res.data.data.no_answer;
        if (status != 1) {
          wx.showToast({
            title: res.data.msg,
            duration: 2000
          })
          return false;
        }
        if (answer.data.length == 0 && no_answer.data.length == 0) {
          wx.showToast({
            title: '没有更多数据！',
            icon: 'none',
            duration: 2000
          });
          return false;
        }
        that.setData({
          page: page + 1,
          existQuestion: that.data.existQuestion.concat(no_answer.data),
          returnQuestion: that.data.returnQuestion.concat(answer.data)
        });
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
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
  
  }
})