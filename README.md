xframework
==========
一个基于php5开发的web框架。80%面向对象，目前已在实际项目中使用，节约了我很多的时间；现在我把它放到github分享给大家。

###项目结构
  application
    bootstraps
    configs
    functions
    layouts
    controllers
    models
    modules
      appName1
        controllers
        models
        views
      appName2
      ....
    plugins
    views
  public
  temp
  xframework
 
###功能描述
- 特点
  - 多模块
  - 域名绑定指定到模块
  

- 全局
  - 配置文件
  - 常量
  - 运行时数据池
  - Functions辅助函数

- 启动器(Bootstrap)
  - 自定义init函数
  - 框架执行前处理一些事
  

- 插件(Plugin)
  - 什么是plugin?
  - 注册插件
  - 删除插件
  - 是否存在指定的插件
  - 获取所有的插件
  - 路由开始执行时
  - 路由完成时
  - 转发控制器之前
  - 转发控制器之后
  - Action执行之前
  - Action执行之后调用[渲染模板之前]
  - 404
  - 监听全局发现的异常


- 控制器(controller)
  - 精简的controller
  - 是否存在Action
  - 改变当前请的Action
  - 执行Action前的操作
  - 请求的参数
  - 缓存
  - 修改默认模板
  - 不使用模板
  

- 数据表(Table)
- 视图模板(View)
  - 布局
  - Header
 

- 路由器
  - 路由重写规则
  - 传统GET参数
  - 参数列表
  

- 常用验证
  - 身份证
  - 中文
  - 日期
  - 时间
  - 邮箱
  - ip地址
  - 手机
  - 电话
  - 邮编
