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
class Wind implements EngineInterface {
    /**
     * 存贮模板中设置及调用的变量
     *
     * @var array
     */
    protected $vars = array();
    
    /**
     * 模板配置
     * @var array
     */
    protected $cfg = [
        // 模板目录，位于站点根目录
        'tplDir'         => 'template/default',
        
        // 渲染模板时，是否检查模板文件是否需要编译
        'compileCheck'   => true,
        
        // 设置模板识别id,用于区分不同国家的语言
        'compileId'      => '',
        
        // 编译后的模板文件保存的文件夹
        'compiledDir'    => 'data/template',
        
        // 设置模板是否强制每次都编译
        'forceCompile'   => false,
        
        // 编译后的模板文件是否合并成一个文件
        'mergeCompile'   => true,
        
        // 默认模板文件，建议是"{$mod}/{$ctl}/{$act}.html"
        'defaultTpl' => '',
        
        // 默认备用模板文件，为空或跟默认模板文件一样，则不使用备用模板文件，建议是"{$mod}/{$ctl}/{$act}.html"
        'defaultSpareTpl' => '',
    ];
    
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
    public function __construct(array $cfg = []) {
        
        // 模板参数设置
        foreach ($this->cfg as $key => $val) {
            if (!isset($cfg[$key])) {
                continue;
            }
            
            $this->cfg[$key] = $cfg[$key];
        }

        $this->cfg['tplDir']      = rtrim($this->cfg['tplDir'], '/');
        $this->cfg['compileId']   = rtrim($this->cfg['compileId'], '/');
        $this->cfg['compiledDir'] = rtrim($this->cfg['compiledDir'], '/');
    }

    /**
     * 未定义的属性赋值给$this->vars
     *
     * @param string $var
     * @param mixed $val
     */
    public function __set($var, $val) {
        $this->vars[$var] = $val;
    }

    /**
     * 访问未定义的属性返回$this->vars[属性名]
     *
     * @param string $var 属性名
     * @return mixed
     */
    public function __get($var) {
        return isset($this->vars[$var])? $this->vars[$var] : null;
    }

    /**
     * 模板变量赋值
     *
     * @param string $k 模板变量下标
     * @param mixed $v 模板变量值
     * @return \wf\template\Engine
     */
    public function assign($k, $v) {
        $this->vars[$k] = $v;
        return $this;
    }

    /**
     * 获取模板变量的值
     *
     * @param string $index
     * @return mixed
     */
    public function getVar($index) {
        return isset($this->vars[$index]) ? $this->vars[$index] : null;
    }

    /**
     * 获取模板所有变量
     *
     * @return array
     */
    public function getVars() {
        return $this->vars;
    }

    /**
     * 
     * @param string $file
     * @param string $spareFile
     */
    public function render($file = '', $spareFile = '') {
        extract($this->vars,  EXTR_SKIP);
        
        if (empty($file)) {
    		$file = $this->cfg['defaultTpl'];
    	}
    	
    	if (empty($spareFile)) {
    		$spareFile = $this->cfg['defaultSpareTpl'];
    	}

        // 包含文件
        require $this->getTpl($file, $spareFile);
    }
    
    /**
     * 获取编译后的模板文件路径
     *
     * @param string $file 模板文件名
     * @param string $spareFile 备选模板文件名
     * @return string 编译后的模板文件路径
     */
    protected function getTpl($file, $spareFile = '') {        
        $file = trim(strtolower($file), '/ ');
        $spareFile = trim(strtolower($spareFile), '/ ');
        
        $compiledFile = $this->getTplCompile($file);
        if ($compiledFile) {
            return $compiledFile;
        }
        
        // 使用备用编译模板
        if ($spareFile && $file != $spareFile) {
            $spareCompiledFile = $this->getTplCompile($spareFile);
            if ($spareCompiledFile) {
                return $spareCompiledFile;
            }
        }
        
        // 模板文件不存在
        throw new Exception("'{$file}' not exists!");
    }
    
