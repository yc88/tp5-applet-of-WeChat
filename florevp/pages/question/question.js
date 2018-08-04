// pages/question/question.js
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: { 
      questionBut:true,
      myquestio:'',
      other_question:'',
      page: 2,
      is_exists:1,
      exist_css:'no_exist',
      userId:'',
      get_more_title: '没有更多数据' //点击获取更多

  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    wx.hideShareMenu();//取消转发
    this.getquestionList();
  },
  //点击加载更多
  getMore: function (e) {
    var that = this;
    var page = that.data.page;
    wx.request({
      url: app.use.hostUrl + '/wx/Question/list_index',
      method: 'post',
      data: { page: page },
      header: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      success: function (res) {
        var other_question = res.data.data.other_question.data;
        if (other_question.length == 0) {
          wx.showToast({
            title: '没有更多数据！',
            icon:'none',
            duration: 2000
          });
          return false;
        }
        //that.initProductData(data);
        that.setData({
          page: page + 1,
          other_question: that.data.other_question.concat(other_question)
        });
        //endInitData
      },
      fail: function (e) {
        wx.showToast({
          title: '网络异常！',
          duration: 2000
        });
      }
    })
  },
  //获取咨询页面的数据
  getquestionList:function(res){
    var that = this;
    var uid = app.use.userId;
    wx.request({
      url: app.use.hostUrl + '/wx/Question/list_index', //仅为示例，并非真实的接口地址
      data: {
        uid: uid
      },
      header: {
        'content-type': 'application/json' // 默认值
      },
      success: function (res) {
        var myquestio = res.data.data.my_question;
        var other_question = res.data.data.other_question.data;
        var page = res.data.data.other_question.current_page; //当前页数
        // console.log(res);
        if (res.data.status != 1) {
          wx.showToast({
            title: res.data.msg,
            icon: 'none',
            duration: 2000
          });
          return false;
        }
        if (myquestio){
          that.setData({
            is_exists:0,
            userId: app.use.userId
          });
        }
        if (other_question.lenght > 0) {
          that.setData({
            get_more_title: '点击获取更多',
          });
        }
        
        that.setData({
          myquestio: myquestio,
          other_question: other_question,
          exist_css: 'is_exist'
        });
        //console.log(other_question);
      },
      fail: function (e) {
        wx.showToast({
          title: '网络异常!',
          duration: 2000
        });
      },
    })
  },
  //获取用户信息
  getUserInfo: function (e) {
    var that = this;
    var uid = app.use.userId;
    var errMsg = e.detail.errMsg;
    if (errMsg == "getUserInfo:ok"){ //用户同意授权
      // console.log(e);
      var userInfo = e.detail.userInfo;
      // console.log(uid);
      wx.request({
        url: app.use.hostUrl + '/wx/Login/editUser',
        method: 'post',
        data: { 
          nickName: userInfo.nickName, 
          gender: userInfo.gender,
          avatarUrl:userInfo.avatarUrl,
          uid: uid
         },
        header: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        success: function (res) {
          var  status = res.data.status;
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
    }else{
      wx.switchTab({
        url: '../question/question'
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
  onPullDownRefresh(){
    wx.showNavigationBarLoading();
    var that = this;
    var uid = app.use.userId;
    wx.request({
      url: app.use.hostUrl + '/wx/Question/list_index', //仅为示例，并非真实的接口地址
      data: {
        uid: uid
      },
      header: {
        'content-type': 'application/json' // 默认值
      },
      success: function (res) {
        var myquestio = res.data.data.my_question;
        var other_question = res.data.data.other_question.data;
        var page = res.data.data.other_question.current_page; //当前页数
        // console.log(res);
        if (res.data.status != 1) {
          wx.showToast({
            title: res.data.msg,
            icon: 'none',
            duration: 2000
          });
          return false;
        }
        if (myquestio) {
          that.setData({
            is_exists: 0,
            userId: app.use.userId
          });
        }
        that.setData({
          myquestio: myquestio,
          other_question: other_question,
          page:2
        });
        //console.log(other_question);
      },
      fail: function (e) {
        wx.showToast({
          title: '网络异常!',
          duration: 2000
        });
      }, 
      complete: function (e){
    wx.hideNavigationBarLoading() //完成停止加载
            wx.stopPullDownRefresh() //停止下拉刷新
      }
    })
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom() {
    this.getMore();
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
  
  }
})