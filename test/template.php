<?php


define('IS_IN', 1);

require_once __DIR__ . '/../lib/Engine.php';
require_once __DIR__ . '/../lib/Exception.php';


use \wf\template\Engine;

define('TEST_CONST', 'test-const');

class A {
	public $arg = 112358;
	public static $sta_2 = 2;

	public $arr = ['aa', 'bb', 'cc'];
	public static $sArr = ['aa', 'bb', 'cc'];
	
	public function fnc() {
		return 'run A::fnc()';
	}

	const CON = 'A.const';
	const CON_1 = 1;
	
	public static $sta = 'static.attribute';
	
	public static function staFnc() {
		return 'static.function';
	}
}

function url($uri) {
	return $uri;
}

function lang($key) {
	$lang = [
		'name' => '姓名',
		'phone' => '手机号码',
	];
	return @$lang[$key];
}

class B {
	public $a;

	public function __construct(A $a) {
		$this->a = $a;
	}	
}

function fnc($obj) {
	return $obj;
}


// pc
$template = new Engine();

$template
// 设置模板配置参数
->setEnableViewType(true)
->setForceCompile(1)
->setMergeCompile(0)
->setCompiledDir(__DIR__ . '/compiled')
->setCompileId('test-pc')
->setTplDir(__DIR__ . '/tpl')
->render('test-pc.html');

// m
$template = new Engine();

$template
// 设置模板配置参数
->setEnableViewType(true)
->setViewType('m')
->setForceCompile(1)
->setMergeCompile(0)
->setCompiledDir(__DIR__ . '/compiled')
->setCompileId('test-m')
->setTplDir(__DIR__ . '/tpl');

$template->render('test-m.html');

// disable ViewType
$template = new Engine();

$b = new B(new A());

$template
->setEnableViewType(false)
// 设置模板配置参数
->setForceCompile(1)
->setMergeCompile(1)
->setCompiledDir(__DIR__ . '/compiled')
->setCompileId('test-x-merge')
->setTplDir(__DIR__ . '/tpl')
->assign('title', '测试')
->assign('b', $b)
->assign('phone', 'phone')
->assign('arr', ['a' => ['b' => 'bbbb'], 'a2', 'b2', 'c2']);

$template->render('test.html');

$template = new Engine();

$template
// 设置模板配置参数
->setEnableViewType(false)
->setForceCompile(1)
->setCompiledDir(__DIR__ . '/compiled')
->setTplDir(__DIR__ . '/tpl')
->setMergeCompile(0)
->setCompileId('test-no-merge');
$template->render('test-no-merge.html');
