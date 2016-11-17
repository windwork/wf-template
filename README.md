Windwork模板引擎
=================
Windwork通过模板引擎将视图从业务逻辑分离，便于前端与程序的分工协作。前端或设计师只需要简单的模板标签语法即可进行模板开发。
视图层将模型化的数据渲染为某种表现形式。负责用它得到的信息生成应用程序需要的任何表现界面。

视图层并非只限于 HTML 或文本格式的数据表现形式，它可以根据需要生成多种多样的格式， 比如视频、音乐、文档或其它任何你能想到的格式。

我们使用模板视图作为视图层。

# 模板文件夹
所有模板放在 src/template/文件夹中，每套模板放在一个文件夹。
默认模板文件放在 src/template/default文件夹中，可建另外的文件夹选择作为自定义模板。
系统管理后台、前台PC版、前台手机版分开存放到不同的文件夹中。当客户的为手机时，如果手机版模板文件不存在的时候则使用PC版模板，PC版也不存在的时候则提示“模板文件不存在”的错误。


1、使用模板
------------------
在控制器中使用 $this->view()调用模板实例。

### 模板变量赋值：
```
$this->view()->assign(‘变量’, “值”); // 变量名为字符串，值为任意数据类型。
```

### 显示模板：
默认模板文件存放于 src/template/default 文件夹中，
```
$this->view()->render($tpl = ‘模板文件’);  
$this->view()->render(); // 默认 $tpl = "{$mod}/{$ctl}.{$act}.html"
```

### 使用模板案例
```
class AccountController extends \wf\mvc\Controller {
    public function loginAction() {
        // 模板变量赋值
        $this->view()->assign('time', time());

        // 显示模板 src/template/default/user/account.login.html
        $this->view()->render();

        // 显示模板 src/template/default/user/account.login.m.html
        $this->view()->render('user/account.login.m.html');
    }
}
```

2、模板引擎语法
--------------
```
# foreach
  {loop $arr $var}
  ...
  {/loop}
  # 解析后同 
  <?php foreach($arr as $var) :?>
  ...
  <?php endforeach; ?>

  {loop $arr $k $v}
  ...
  {/loop}
  # 解析后同 
  <?php foreach($arr as $k => $v) :?>
  ...
  <?php endforeach; ?>

# if
  {if $a}

  {elseif $b}

  {else}

  {/if}

# for
  {for $a = 0; $a < $x; $a++}

  {/for}

# 变量输出
  {$var}
  {$arr[key]}
  {$arr[key1][key2]}

# 常量输出
  {CONST_XX} // 模板中的常量约定全部为大写

# 调用类、对象、函数并输出返回值
  // 调用
  {fncName()}
  {fncName($arg)}
  {$obj->method()}
  {\mymod\model\XXModel::fnc()}

  // 缩略图函数：
  <img src="{thumb($uploadPath, $width, $height)}" />  // 通过上传文件路径，指定宽高
  <img src="{thumb($uploadPath, $width, 0)}" />  // 高自动
  <img src="{thumb($uploadPath, 0, $height)}" />  // 宽自动

  // 用户头像地址函数：
  <img src="{avatar($uid, $type)}" />  // type： big|medium|small|tiny

# url 链接标签
  1)使用url标签 {url $mod.$ctl.$act/param1/param2/paramk1:paramv1/...}
  2)使用url函数 {url("$mod.$ctl.$act/param1/param2/paramk1:paramv1/...")}


# lang 语言标签：
  {lang xxx}

# 执行代码段：
1）{#任意PHP代码段...#}
2）<?php php代码 ?>

# static 不解析内容标签 
{static}
不解析的内容...
{/static}

# 模板标签注释
<!--{模板标签}--> 
模板解析时将去掉模板标签两边的html注释变成 {模板标签}

```

### 注：
{lang xx}与 {lang('xx')}的区别：
- **{lang xx}** 被模板引擎直接解析为语言变量值，lang标签后面不能是变量；
- **{lang('xx')}** 被模板引擎解析为函数调用，lang()参数可以是变量；

如：

```
$lang = ['name' => '姓名'];

// 模板中lang标签
name: {lang name} 
// 解析后模板代码为：
name: 姓名

// 模板中lang函数
name: {lang('name')}
// 解析后模板代码为：
name: <?php echo lang('name')?>
```

3、只使用windwork模板引擎，不使用windwork MVC
----------------------------------------

```
// 创建模板实例
$view = new \wf\template\Engine();

// 设置参数		
$view
->setCompiledDir('data/temp/tpl') // 保存编译后保存目录
->setTplDir('template/default')   // 模板文件目录
->setCompileId('zh_pc');          // 模板编译ID，用于区分编译后的文件名，可根据不同的模板文件类型、访问使用的语言进行区分

// 变量赋值
$view->assign('myVar', '123456');

// 使用模板 template/default/my/demo.html
$view->render('my/demo.html');

```