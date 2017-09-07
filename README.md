Windwork模板引擎
=================
Windwork模板引擎模板引擎是一个超轻量级“编译”型模板引擎,10分钟即可完全掌握。

Windwork通过模板引擎将视图从业务逻辑分离，便于前端与程序的分工协作。前端或设计师只需要简单的模板标签语法即可进行模板开发。
视图层将模型化的数据渲染为某种表现形式。负责用它得到的信息生成应用程序需要的任何表现界面。

模板示例
```
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>wf-template usage</title>
    </head>
    <body>
        <ul id="nav">
            <!-- 循环 -->
            {loop nav() $navItem}
            <li><a href="{{$navItem[url]}}">{{$navItem[title]}}</a></li>
            {/loop}
            <!-- END 循环 -->
        </ul>
        <!-- 条件 -->
        <!-- {if $_SESSION['uid']} -->
        <a href="logout.html">Logout</a>
        <!-- {else} -->
        <a href="login.html">Login</a>
        <!-- {/if} -->
        <!-- END 条件 -->

        <h1>My Webpage</h1>        
        <!-- 输出常量 -->
        {{MY_CONST}}   

        <!-- 输出变量 -->
        {{$myAariable}}  
   
        <!-- 输出函数 -->
        {{call_fnc()}}
   
        <!-- 输出静态方法调用 -->
        {{\call\my\ComponentClass::fnc($arg1, $arg2)}}
   
        <!-- 输出数组变量 -->
        {{$arr[key]}}
        {{$arr[key2][key2]}}
    </body>
</html>
```

## 安装
该组件已包含在Windwork框架中，如果你已安装Windwork框架则可以直接使用。

- 安装方式一：通过composer安装（推荐）  
```
composer require windwork/wf
```