    /**
     * 对模板进行编译，然后返回编译后模板文件路径
     * @param string $file
     * @return string 编译后的模板文件路径
     */
    protected function getTplCompile($file) {
        $file = trim(strtolower($file), '/ ');        
        $tplFile = "{$this->cfg['tplDir']}/{$file}";
        
        if (!is_file($tplFile)) {
            return false;
        }
        
        $compiledName = str_replace("/", '^', $file);
        $compiledFile = "{$this->cfg['compiledDir']}/{$this->cfg['compileId']}^{$compiledName}.php";

        // 判断是否强制编译或是否过期($compiledFile不存在时 < 成立)
        if($this->cfg['forceCompile'] || !is_file($compiledFile) || ($this->cfg['compileCheck'] && @filemtime($compiledFile) < @filemtime($tplFile))) {
            $this->compile($tplFile, $compiledFile);
        }

        return $compiledFile;
    }

	/**
	 * 获取模板页中的区块
	 * @param $tplString
	 * @return array
	 */
	protected function getBlock($tplString) {
		$matches = [];
		$rtv = [];
		// {block ... } ... {/block}
		if (preg_match_all("/\\{block\\s+(.*?)\\}(.*?)\\{\\/block\\}/is", $tplString, $matches)) {
			$len = count($matches[0]);
			for ($i = 0; $i < $len; $i++) {
				$rtv[$matches[1][$i]] = $matches[2][$i];
			}
		}
		return $rtv;
	}

