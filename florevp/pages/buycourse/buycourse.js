// pages/buycourse/buycourse.js
var app = getApp();
Page({
  /**
   * 页面的初始数据
   */
  data: {
    isIpx: app.globalData.isIpx,
    // buy_type: ['全款支付', '定金支付'],
    buy_type:'定金支付',
    courseData: {},
    courseOne: {},
    courseId: '',
    type_id: 1,
    is_agreement:2,
    type_inde: 0,
    index: 0,
    checked_index: 0,
    nub_cursor:2,
    phone: true,
    code: true,
    money: '',
    statusCss: '',
    phone: '',
    yzm: '获取验证码',
    s_unit:'',
    yzmDisabled: true,
    form_status: '', //submit
    form_status_css: 'form_status_css'
  },
  // 短信验证码
  getYzm: function (e) {
    var self = this;
    self.sendSms(e);
  }, 
  changeYzm: function () {
    var self = this;
    var n = 60;
    self.setData({
      //禁用button
      yzmDisabled: true,
      yzm: n,
      s_unit:'s'
    })
    var yzmInterval = setInterval(function () {
      if (self.data.yzm <= 0) {
        self.setData({
          yzm: '获取验证码',
          yzmDisabled: false,
          s_unit: ''
        })
        clearInterval(yzmInterval);
      } else {
        n = n - 1;
        self.setData({
          yzm: n,
          s_unit: 's'
        })
      }
    }, 1000)
  },

  // 发送短信验证码
  sendSms: function (e) {
    var phone = e.target.dataset.phone;
    var uid = app.use.userId;
    var that = this;
    wx.request({
      url: app.use.hostUrl + '/wx/Buy/SmsSend',
      method: 'post',
      data: { uid: uid, phone: phone },
      header: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      success: function (res) {
        var status = res.data.status;
        var data = res.data.msg;
        if (status != 1) {
          wx.showToast({
            title: data,
            icon: 'none',
          }) 
          that.setData({
            form_status_css: 'form_status_css',
            form_status: "",
            statusCss: '',
          })
          return false;
        }
        that.changeYzm();
        wx.showToast({
          title: data,
          icon: 'none',
        })
        that.setData({
          form_status_css: 'form_status_css_ok',
          form_status: "submit",
          statusCss: 'but_after'
        })
        // console.log(courseData);
      },
      fail: function (res) {
        wx.showToast({
          title: '网络异常！',
          duration: 2000
        });
      }
    })
  },
  //验证用户名称
  checked_name(e) {
    console.log(e);
    var value = e.detail.value;
    var that = this;
    if (!value) {
      wx.showToast({
        title: '用户名不能为空',
        icon: 'none',
        duration: 2000
      })
      return false;
    }
  },
  // change_phone: function (e) {
  //   console.log(e);
  // },
  //获取焦点离开之后验证手机号码是否存在
  checked_phone: function (e) {
    var that = this;
    var phone = e.detail.value;
    if (!phone) {
      wx.showToast({
        title: '手机号不能为空',
        icon: 'none',
        duration: 2000
      })
      that.setData({
        phone: '',
        statusCss: 'but_after',
        yzmDisabled: true,
      })
      return false;
    }
    var myreg = /^(((13[0-9]{1})|(15[0-9]{1})|(18[0-9]{1})|(17[0-9]{1}))+\d{8})$/;
    if (!myreg.test(phone)) {
      wx.showToast({
        title: '手机号有误',
        icon: 'none',
        duration: 2000
      })
      return false;
    }
    that.setData({
      phone: phone,
      statusCss: 'but_after',
      yzmDisabled: false,
    })
  },
  // 验证手机号码 输入编辑 返回 
  checked_phone1: function (e) {
    console.log(e);
    var phone = e.detail.value;
    var that = this;
    if (phone.length == 11) {
      var myreg = /^(((13[0-9]{1})|(15[0-9]{1})|(18[0-9]{1})|(17[0-9]{1}))+\d{8})$/;
      if (!myreg.test(phone)) {
        wx.showToast({
          title: '手机号有误1',
          icon: 'none',
          duration: 2000
        })
        return false;
      }
      console.log(111);
      that.setData({
        phone: phone,
        statusCss: 'but_after',
        yzmDisabled: false,
      })
    } else {
      if (phone.length < 11) {
        that.setData({
          phone: '',
          statusCss: 'but_after',
          yzmDisabled: true,
        })
      }
    }
  },

  //验证验证码
  checked_code(e) {
    var value = e.detail.value;
    var that = this;
    if (!value) {
      wx.showToast({
        title: '验证码不能为空',
        icon: 'none',
        duration: 2000
      })
      return false;
    }
  },
  // 选择课程购买页面的 下拉框事件
  bindPickerChange: function (e) {
    var courseData = this.data.courseData;
    var checked = courseData[e.detail.value];
    var value = e.detail.value;
    var that = this;
    this.setData({
      courseId: checked.id,
      checked_index: value,
      money: checked.depoit_price,
      index: value,
      type_inde: 0
    })
  },
  //支付类型选择
  // bindPickerChangetype: function (e) {
  //   var courseData = this.data.courseData;
  //   var checkd_index = e.target.id;
  //   var value = e.detail.value; //类型type_index
  //   var checked = courseData[checkd_index];
  //   var that = this;
  //   if (value == 1) {
  //     that.setData({
  //       money: checked.depoit_price
  //     });
  //   } else {
  //     that.setData({
  //       money: checked.courser_price
  //     });
  //   }
  //   // console.log('picker发送选择改变，携带值为', e.detail.value)
  //   that.setData({
  //     type_id: value,
  //     type_inde: e.detail.value
  //   })
  // },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    wx.hideShareMenu();//取消转发
    var id = options.cid; //选择的课程id options.cid
    var that = this;
    that.setData({
      courseId: id,
    })
    that.getOneCourse(id);
    that.getAllCourse(id);
  },
  /**
   * 获取所有的课程
   */
  getAllCourse: function (id) {
    var that = this;
    var id = id;
    wx.request({
      url: app.use.hostUrl + '/wx/Course/course_list',
      method: 'post',
      data: { cid: id },
      header: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      success: function (res) {
        var courseData = res.data.data,
          status = res.data.status;
        that.setData({
          courseData: courseData
        });
        // console.log(courseData);
      },
      fail: function (res) {
        wx.showToast({
          title: '网络异常5！',
          duration: 2000
        });
      }
    })
  },
  //获取当前选择的课程
  getOneCourse: function (id) {
    var that = this;
    var id = id;
    wx.request({
      url: app.use.hostUrl + '/wx/Course/course_list_one',
      method: 'post',
      data: { cid: id },
      header: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      success: function (res) {
        var courseData = res.data.data,
          status = res.data.status;
        that.setData({
          courseOne: courseData,
          money: courseData.depoit_price
        });
      },
      fail: function (res) {
        wx.showToast({
          title: '网络异常6！',
          duration: 2000
        });
      }
    })
  },
  // 协议同意
  checkboxChange: function (e) {
    var that = this;
    that.setData({
      is_agreement: e.detail.value,
    })
  },
  // 查看协议
  show_agreement:function(){
    wx.downloadFile({
      url: 'https://wx.florevp.com/agreement.doc',
      success: function (res) {
        var filePath = res.tempFilePath
        wx.openDocument({
          filePath: filePath,
          success: function (res) {
            return true;
          }
        })
      }
    })
  },
  //form 表单提交
  formSubmit_buy: function (e) {
    var data = e.detail.value;
    var real_name = data.real_name,
      phone = data.phone,
      code = data.code,
      course_id = data.course_id,
      type_id = data.type_id,
      is_agreement = data.is_agreement;
    var uid = app.use.userId;
    if (!real_name) {
      wx.showToast({
        title: '亲，请填写您的称呼',
        icon: 'none',
        duration: 3000,
        mask: false
      });
      return false;
    }
    if (!phone) {
      wx.showToast({
        title: '亲，手机号不能为空哟',
        icon: 'none',
        duration: 3000,
        mask: false
      });
      return false;
    }
    var myreg = /^(((13[0-9]{1})|(15[0-9]{1})|(18[0-9]{1})|(17[0-9]{1}))+\d{8})$/;
    if (!myreg.test(phone)) {
      wx.showToast({
        title: '亲，手机号码有误哟',
        icon: 'none',
        duration: 2000
      })
      return false;
    }
    if (!code) {
      wx.showToast({
        title: '亲，请填写验证码',
        icon: 'none',
        duration: 3000,
        mask: false
      });
      return false;
    }
    if (is_agreement != 2){
      wx.showToast({
        title: '亲，请同意报名协议',
        icon: 'none',
        duration: 3000,
        mask: false
      });
      return false;
    }
    // 下订单
    wx.request({
      url: app.use.hostUrl + '/wx/Buy/createOrder', //
      mothed: 'post',
      data: {
        uid: uid,
        real_name: real_name,
        phone: phone,
        code: code,
        course_id: course_id,
        type_id: type_id,
        is_agreement: is_agreement
      },
      header: {
        'content-type': 'application/json' // 默认值
      },
      success: function (res) {
        var status = res.data.status;
        var data = res.data.data;
        if (status != 1) {
          // 下单失败 
          wx.showToast({
            title: res.data.msg,
            duration: 2000,
            icon: 'none',
            success: function () {
              setTimeout(function () {
                //要延时执行的代码
                wx.navigateBack({
                  delta: 1
                })
              }, 2000) //延迟时间
            }
          });
          return false;
        } else {
          // 下单成功进行支付
          var order_no = data.order_no;
          var userId = data.userId;
          var that = this;
          //现在进行统一下单
          wx.request({
            url: app.use.hostUrl + '/wx/Weipay/wxPay',
            method: 'post',
            data: { userId: userId, order_no: order_no },
            header: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            success: function (res) {
              var status = res.data.status;
              var data = res.data.data;
              if (status != 1) {
                wx.showToast({
                  title: '支付失败',
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
                              typed_id:2,
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
                                title: '网络异常1!',
                                duration: 2000
                              });
                            }
                          })
                      }, fail: function () {
                        wx.showToast({
                          title: '网络异常2!',
                          duration: 2000
                        });
                      }
                    })
                    wx.switchTab({
                      url: '../index/index'
                    });
                    // setTimeout(function () {
                    //   wx.navigateTo({
                    //     url: '../courseDetail/courseDetail?course_id=' + course_id,
                    //   });
                    // }, 1000);
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
                title: '网络异常3！',
                duration: 2000
              });
            }
          })

        }
      },
      fail: function (e) {
        wx.showToast({
          title: '网络异常4!',
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