- 安装方式二：传统方式安装  
[下载源码](https://github.com/windwork/wf/releases)后，解压源码到项目文件夹中，然后require_once $PATH_TO_WF/core/lib/Loader.php文件，即可自动加载组件中的类。

# 1、模板文件夹
- 默认模板文件放在 ./template/，可自定义模板文件夹。
- 建议系统管理后台、前台PC版、前台手机版可以分开存放到不同的文件夹中。
- 模板引擎提供自定义模板文件夹的配置，详见模板引擎**配置参数**部分

## Windwork模板引擎


# 2、使用模板
在Windwork控制器中使用 $this->view()调用模板实例。

## 2.1 模板变量赋值：
```
$this->view()->assign(‘变量’, “值”); // 变量名为字符串，值为任意数据类型。
```

## 2.2 显示模板：
默认模板文件存放于模块文件夹下的view文件夹中，使用render方法显示模板。
```
$this->view()->render($tpl = ‘模板文件’, $spare = "备选模板文件");  

// tpl1.html 不存在的时候，使用tpl2.html,tpl2.html也不存在则报tpl1.html不存在的异常  
$this->view()->render(‘tpl1.html’, "tpl2.html");  

// 默认 $tpl = "{$mod}/{$ctl}.{$act}.html"
$this->view()->render(); 

```

## 2.3 在控制器中使用模板
如下为在Windwork MVC的控制器中使用模板引擎
```
class AccountController extends \wf\app\web\Controller {
    public function loginAction() {
        // 模板变量赋值
        $this->view()->assign('time', time());

        // 显示模板 app/user/view/account.login.html
        $this->view()->render();

        // 显示模板 app/user/view/account.login.m.html
        $this->view()->render('user/account.login.m.html');
    }
}
```

# 3、模板引擎语法

## 3.1 输出
输出代码写在两对大括号中间，如{{$var}} 被解析成 <?php echo $var; ?>。
{{后不能有空格，否则不会被解析。
非输出标签只有一对大括号。
### 3.1.1 变量输出 
  - 标量变量 {{$var}}
  - 数组变量 {{$arr[key]}}
  - 多维数组 {{$arr[key1][key2]}}


### 3.1.2 常量输出
  {{CONST_XX}} // 模板中的常量约定全部为大写

### 3.1.3 调用类、对象、函数并输出返回值
 - 使用双大括号{{}}调用函数、方法并输出内容，
 - 如果不输出内容，使用{# ... #}

```
  {{fncName()}}
  {{fncName($arg)}}
  {{$obj->method()}}
  {{\app\myapp\model\XXModel::fnc()}}

  // 缩略图函数（该函数为Windwork框架定义的函数）
  <img src="{{thumb($uploadPath, $width, $height)}}" />  // 通过上传文件路径，指定宽高
  <img src="{{thumb($uploadPath, $width, 0)}}" />  // 高自动
  <img src="{{thumb($uploadPath, 0, $height)}}" />  // 宽自动

  // 用户头像地址函数（avatar函数为Windwork框架定义的函数）
  <img src="{{avatar($_SESSION['uid'], 'small')}}" />  // 第二个参数头像尺寸 size： big|medium|small|tiny
```
### 3.1.4 url 链接标签
  1)使用url标签 {{url $mod.$ctl.$act/param1/param2/paramk1:paramv1/...}}
  2)使用url函数 {{url("$mod.$ctl.$act/param1/param2/paramk1:paramv1/...")}}


### 3.1.5 lang 

#### 3.1.5.1 lang 语言标签：
  {{lang nickname}}

#### 3.1.5.2 lang函数：
  {{lang("nickname")}}

#### 3.1.5.3 {{lang xx}}与 {{lang('xx')}}的区别：
- **{{lang xx}}** 被模板引擎直接解析为语言变量值，lang标签后面不能是变量；
- **{{lang('xx')}}** 被模板引擎解析为函数调用，lang()参数可以是变量；
如：

```
// 定义模板变量
$lang = ['name' => '姓名'];

// 模板中lang标签
name: {{lang name}}
// 解析后模板代码为：
name: 姓名

// 模板中lang函数
name: {{lang('name')}}
// 解析后模板代码为：
name: <?php echo lang('name')?>
```


## 3.2 模板逻辑运算标签
### 3.2.1 foreach
```
  {loop $arr $var}
  ...
  {/loop}
  解析后为
  <?php foreach($arr as $var) :?>
  ...
  <?php endforeach; ?>

  {loop $arr $k $v}
  ...
  {/loop}
  解析后为
  <?php foreach($arr as $k => $v) :?>
  ...
  <?php endforeach; ?>
```
### 3.2.2 if
```
  {if $a}

  {elseif $b}

  {else}

  {/if}
```
### 3.2.3 for
```
  {for $a = 0; $a < $x; $a++}

  {/for}
```

4、扩展语法
### 4.1 执行代码段
- 1）{# ... #}

```
单行
{# echo 123 #}

多行
{#
print 'hello';
print 'hi';
#}

可增加HTML注释，代码高亮时便于阅读
<!--{#
print 'hello';
#}-->
```

- 2）可以直接使用PHP标签 <?php code... ?>

### 4.2 static 不解析内容标签
模板中有些部分我们不希望进行解析“编译”，这时我们需要使用static标签。
```
{static}
不解析的内容...
{/static}
```

### 4.3 模板继承
模板继承可以在父模板中定义继承后的模板中可重新定义的区域。

#### 4.3.1 使用模板继承
第一步，使用ext标签定义继承的模板文件，如下
```
{ext 父模板文件}
```

第二步，使用block标签在父模板文件定义可修改的区块，然后在继承的模板中使用同样的区块名修改区块内容。
```
{block 块名}
    块内容
{/block}
```

案例：使用区块定义重写父模板中的区块

父模板
```
<!-- base_tpl.html -->
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Document</title>
</head>
<body>
    {block BL_1}
    继承前的 BL_1
    {/block}
    
    {block BL_2}
    继承前的 BL_2
    {/block}
</body>
</html>
```

子模板extend.html 继承 base_tpl.html
```
<!-- extend.html -->
{ext base_tpl}

{block BL_1}
继承后的 BL_1
{/block}

```
extend.html模板解析后内容为
```
<!-- extend.html -->
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Document</title>
</head>
<body>
    
    继承后的 BL_1
    
    
    
    继承前的 BL_2
    
</body>
</html>
```

#### 4.3.2 保留父模板区块内容的语法
在继承模板的区块中嵌入{parent}标签，该标签将被替换成父区块中的代码。
```
{block BL_1}
{parent}
继承后的 BL_1
{parent}
{/block}
```
将解析为
```
继承前的 BL_1
继承后的 BL_1
继承前的 BL_1
```

### 4.4 模板注释
#### 1） 模板标签注释
可在模板标签两边加上HTML注释，模板解析时自动清掉HTML注释
```
<!--{模板标签}-->
模板解析时将去掉模板标签两边的html注释变成 
{模板标签}
```
#### 服务端注释
使用 {* 注释内容 *} 的格式添加模板注释，模板编译后自动去掉注释内容。
```
{* 单行注释 *}

{*
多行注释
...
*}
<!--{*
结合HTML标签注释
便于阅读
*}-->

```

# 5、单独使用Windwork模板引擎
Windwork控制器中已经初始化好模板引擎，你只需要在配置文件中设置模板参数即可。而当你只使用windwork模板引擎，不使用完整的windwork MVC时，创建模板对象案例如下：

```
// 模板可设置参数
$tplOpt = [
    // 模板目录，相对于入口文件所在目录
    'tplDir'         => 'template/default',
    
    // 渲染模板时，是否检查模板文件是否需要编译
    'compileCheck'   => true,
    
    // 设置模板识别id,用于区分不同国家的语言，不同用户使用不同模板等
    'compileId'      => 'zh_CN',
    
    // 编译后的模板文件保存的文件夹
    'compileDir'    => 'data/template',
    
    // 是否强制每次都编译
    'compileForce'   => false,
    
    // 编译后的模板文件是否合并成一个文件
    'compileMerge'   => true,
    
    // 默认模板文件，建议是"{$mod}/{$ctl}.{$act}.html"
    'defaultTpl' => '',
    
    // 默认备用模板文件，为空或跟默认模板文件一样，则不使用备用模板文件，建议是"{$mod}/{$ctl}.{$act}.html"
    'defaultSpareTpl' => '',
];
$view = new \wf\template\adapter\Wind($tplOpt);

// 变量赋值
$view->assign('myVar', '123456');

// 使用模板 template/default/my/demo.html
$view->render('my/demo.html');

```

# 6、配置参数
通过模板引擎构造函数设置模板配置参数，可配置参数如下：  

 参数 | 示例 |说明 |
 -- | -- | -- 
 tplDir | app/view | 模板目录。
 compileCheck | true | 渲染模板时，是否检查模板文件是否需要编译 
 compileId | zh_CN | 设置模板识别id,用于区分不同国家的语言，不同用户使用不同模板等
 compileDir | data/template | “编译”后的模板文件保存的文件夹
 compileForce | false | 是否强制每次都“编译”，建议开发环境为true，正式环境为false
 compileMerge | true | 编译后的模板文件是否合并成一个文件，建议开发环境为false，正式环境为true
 defaultTpl | {$mod}/{$ctl}/{$act}.html | 默认模板文件，$view->render()不传参时使用的模板
 defaultSpareTpl | '' | 默认备用模板文件，为空或跟默认模板文件一样，则不使用备用模板文件

注：为灵活支持模板文件的查找，模板目录选项可使用{0}、{1}、{2}等匹配规则，运行时``$view->render($file)``的$file参数中的第n个文件夹将替换掉大括号中对应的n数字的配置。
例如：
``` 
// 当启用模块时，tplDir可做如下配置
$tplDir = 'app/{0}/view'; 
$view->render('goods.manage.add.html');
// {0} => goods
// 模板文件为： 'app/goods/view/manage.add.html'; 

// 以下两种参数可支持但很少用
$tplDir = 'app/{0}/view/{1}/demo'; 
$view->render('topic/2017/newyear.html');
// {0} => topic
// {1} => 2017
// 模板文件为： 'app/topic/view/2017/demo/newyear.html'; 


$tplDir = 'app/{1}/view/{0}/demo'; 
$view->render('topic/2017/newyear.html');
// {0} => topic
// {1} => 2017
// 模板文件为： 'app/2017/view/topic/demo/newyear.html'; 
```

# 7、自定义模板引擎
如果你有兴趣，也可以自定义模板引擎，在Windwork中使用。
请implements \wf\template\EngineInterface 接口实现自定义模板。


<br />  
<br />  

### 要了解更多？  
> - [官方完整文档首页](http://docs.windwork.org/manual/)  
> - [官方源码首页](https://github.com/windwork)  