    /**
     * 编译模板
     *
     * @param string $tplFile
     * @param string $compiledFile
     * @return bool
     * @throws \wf\template\Exception 如果模板文件不存在则抛出异常
     */
    protected function compile($tplFile, $compiledFile) {
        if(false === ($template = @file_get_contents($tplFile))) {
            throw new Exception("'{$tplFile}' does not exists!");
        }
        
		$match = [];
		// {ext ...}
        if (preg_match("/\\{ext\\s+(.*?)\\}/is", $template, $match)) {
            $masterTpl = @file_get_contents("{$this->cfg['tplDir']}/{$match[1]}.html");
            $masterBlock = $this->getBlock($masterTpl);
            $extBlock = $this->getBlock($template);
            
			// 母版内容替换
            $template = $masterTpl;
			foreach ($masterBlock as $blockName => $masterContent) {
    			if (empty($extBlock[$blockName])) {
    			    continue;
    			}
    			// 提取 {block ... } ... {/block}
    			$template =	preg_replace("/\\{block\\s+{$blockName}\\}(.*?)\\{\\/block\}/is", $extBlock[$blockName], $template);
			}
			
			// 清除继承标签 {ext ...}
			$template = preg_replace("/\\{ext(.*?)\\}/is", '', $template);
			
			// 清除未重写的区块标签名
			$template =	preg_replace("/\\{block.*?\\}(.*?)\\{\\/block\\}/is", "\\1", $template);
		}
		
		// 服务端注释 {* .... *}，清空
        $template = preg_replace("/\\{\\*(.*?)\\*\\}/is", '', $template);

        // 去掉模板标签HTML注释，去掉<!--{}-->的<!-- --> HTML注释
        $template = preg_replace("/\\<\\!\\-\\-\\s*?\\{(.+?)\\}\\s*?\\-\\-\\>/s", "{\\1}", $template);

        // {tpl xx} 包含另一个模板
        $template = preg_replace_callback("/\\{tpl\\s+['\\\"]?(.*?)['\\\"]?\\}/is", array($this, 'subTpl'), $template);

        
        // START 处理不解析部分  ======================

        // 允许模板中使用 <?xml ... ？>标签，解决xml声明标签被当做php标识符
        $template = preg_replace_callback("/(<\\?xml(.*?)\\?>)/is", function($m) {
        	$str = "<?php echo '" . str_replace("'", "\'", $m[1]) . "'?>";
        	return "<![wf-hold-code[" . base64_encode($str) . "]wf-hold-code]>";
        }, $template);

        /* {# php-code #} => <?php ... ?> */
        $template = preg_replace("/\\{#(.+?)#\\}/s", "<?php \\1?>", $template);

        // 将php代码进行编码防止被当做模板标签解析
        $template = preg_replace_callback("/(<\\?.+?\\?>)/is", function($m){
        	return "<![wf-hold-code[" . base64_encode($m[1]) . ']wf-hold-code]>';
        }, $template);

        // {static}不进行解析的内容{/static} => <![wf-hold-code[编码内容]wf-hold-code]>
        $template = preg_replace_callback("/\\{static\\}(.+?)\\{\\/static\\}/s", function($m){
        	return "<![wf-hold-code[" . base64_encode($m[1]). "]wf-hold-code]>";
        }, $template);

        // END 处理不解析部分  ======================
        
        // url 
        $template = preg_replace("/\\{\\{url\\s+['\"]?(.*?)['\"]?\\}\\}/is", "<?php echo url(\"\\1\");?>", $template);
        
        // lang 语言标签 {{lang key}}
        $template = preg_replace_callback("/\\{\\{lang\\s+['\\\"]?(.+?)['\\\"]?\\}\\}/is", function($m) {
        	return lang(trim($m[1]));
        }, $template);

        // {if 表达式}
        $template = preg_replace_callback("/\\{if\\s+(.+?)\\}/is", function($m){
        	return static::quote("<?php if({$m[1]}) : ?>");
        }, $template);

        // {elseif 表达式} {else if 表达式}
        $template = preg_replace_callback("/\\{else\\s*if\\s+(.+?)\\}/is", function($m){
        	return static::quote("<?php elseif({$m[1]}) : ?>");
        }, $template);

        // {else}
        $template = preg_replace("/\\{else\\}/is", "<?php else : ?>", $template);

        // {/if} => endif
        $template = preg_replace("/\\{\\/if\\}/is", "<?php endif; ?>", $template);

        // {for 表达式1; 表达式2; 表达式3}
        $template = preg_replace_callback("/\\{for\\s+(.*?)\\}/is", function($m){
        	return static::quote("<?php for({$m[1]}) :?>");
        }, $template);

        // {/for} => endfor
        $template = preg_replace("/\\{\\/for\\}/is", "<?php endfor; ?>", $template);

        // {loop $arr $v} => foreach($arr as $v)
        if(preg_match_all("/\\{loop\\s+?(\\S+?)\\s+?(\\S+?)\\}/s", $template, $matches)) {
        	$search = array();
        	$replace = array();
        	foreach ($matches[0] as $k => $mat) {
        		$search[$k] = $mat;
        		$replaceStr = "<?php
        		  \$__loop__tmp__{$k} = @{$matches[1][$k]};
        		  if(!empty(\$__loop__tmp__{$k}) && !is_scalar(\$__loop__tmp__{$k})):
        		  foreach(\$__loop__tmp__{$k} as {$matches[2][$k]}) :
        		?>";
        		$replace[$k] = static::quote($replaceStr);
        	}
        	$template = str_replace($search, $replace, $template);
        }

        // {loop $arr $k $v} => foreach($arr as $k => $v)
        if(preg_match_all("/\\{loop\\s+?(\\S+?)\\s+?(\\S+)?\\s+?(\\S+?)\\}/s", $template, $matches)) {
        	$search = array();
        	$replace = array();
        	foreach ($matches[0] as $k => $mat) {
        		$search[$k] = $mat;
        		$replaceStr = "<?php
        		  \$__loop__tmp__x_{$k} = @{$matches[1][$k]};
        		  if(!empty(\$__loop__tmp__x_{$k}) && !is_scalar(\$__loop__tmp__x_{$k})):
        		  foreach(\$__loop__tmp__x_{$k} as {$matches[2][$k]} => {$matches[3][$k]}) :
        		?>";
        		$replace[$k] = static::quote($replaceStr);
        	}
        	$template = str_replace($search, $replace, $template);
        }

        // {/loop} => endforeach
        $template = preg_replace("/\\{\\/loop\\}/", "<?php endforeach; endif; ?>", $template );

        // 外部变量先进行xss过滤
        $template = preg_replace("/\\{\\{(\\$\\_(GET|POST|REQUEST|COOKIE)\\[.*?\\])\\}\\}/", "{{htmlspecialchars(@$1)}}", $template);

        // echo 常量 {{CONST}}
        $template = preg_replace("/\\{\\{([A-Z_][A-Z0-9_]+)\\}\\}/", "<?php defined('\\1') && print \\1;?>", $template );

        // echo 变量/数组/函数/对象属性、方法/类静态属性、方法/类常量
        /* {{xxxx}} => <?php echo xxxx ?> */
        $template = preg_replace_callback("/\\{\\{([@a-z_\\$\\\\].+?)\\}\\}/i", function($m){
        	return static::quote("<?php echo @{$m[1]};?>");
        }, $template);

        // 还原不解析的内容<![wf-hold-code[编码内容]wf-hold-code]>
        $template = preg_replace_callback("/<\\!\\[wf-hold-code\\[(.+?)\\]wf-hold-code\\]>/s", function($m){
        	return str_replace("\\\"", "\"", base64_decode($m[1]));
        }, $template);

        // 添加在模板顶部的文件说明信息
        $thisTplMsg = "<?php\n/**\n"
                    . " * Windwork Template View (Don't edit this file)\n"
                    . " *\n"
                    . " * File: {$compiledFile}\n"
                    . " * From: {$tplFile}\n"
                    . " * Time: ". microtime(1) . "\n"
                    . " * Make: by Windwork template engine at "  . date('Y-m-d H:i:s') . "\n"
                    . " */\n"
                    . "defined('IS_IN') || die('Access Denied');\n"
                    . "?>";


        // 保存“编译”后模板文件
        @file_put_contents($compiledFile, $thisTplMsg . $template);

        return true;
    }

