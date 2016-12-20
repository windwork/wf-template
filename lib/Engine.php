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
 * @link        http://www.windwork.org/manual/wf.template.html
 * @since       1.0.0
 */
class Engine {
	/**
	 * 是否启用视图分组，把手机、PC版、管理后台模板分别存放在不同的文件夹
	 * @var bool
	 */
	protected $enableViewType = false;
	
	/**
	 * 视图类型，pc）PC版界面视图，包括PC、平板电脑等大屏设备；mobile）智能手机界面视图；admincp）系统管理后台视图
	 * @var string 
	 */
	protected $viewType = 'pc';
	
	/**
	 * PC版界面视图，包括PC、平板电脑等大屏设备
	 * @var string
	 */
	const VIEW_TYPE_PC = 'pc';
	
	/**
	 * 智能手机界面视图
	 * @var string
	 */
	const VIEW_TYPE_MOBILE = 'mobile';
	
	/**
	 * 系统管理后台视图
	 * @var string
	 */
	const VIEW_TYPE_ADMINCP = 'admincp';

	/**
	 * 回调函数设置
	 * 通过回调函数方式注入依赖，
	 * 模板需要设置回调函数来支持获取语言变量值的lang标签和生成网址的url标签
	 * @var array
	 */
	public $callback = [
		'lang' => 'lang',
		'url'  => 'url',
	];
	
	/**
	 * 是否每次都“编译”模板
	 * 
	 * @var bool 默认 true
	 */
    protected $forceCompile = true;
    
    /**
     * “编译”模板时是否合并模板到一个文件中
     * 
     * @var bool 默认 false
     */
    protected $mergeCompile = false;
    
    /**
     * 存放视图的文件夹，相对于当前站点所在目录
     * 
     * @var string 
     */
    protected $tplDir = 'template/default';
    
    /**
     * 模板“编译”后存放目录
     * 
     * @var string
     */
    protected $compiledDir = 'data/template';
    
    /**
     * 默认模板
     * @var string
     */
    protected $defaultTplFile;
    
    /**
     * 模板编译id，用于编译特殊的需要针对不同用户或不同参数唯一的模板界面
     * 
     * @var string 
     */
    protected $compileId = '';
        
    /**
     * 存贮模板中设置及调用的变量
     * 
     * @var array
     */
    protected $vars = array();
    
    /**
     * 是否已渲染过视图
     * @var bool
     */
    protected $isRenderOptFixed = false;
    
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
     * 设置是否启用视图模板文件分类型
     * @param bool $isEnable
     * @return \wf\template\Engine
     */
    public function setEnableViewType($isEnable) {
    	$this->enableViewType = $isEnable;
    	
    	return $this;
    }
    
    /**
     * 设置模板文件类型
     * @param bool $viewType
     * @return \wf\template\Engine
     */
    public function setViewType($viewType) {
    	$this->viewType = $viewType;
    	
    	return $this;
    }

    /**
     * 设置默认模板文件，当reader方法不设置模板文件时使用该文件
     *
     * @param string $file
     * @return \wf\template\Engine
     */
    public function setDefaultTplFile($file) {
    	$this->defaultTplFile = $file;
    	return $this;
    }
    
    /**
     * 设置模板目录，位于站点根目录
     * 
     * @param string $path
     * @return \wf\template\Engine
     */
    public function setTplDir($path) {
        $this->tplDir = trim($path, '/');
        return $this;
    }
    
    /**
     * 设置模板是否强制每次都编译
     * 
     * @param bool $isForceCompile
     * @return \wf\template\Engine
     */
    public function setForceCompile($isForceCompile) {
        $this->forceCompile = $isForceCompile;
        return $this;
    }
      
    /**
     * 设置模板编译时是否将该页面调用的模板合并到同一个文件中
     * 如果启用，能稍微提高性能，但页面子模板修改时程序将不能检测到，修改子模板后需要通过后台清楚模板缓存
     * 建议网站正式上线时启用该功能
     * 
     * @param bool $isMergeCompile
     * @return \wf\template\Engine
     */
    public function setMergeCompile($isMergeCompile) {
        $this->mergeCompile = $isMergeCompile;
        return $this;
    }        
    
