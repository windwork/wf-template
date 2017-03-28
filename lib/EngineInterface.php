<?php
/**
 * Windwork
 *
 * 一个开源的PHP轻量级高效Web开发框架
 *
 * @copyright   Copyright (c) 2008-2016 Windwork Team. (http://www.windwork.org)
 * @license     http://opensource.org/licenses/MIT	MIT License
 */
namespace wf\template;

/**
 * 模板视图引擎
 * 模板引擎将模板“编译”成php脚本，每次调用视图的时候将包含 “编译”后的php脚本
 *
 * @package     wf.template
 * @author      erzh <cmpan@qq.com>
 * @link        http://docs.windwork.org/manual/wf.template.html
 * @since       1.0.0
 */
interface EngineInterface {
    /**
     * 未定义的属性赋值给模板变量
     *
     * @param string $var
     * @param mixed $val
     */
    public function __set($var, $val);

    /**
     * 访问未定义的属性返回模板变量值
     *
     * @param string $var 属性名
     * @return mixed
     */
    public function __get($var);

    /**
     *
     * @param array $cfg = [<pre>
     *     // 模板目录，相对于入口文件所在目录
     *     'tplDir'         => 'template/default',
     *
     *     // 渲染模板时，是否检查模板文件是否需要编译
     *     'compileCheck'   => true,
     *
     *     // 设置模板识别id,用于区分不同国家的语言
     *     'compileId'      => '',
     *
     *     // 编译后的模板文件保存的文件夹
     *     'compiledDir'    => 'data/template',
     *
     *     // 设置模板是否强制每次都编译
     *     'forceCompile'   => false,
     *
     *     // 编译后的模板文件是否合并成一个文件
     *     // 如果启用，能稍微提高性能，但页面子模板修改时程序将不能检测到，修改子模板后需要通过后台清楚模板缓存
     *     // 建议工业环境启用、开发环境停用
     *     'mergeCompile'   => true,
     *
     *     // 默认模板文件，建议是"{$mod}/{$ctl}/{$act}.html"
     *     'defaultTpl' => '',
     *
     *     // 默认备用模板文件，为空或跟默认模板文件一样，则不使用备用模板文件，建议是"{$mod}/{$ctl}/{$act}.html"
     *     'defaultSpareTpl' => '',
     * <pre>];
     */
    public function __construct(array $cfg = []);

    /**
     * 模板变量赋值
     *
     * @param string $k 模板变量下标
     * @param mixed $v 模板变量值
     * @return \wf\template\Engine
     */
    public function assign($k, $v);

    /**
     * 显示视图
     *
     * @param string $file = '' 模板文件，如果为空则使用"{$mod}/{$ctl}/{$act}.html"，模板目录及文件名全部为小写
     * @param string $spareFile = '' 备用模板文件，如果第一个参数传入的模板文件不存在则使用，如果为空则使用"{$mod}/{$ctl}/{$act}.html"，模板目录及文件名全部为小写
     */
    public function render($file = '', $spareFile = '');

}
