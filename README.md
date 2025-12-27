**项目概述**
- **名称**: 特色时令产品平台 (简易 PHP + MySQL)
- **说明**: 一个基于原始 PHP + MySQL 的小型商品交易与管理示例，包含用户发布商品、购物车、下单、投诉与管理员处理功能。

**主要功能**
- **用户:** 注册、登录、发布产品、加入购物车、从购物车下单、提交投诉。
- **管理员:** 产品下架/恢复、处理投诉、冻结用户。

**运行环境与依赖**
- **PHP**: 7.x 或更高（推荐使用 XAMPP / WAMP / PHP 内置服务器）。
- **MySQL**: 需创建并填充数据库，默认数据库名见 `config.php`。

**数据库配置**
- 配置文件: [config.php](config.php)
- 默认示例配置（请根据实际环境修改）:

```
$host = 'localhost:3308';
$user = 'root';
$pwd = '';
$dbname = 'try4_2023150020';
```

**快速安装与运行**
1. 将项目放到 Web 服务器根目录（例如 XAMPP 的 `htdocs`），或在项目根目录使用 PHP 内置服务器：

```
php -S localhost:8000
```

2. 在 MySQL 中创建数据库并导入所需表（项目中使用表：`user`、`usertype`、`product`、`producttype`、`buycar`、`orderinfo`、`complaint`、`ctype` 等）。
3. 编辑 [config.php](config.php) 中的数据库连接信息，设置正确的 `host`/`user`/`pwd`/`dbname`。
4. 通过浏览器访问 `http://localhost:8000/`（或你服务器对应的地址），使用注册页面创建用户并登录。

**主要页面与代码位置**
- **入口/首页**: [index.php](index.php)
- **用户认证**: [login.php](login.php), [register.php](register.php)
- **公共工具与鉴权**: [common.php](common.php)
- **管理员功能**: [admin/product_manage.php](admin/product_manage.php), [admin/complaint_handle.php](admin/complaint_handle.php), [admin/user_freeze.php](admin/user_freeze.php)
- **用户功能**: [user/product_publish.php](user/product_publish.php), [user/buycar.php](user/buycar.php), [user/order_create.php](user/order_create.php), [user/complaint.php](user/complaint.php), [user/user_product_manage.php](user/user_product_manage.php)
- **样式**: `css/style.css`

**安全与注意事项**
- 当前示例中用户密码以明文形式存储/比较（见 `register.php` / `login.php`），上线前必须使用 `password_hash()` / `password_verify()` 进行加密存储。
- 所有输入应进一步做参数绑定或使用预处理语句（prepared statements）防止 SQL 注入。
- `config.php` 中的数据库密码不应提交到公开仓库。

**可能的改进**
- 使用 Composer 管理依赖并引入框架或 ORM（如 PDO + prepared statements）。
- 增加单元/集成测试和基本前端验证。
- 完善错误与日志记录，增加文件上传与图片支持。

**许可证与作者**
- 本仓库为课程/示例项目代码。请按需要补充作者信息和许可证声明。

如需我把 README 调整为英文、添加数据库建表语句或生成 demo 数据，请告诉我。
