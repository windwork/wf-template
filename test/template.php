<?php


define('WF_IN', 1);

require_once __DIR__ . '/../lib/EngineInterface.php';
require_once __DIR__ . '/../lib/strategy/Wind.php';
require_once __DIR__ . '/../lib/Exception.php';


use \wf\template\strategy\Wind;

define('TEST_CONST', 'test-const');

class A 
{
    public $arg = 112358;
    public static $sta_2 = 2;

    public $arr = ['aa', 'bb', 'cc'];
    public static $sArr = ['aa', 'bb', 'cc'];
    
    public function fnc() 
    {
        return 'run A::fnc()';
    }

    const CON = 'A.const';
    const CON_1 = 1;
    
    public static $sta = 'static.attribute';
    
    public static function staFnc() 
    {
        return 'static.function';
    }
}

function url($uri) 
{
    return $uri;
}

function lang($key) 
{
    $lang = [
        'name' => '姓名',
        'phone' => '手机号码',
    ];
    return @$lang[$key];
}

class B 
{
    public $a;

    public function __construct(A $a) 
    {
        $this->a = $a;
    }    
}

function fnc($obj) 
{
    return $obj;
}

$cfg = [
    // 模板目录，位于站点根目录
    'tplDir'         => __DIR__ . '/tpl',
    
    // 渲染模板时，是否检查模板文件是否需要编译
    'compileCheck'   => true,
    
    // 设置模板识别id,用于区分不同国家的语言
    'compileId'      => 'test-01-std',
    
    // 编译后的模板文件保存的文件夹
    'compileDir'    => __DIR__ . '/compiled',
    
    // 设置模板是否强制每次都编译
    'compileForce'   => true,
    
    // 编译后的模板文件是否合并成一个文件
    'compileMerge'   => true,
    
    // 默认模板文件，建议是"{$mod}/{$ctl}/{$act}.html"
    'defaultTpl' => 'test.html',
    
    // 默认备用模板文件，为空或跟默认模板文件一样，则不使用备用模板文件，建议是"{$mod}/{$ctl}/{$act}.html"
    'defaultSpareTpl' => '',
];


// test 01
$template = new Wind($cfg);

$b = new B(new A());
$template
->assign('title', '测试')
->assign('b', $b)
->assign('phone', 'phone')
->assign('arr', ['a' => ['b' => 'bbbb'], 'a2', 'b2', 'c2']);

$template->render();

// ====================
// test 02
// 编译不合并
$cfg['compileMerge'] = false;
$cfg['compileId'] = 'test-02-no-merge';

$template = new Wind($cfg);

$b = new B(new A());
$template
->assign('title', '测试')
->assign('b', $b)
->assign('phone', 'phone')
->assign('arr', ['a' => ['b' => 'bbbb'], 'a2', 'b2', 'c2']);

$template->render();

// =====================
// test 03
// 备用模板
// 通过render方法参数使用备用模板
$cfg['compileId'] = 'test-03-spare-param';
$template = new Wind($cfg);
$template->render('xxx_no_file.html', 'm/test-m.html');


// test 04
// 通过构造函数传参使用备用模板
$cfg['defaultTpl'] = 'xxx_no_file.html';
$cfg['defaultSpareTpl'] = 'pc/test-pc.html';
$cfg['compileId'] = 'test-04-spare-construct';

$template = new Wind($cfg);
$template->render();

// test 05
// 备用模板不存在抛出异常
try {
    $template = new Wind($cfg);
    $template->render('xxx_no_file1.html', 'xxx_no_file2.html');
} catch (\Exception $e) {
    print "\n=====================\n";
    print $e->getMessage();
    print "\n=====================\n";
}


// test 06
$cfg['compileId'] = 'test-06-extends';
$template = new Wind($cfg);
$template->render('extend_a.html');
