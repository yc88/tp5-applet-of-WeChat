// pages/listDetail/questionDetail.js
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    questionDetail:{},
    q_id:''
  },
  likeQuestion:function(e){
    var q_id = e.target.dataset.qid,
        uid = app.use.userId;
        var that = this;
    wx.request({
      url: app.use.hostUrl + '/wx/Question/question_like',
      method: 'post',
      data: { uid: uid, q_id: q_id },
      header: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      success: function (res) {
        var data = res.data.msg,
          status = res.data.status;
        // console.log(res);
        if (status!=1) {
          wx.showToast({
            title: data,
            icon: 'loading',
            duration: 2000, //提示的延迟时间，单位毫秒，默认：1500  
            mask: true,  //是否显示透明蒙层，防止触摸穿透，默认：false  
            success: function () {
              setTimeout(function () {
                //要延时执行的代码
                wx.navigateBack({
                  delta: 1
                })
              }, 2000) //延迟时间
            }
          })
        }else{
          wx.showToast({
            title: data,
            icon:'none',
            duration: 2000, //提示的延迟时间，单位毫秒，默认：1500  
            mask: true,  //是否显示透明蒙层，防止触摸穿透，默认：false  
            success: function () {
              setTimeout(function () {
                that.onPullDownRefresh();
              }, 2000) //延迟时间
            }
          })
        }
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
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    wx.hideShareMenu();//取消转发
       var q_id = options.qid,
         uid = (app.use.userId) ? (app.use.userId) : null;
          var that = this;
        if(!q_id){
          wx.showToast({
            title: '未知错误',
            icon:'loading',
            duration: 2000, //提示的延迟时间，单位毫秒，默认：1500  
            mask: true,  //是否显示透明蒙层，防止触摸穿透，默认：false  
            success:function(){
              setTimeout(function () {
                //要延时执行的代码
                wx.navigateBack({
                  delta:1
                })
              }, 2000) //延迟时间
            }
          })
          return false;
        }
        wx.request({
          url: app.use.hostUrl + '/wx/Question/question_detail',
          method: 'post',
          data: { uid: uid,q_id:q_id},
          header: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          success: function (res) {
            var questionDetail = res.data.data,
                status = res.data.status;
                // console.log(questionDetail);
              if(status!=1){
                wx.showToast({
                  title: res.datal.msg,
                  icon: 'loading',
                  duration: 2000, //提示的延迟时间，单位毫秒，默认：1500  
                  mask: true,  //是否显示透明蒙层，防止触摸穿透，默认：false  
                  success: function () {
                    setTimeout(function () {
                      //要延时执行的代码
                      wx.navigateBack({
                        delta: 1
                      })
                    }, 2000) //延迟时间
                  }
                })
              }
            that.setData({
              questionDetail: questionDetail,
              q_id: q_id
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
    var uid = (app.use.userId) ? (app.use.userId) : null;
      var q_id = this.data.q_id;
      var that = this;
      wx.request({
        url: app.use.hostUrl + '/wx/Question/question_detail',
        method: 'post',
        data: { uid: uid, q_id: q_id },
        header: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        success: function (res) {
          var questionDetail = res.data.data,
            status = res.data.status;
          // console.log(questionDetail);
          if (status != 1) {
            wx.showToast({
              title: res.datal.msg,
              icon: 'loading',
              duration: 2000, //提示的延迟时间，单位毫秒，默认：1500  
              mask: true,  //是否显示透明蒙层，防止触摸穿透，默认：false  
              success: function () {
                setTimeout(function () {
                  //要延时执行的代码
                  wx.navigateBack({
                    delta: 1
                  })
                }, 2000) //延迟时间
              }
            })
          }
          that.setData({
            questionDetail: questionDetail,
            q_id: q_id
          });
          //endInitData
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
  
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
  
  }
})