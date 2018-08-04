// pages/listDetail/mycourse.js
var app = getApp();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    is_deposit:1,
    orderList :{},
    page:2,
    goToCourse:1,
  
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    wx.hideShareMenu();//取消转发
    this.getMyOrder();
  },

//我的订单获取
getMyOrder:function(e){
  var userId = app.use.userId;
  var that = this;
  wx.request({
    url: app.use.hostUrl + '/wx/User/my_courses_order',
    method: 'post',
    data: { uid: userId},
    header: {
      'Content-Type': 'application/x-www-form-urlencoded'
    },
    success: function (res) {
       var order = res.data.data.data,
         status = res.data.data.status;
      // console.log(res);
      // console.log(order);
      var status = res.data.status;
      if (status != 1) {
        wx.showToast({
          title: res.data.msg,
          icon:none,
          duration: 2000
        })
        return false;
      }
      if (order.length > 0){
        that.setData({
          goToCourse: 0,
        })
      }
      that.setData({
        orderList: order,
      })
    },
    fail: function (e) {
      wx.showToast({
        title: '网络异常1！',
        duration: 2000
      });
    }
  })
},

//我的余款支付
/** 
  buy_residue(e){
    // var money = e.currentTarget.dataset.money;
    var order_no = e.currentTarget.dataset.order;
    var userId = app.use.userId;
    //现在进行统一下单
    wx.request({
      url: app.use.hostUrl + '/wx/Buy/buyResidue',
      method: 'post',
      data: { uid: userId, order_no: order_no },
      header: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      success: function (res) {
        var status = res.data.status;
        var data = res.data.data;
        // console.log(data);
        // return false;
        if (status != 1) {
          wx.showToast({
            title: '操作失败',
            icon:'none',
            duration: 2000
          });
          return false;
        } else {
          wx.requestPayment({
            timeStamp: data.timeStamp,
            nonceStr: data.nonceStr,
            package: data.package,
            signType: "MD5",
            paySign: data.paySign,
            success: function (res) {
              wx.showToast({
                title: "支付成功!稍后老师联系你!",
                icon: 'none',
                duration: 2000,
              });
              // 服务通知 
              wx.request({ //获取getAccessToken
                url: app.use.hostUrl + '/wx/Login/getAccessToken',
                success: function (res) {
                  // var access_token = res.data.access_token;
                  // var expires_in = res.expires_in; //7200 毫秒
                  // console.log(res.data);
                  app.globalData.access_token = res.data;
                  // 根据accessToken 组装服务通知
                  wx.request({ //获取getAccessToken
                    url: app.use.hostUrl + '/wx/Buy/pushServiceInfo',
                    method: 'get',
                    header: {
                      'content-type': 'application/json' // 默认值
                    },
                    data: {
                      uid: userId,
                      access_token: app.globalData.access_token.access_token,
                      prepay_id: data.prepay_id,
                      order_no: data.order_no,
                      typed_id: 1,
                    },
                    success: function (res) {
                      if (res.errcode != 0) {
                        wx.showToast({
                          title: res.errmsg,
                          icon: 'none',
                          duration: 2000
                        });
                      }
                    }, fail: function () {
                      wx.showToast({
                        title: '网络异常3!',
                        duration: 2000
                      });
                    }
                  })
                }, fail: function () {
                  wx.showToast({
                    title: '网络异常4!',
                    duration: 2000
                  });
                }
              })
              setTimeout(function () {
                wx.navigateTo({
                  url: '../listDetail/mycourse',
                });
              }, 1000);
            },
            fail: function (res) {
              var errMsg = res.errMsg;
              if (errMsg == 'requestPayment:fail cancel') {
                wx.showToast({
                  title: '你取消了支付',
                  icon: 'none',
                  duration: 3000
                })
              }
              wx.showToast({
                title: res.err_desc,
                icon: 'none',
                duration: 3000
              })
            }
          })
        }
      },
      fail: function (res) {
        wx.showToast({
          title: '网络异常5！',
          duration: 2000
        });
      }
    })
    console.log(e);
    return false;

  },
**/
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
    var userId = app.use.userId;
    var that = this;
    wx.request({
      url: app.use.hostUrl + '/wx/User/my_courses_order',
      method: 'post',
      data: { uid: userId },
      header: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      success: function (res) {
        var order = res.data.data.data,
          status = res.data.data.status;
        var status = res.data.status;
        if (status != 1) {
          wx.showToast({
            title: res.data.msg,
            duration: 2000
          })
          return false;
        }
        if (order.length > 0) {
          that.setData({
            goToCourse: 0,
          })
        }
        that.setData({
          orderList: order,
          page:2
        })
      },
      fail: function (e) {
        wx.showToast({
          title: '网络异常！',
          duration: 2000
        });
      }, complete: function (e) {
        wx.hideNavigationBarLoading() //完成停止加载
        wx.stopPullDownRefresh() //停止下拉刷新
      }
    })
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
    var userId = app.use.userId;
    var that = this,
        page = that.data.page;
    wx.request({
      url: app.use.hostUrl + '/wx/User/my_courses_order',
      method: 'post',
      data: { uid: userId, page: page},
      header: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      success: function (res) {
        var order = res.data.data.data,
          status = res.data.data.status;
        // var status = res.data.status;
        if (status != 1) {
          wx.showToast({
            title: res.data.msg,
            duration: 2000
          })
          return false;
        }
        that.setData({
          page: page + 1,
          orderList: that.data.orderList.concat(order),
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

  goToCourse(){
    wx.switchTab({
      url: '../index/index',
    })
  },
  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
  
  }
})