// pages/courseDetail/courseDetail.js
var app = getApp();
Page({

  /**
   * 页面的初始数据
   */
  data: {
    isIpx: app.globalData.isIpx,
    hostUrl: app.use.hostImg,
    dataOne:{},
    courseDetailImg:'',
    FqaImg:'',
    staImg:'',
    page:2,
    url: app.use.hostUrl,
    currentTab: '' //选项卡
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
  // 滑动底部触发事件
  courseDetailclick:function(e){
    var cateId = e.currentTarget.dataset.cateid,
      cid = e.currentTarget.dataset.cid;
      var page = this.data.page;
      var that = this;
      console.log(that.data.courseDetailImg);
      wx.request({
        url: app.use.hostUrl + '/wx/Course/getDetailImg', //课程详情
        data: {
          cid: cid, cate_id:cateId, type_n: 2, page: page
        },
        header: {
          'content-type': 'application/json' // 默认值
        },
        success: function (res) {
          var data = res.data.data.data;
          if (res.data.status != 1) {
            wx.showToast({
              title: res.data.msg,
              icon: 'none',
              duration: 2000
            });
            return false;
          }
          if (data.length < 1){
            wx.showToast({
              title: '已经到底了',
              icon: 'none',
              duration: 2000
            });
            return false;
          }
          if (cateId == 1){
            that.setData({
              courseDetailImg: that.data.courseDetailImg.concat(data),
            });
          } else if (cateId == 2){
            that.setData({
              FqaImg: that.data.FqaImg.concat(data),
            });
          }else{
            that.setData({
              staImg: that.data.staImg.concat(data),
            });
          }
          that.setData({
            page: page + 1,
          });
        },
        fail: function (e) {
          wx.showToast({
            title: '网络异常!',
            duration: 2000
          });
        },
      })
  },
  // 滑动顶部刷新
  topPush:function(e){
    var cid = e.currentTarget.dataset.cid;
    wx.request({
      url: app.use.hostUrl + '/wx/Course/course_detail', //课程详情
      data: {
        cid: cid
      },
      header: {
        'content-type': 'application/json' // 默认值
      },
      success: function (res) {
        var data = res.data;
        var courseDetailImg = data.data.courseDetailImg.data,//课程详情
          FqaImg = data.data.FqaImg.data, //常见问题
          staImg = data.data.staImg.data; //入学
        if (data.status != 1) {
          wx.showToast({
            title: data.msg,
            icon: 'none',
            duration: 2000
          });
          return false;
        }
        taht.setData({
          dataOne: data.data, //数据
          courseDetailImg: courseDetailImg,
          FqaImg: FqaImg,
          staImg: staImg
        });
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
    var title = options.title;  //课程标题
    var id = options.course_id;  //课程id options.course_id
    var taht = this;
    //更改头部标题
    wx.setNavigationBarTitle({
      title: title,
    });
    wx.request({
      url: app.use.hostUrl + '/wx/Course/course_detail', //课程详情
      data: {
        cid: id
      },
      header: {
        'content-type': 'application/json' // 默认值
      },
      success: function (res) {
        var data = res.data;
        // console.log(data);
        var courseDetailImg = data.data.courseDetailImg.data,//课程详情
          FqaImg = data.data.FqaImg.data, //常见问题
          staImg = data.data.staImg.data; //入学
        if (data.status != 1) {
          wx.showToast({
            title: data.msg,
            icon: 'none',
            duration: 2000
          });
          return false;
        }
        taht.setData({
          dataOne: data.data, //数据
          courseDetailImg:courseDetailImg,
          FqaImg: FqaImg,
          staImg: staImg
        });
        // console.log(data.data);
      },
      fail:function (e) {
        wx.showToast({
          title: '网络异常!',
          duration: 2000
        });
      },
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