    /**
     * 转义双引号
     *
     * @param string $var
     * @return string
     */
    protected static function quote($var) {
        return str_replace ("\\\"", "\"", preg_replace("/\\[([a-zA-Z0-9_\\-\\.\\x7f-\\xff]+)\\]/s", "['\\1']", $var));
    }

    /**
     * 解析子模板标签
     *
     * @param array $m
     * @return string
     */
    protected function subTpl($m) {
    	$subTpl = $m[1]; // 匹配的子模板
        if ($this->cfg['mergeCompile']) {
        	$subTpl = "{$this->cfg['tplDir']}/{$subTpl}.html";
            $content = file_get_contents($subTpl);
            $content = preg_replace("/\\<\\!\\-\\-\\s*?\\{(.+?)\\}\\s*?\\-\\-\\>/s", "{\\1}", $content); // 去掉<!--{}-->的<!-- -->

            $tplDir = $this->cfg['tplDir'];
            // 多级嵌套包含，尽管支持多级包含，但应该少用多级包含
            for ($i = 0; $i < 6; $i++) {
            	$content = preg_replace_callback("/\\{tpl\\s+['\"]?(.*?)['\"]?\\}/", function($m) use ($tplDir){
            		return file_get_contents("{$tplDir}/{$m[1]}.html");
            	}, $content); // {tpl xx}
            }
            
            // 去掉<!--{}-->的<!-- --> HTML注释
            $content = preg_replace("/\\<\\!\\-\\-\\s*?\\{(.+?)\\}\\s*?\\-\\-\\>/s", "{\\1}", $content);
            
            return $content;
        } else {
            return "<?php require \$this->getTpl('{$subTpl}.html');?>";
        }
    }
}
