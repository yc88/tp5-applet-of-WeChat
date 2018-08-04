// pages/question/askquestion.js
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    userId: app.use.userId
  },
  formSubmit_ask: function (e) {
    var detail_centent = e.detail.value['questions'];
    var uid = app.use.userId;
    //验证问题是否为空
    if (!detail_centent){
          wx.showToast({
            title:'请填写您的问题',
            icon: 'loading',
            duration: 3000,
            mask:false
          });
          return false;
    } else if (detail_centent.length < 2){
      wx.showToast({
        title: '你的问题过短 请描述你的问题',
        icon: 'loading',
        duration: 3000,
        mask: false
      });
      return false;
    }
    //提交问题
    wx.request({
      url: app.use.hostUrl + '/wx/Question/get_question', //仅为示例，并非真实的接口地址
      data: {
        uid: uid,
        detail_question: detail_centent
      },
      header: {
        'content-type': 'application/json' // 默认值
      },
      success: function (res) {
        if(res.data.status != 1){
          wx.showToast({
            title: res.data.msg,
            duration: 2000,
            icon: 'none',
            success:function(){
              setTimeout(function () {
                //要延时执行的代码
                wx.navigateBack({
                  delta: 1
                })
              }, 2000) //延迟时间
            }
          });
          return false;
        }else{
          wx.showToast({
            title: '您的问题已提交，请耐心等待，一天之内给你答复',
            duration: 3000,
            icon: 'none',
            success:function(){
              setTimeout(function () {
                //要延时执行的代码
                wx.navigateBack({
                  delta: 1
                })
              }, 2000) //延迟时间
            }
          });
        }
      },
      fail: function (e) {
        wx.showToast({
          title: '网络异常!',
          duration: 2000
        });
      },
    })
  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    wx.hideShareMenu();//取消转发
    var userId = app.use.userId;
    if (!userId){
      wx.switchTab({
        url: './question'
      });
    }
  },
  // 改变数值
  change_value:function(e){
    console.log(e);
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