    /**
     * 设置编译后的模板引擎的地址
     * 
     * @param string $path
     * @return \wf\template\Engine
     */
    public function setCompiledDir($path) {
        $this->compiledDir = rtrim($path, '/');
        return $this;
    }
    
    /**
     * 设置模板识别id,用于区分不同的语言
     * 
     * @param string $compileId
     * @return \wf\template\Engine
     */
    public function setCompileId($compileId) {
        $this->compileId = $compileId;
         return $this;
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
     * 显示视图
     *
     * @param string $file = "{$mod}/{$ctl}.{$act}.html" 模板文件，模板目录及文件名全部为小写
     */
    public function render($file = '') {    	
    	if (empty($file)) {
    		$file = $this->defaultTplFile;
    	}
    	$file = strtolower($file);
    	
    	$this->fixRenderOpt($file);
        
        extract($this->vars,  EXTR_SKIP);
        
        // 包含文件        
        require $this->getTpl($file);
    }
    
    /**
     * 修正模板参数设置
     * @param string $file
     */
    protected function fixRenderOpt($file) {
    	if ($this->isRenderOptFixed) {
    		return;
    	}
    	
    	$this->isRenderOptFixed = true;
    	
    	$this->tplDir      = trim($this->tplDir, '/');
    	$this->compileId   = trim($this->compileId, '/');
    	$this->compiledDir = trim($this->compiledDir, '/');
    		 
    	// 设置模板子文件夹
    	if (!$this->enableViewType) {
    		return;
    	}
    	
    	if ($this->viewType == 'mobile' && is_file($this->tplDir . '/mobile/' . $file)) {
    	    $this->tplDir     .= '/mobile';
    		$this->compileId  .= '^mobile';
    	} else if ($this->viewType == 'admincp') {
    		$this->tplDir     .= '/admincp';
    		$this->compileId  .= '^admincp';
    	} else {
    		$this->tplDir     .= '/' . $this->viewType;
    		$this->compileId  .= '^' . $this->viewType;
    	}    	
    }
    
    /**
     * 获取模板
     *
     * @param string $file 模板文件名
     * @return string 编译后的模板文件
     */
    protected function getTpl($file) {
        $file = trim(strtolower($file), '/ ');

        $tplFile      = "{$this->tplDir}/{$file}";
        $compiledFile = str_replace("/", '^', $file);
        $compiledFile = "{$this->compiledDir}/{$this->compileId}^{$compiledFile}.php";
      
        // 判断是否强制编译或是否过期($compiledFile不存在时 < 成立)
        if($this->forceCompile || !is_file($compiledFile) || @filemtime($compiledFile) < @filemtime($tplFile)) {
            $this->compile($tplFile, $compiledFile);
        }

        return $compiledFile;
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
        
        // 去掉模板标签HTML注释，去掉<!--{}-->的<!-- -->
        $template = preg_replace("/\\<\\!\\-\\-\\s*?\\{(.+?)\\}\\s*?\\-\\-\\>/s", "{\\1}", $template); 
        
        // {tpl xx} 包含另一个模板
        $template = preg_replace_callback("/\\{tpl\\s+['\\\"]?(.*?)['\\\"]?\\}/is", array($this, 'subTpl'), $template); 

        // <!-- 处理不解析部分
        
        // 允许模板中使用 <?xml ... ？>标签，解决xml声明标签被当做php标识符 
        $template = preg_replace_callback("/(<\\?xml(.*?)\\?>)/is", function($m) {
        	$str = "<?php echo '" . str_replace("'", "\'", $m[1]) . "'?>";
        	return "<![wf-hold-code[" . base64_encode($str) . "]wf-hold-code]>";
        }, $template);

        // {# php-code #} => <?php ... ？>
        $template = preg_replace("/\\{#(.+?)#\\}/s", "<?php \\1?>", $template);
        
        // 将php代码进行编码防止被当做模板标签解析
        $template = preg_replace_callback("/(<\\?.+?\\?>)/is", function($m){
        	return "<![wf-hold-code[".base64_encode($m[1]).']wf-hold-code]>';
        }, $template); 
        
        // {static}不进行解析的内容{/static} => <![static[编码内容]static]>
        $template = preg_replace_callback("/\\{static\\}(.+?)\\{\\/static\\}/s", function($m){
        	return "<![wf-hold-code[" . base64_encode($m[1]). "]wf-hold-code]>";
        }, $template);
        
        // END 处理不解析部分 -->
        
        // 语言标签 {lang key}
        $template = preg_replace_callback("/\\{lang\\s+['\\\"]?(.+?)['\\\"]?\\}/is", function($m) {
        	$langCallback = $this->callback['lang'];
        	return $langCallback(trim($m[1]));
        }, $template);
        
        // {if 表达式}
        $template = preg_replace_callback("/\\{if\\s+(.+?)\\}/is", function($m){ 
        	return static::quote("<?php if({$m[1]}) : ?>");
        }, $template);
        
        // {elseif 表达式}
        $template = preg_replace_callback("/\\{elseif\\s+(.+?)\\}/is", function($m){ 
        	return static::quote("<?php elseif({$m[1]}) : ?>");
        }, $template);
        
        // {else if 表达式}
        $template = preg_replace_callback("/\\{else\\s+if\\s+(.+?)\\}/is", function($m){ 
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
                
        // foreach($a as $v)
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
        
        // foreach($a as $k => $v)
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

        // endforeach
        $template = preg_replace("/\\{\\/loop\\}/", "<?php endforeach; endif; ?>", $template );

        // url标签
        $template = preg_replace("/\\{url\\s+['\"]?(.*?)['\"]?\\}/is", "<?php echo {$this->callback['url']}(\"\\1\");?>", $template);
        
        // 外部变量先进行xss过滤
        $template = preg_replace("/\\{(\\$\\_(GET|POST|REQUEST|COOKIE)\\[.*?\\])\\}/", "{htmlspecialchars(@$1)}", $template);

        // echo 常量
        $template = preg_replace("/\\{([A-Z_][A-Z0-9_]+)\\}/s", "<?php defined('\\1') && print \\1;?>", $template );
        
        // echo 变量/数组/函数/对象属性、方法/类静态属性、方法/类常量
        $template = preg_replace_callback("/\\{([@a-zA-z_\\$\\\\](([@\\\\a-zA-Z0-9_\\[\\]\\-\\>\\(\\)\\'\"\\.\\$])|(\\:{2}))+)\\}/s", function($m){
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
        if ($this->mergeCompile) {
        	$subTpl = "{$this->tplDir}/{$subTpl}.html";
            $content = file_get_contents($subTpl);
            $content = preg_replace("/\\<\\!\\-\\-\\s*?\\{(.+?)\\}\\s*?\\-\\-\\>/s", "{\\1}", $content); // 去掉<!--{}-->的<!-- -->

            $tplDir = $this->tplDir;
            // 多级包含，尽管支持多级包含，但应该少用多级包含
            for ($i = 0; $i < 5; $i++) {
            	$content = preg_replace_callback("/\\{tpl\\s+['\"]?(.*?)['\"]?\\}/is", function($m) use ($tplDir){
            		return file_get_contents("{$tplDir}/{$m[1]}.html");
            	}, $content); // {tpl xx}
            }
            // 去掉<!--{}-->的<!-- -->
            $content = preg_replace("/\\<\\!\\-\\-\\s?\\{(.+?)\\}\\s?\\-\\-\\>/s", "{\\1}", $content);
            return $content;
        } else {
            return "<?php require \$this->getTpl('{$subTpl}.html');?>";
        }
    } 
}

