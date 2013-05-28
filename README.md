xframework
==========
一个基于php5面向对象的web框架。目前已在实际项目中使用，节约了我很多的时间；现在通过github分享给大家。有兴趣的同学可以看看，
文档会慢慢的完善！

###标准的项目结构
```javascript
  application
   |-bootstraps
   |-configs
   |-functions
   |-layouts
   |-controllers
   |-models
   |-modules
   |  |-appName1
   |  |  |-controllers
   |  |  |-models
   |  |  |-views
   |  |-appName2
   |  |....
   |-plugins
   |-views
  public
  temp
  xframework //框架
```
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
  - Action执行之后(渲染模板之前)
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
  - 说明
  - 数据配置
  - 初始化的属性
  - 字段值get/set
  - 填充表对象
  - 返回对象的数组格式
  - 表对象是否为空？
  - 字段关联
  - 字段关联状态：自动/手动
  - init方法
  - 查询
  - 删除
  - 更新
  - 打印Sql
  - 数据分页器

- 视图模板(View)
  - 路径约定
  - 模板语法
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
