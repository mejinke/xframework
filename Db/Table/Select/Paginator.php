<?php
/**
 * 
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2011-09-24
 * -+-----------------------------------
 *
 * @desc 表查询分页类
 * @author jingke
 */
class XF_Db_Table_Select_Paginator
{
	/**
	 * 当前实例
	 * @var XF_Db_Table_Select_Paginator
	 */
	private static $_instance;
	
	/**
	 * Request
	 * @var XF_Controller_Request_Abstract
	 */
	protected $_request;
	
	/**
	 * 每页显示数
	 * @var int
	 */
	protected $_page_size;

	/**
	 * 总页数
	 * @var int
	 */
	protected $_page_count;

	/**
	 * 数据总数
	 * @var int
	 */
	protected $_data_count;

	/**
	 * 当前页码
	 * @var int
	 */
	protected $_page = 1;
	
	/**
	 * 显示的连接数量
	 * @var int
	 */
	protected $_now_bar_count = 6;

	/**
	 * 分类器分页参数
	 * @var int
	 */
	protected $_paginator_param = 'page';

	/**
	 * 上一页说明文本
	 * @var string
	 */
	protected $_previous = '上一页';
	
	/**
	 * 下一页说明文本
	 * @var string
	 */
	protected $_next = '下一页';
	
	/**
	 * 第一页说明文本
	 * @var string
	 */
	protected $_first_page = '首 页';
	
	/**
	 * 最后一页说明文本
	 * @var string
	 */
	protected $_last_page = '最后一页';
	
	/**
	 * 自定义参数列表
	 * @var array
	 */
	protected $_custom_params = array();
	
	/**
	 * 最大分页数
	 * @var int
	 */
	protected $_max_page_size;
	
	/** 
	 * 自定义分页的URL
	 * @var string
	 */
	private $_custom_url = '';
	
	/**
	 * 自定义第一页的URL
	 * @var string
	 */
	private $_first_page_url = '';
	

	/**
	 * 当前完整请求的URL，不带分页参数
	 * @var string
	 */
	protected $_no_page_current_url = '';
	
	/**
	 * 生成使用传统带问号方式的连接
	 * @var bool
	 */
	private $_is_default_link = FALSE;
	
	/**
	 * 当前参数列表
	 * @var array
	 */
	protected $_params;
	
	private $args_index=null;
	
	private $style = '';

	private function __construct(){}
	private function __clone(){}
	
	/**
	 * 获取当前实例
	 * @return XF_Db_Table_Select_Paginator
	 */
	public static function getInstance()
	{
		if (self::$_instance === null)
			self::$_instance = new self();
		return self::$_instance;
	}
	
	/**
	 * 设置必要信息
	 * @param int $pageSize 每页显示数
	 * @param int $dataCount　数据总数
	 * @return XF_Db_Table_Select_Paginator
	 */
	public function set($pageSize, $dataCount)
	{
		if ($dataCount == 0 )
			return false;
		
		$this->_page_size = $pageSize;
		$this->_data_count = $dataCount;
		
		//获取总页数
		$this->_page_count = ceil($this->_data_count / $this->_page_size);
		return $this;
	}
	
	/**
	 * 设置分页的URL
	 * @param string $customUrl
	 * @return XF_Db_Table_Select_Paginator
	 */
	public function setCustomUrl($customUrl = '')
	{
		$this->_custom_url = $customUrl;
		return $this;
	}
	
	/**
	 * 设置第一页的URL
	 * @param string $url
	 * @return XF_Db_Table_Select_Paginator
	 */
	public function setFirstUrl($url)
	{
		$this->_first_page_url = $url;
		return $this;
	}
	
	/**
	 * 设置允许的最大页数
	 * @param int $size
	 * @return XF_Db_Table_Select_Paginator
	 */
	public function setMaxPageSize($size)
	{
		$this->_max_page_size = $size;
		return $this;
	}
	
	/**
	 * 设置为使用传统的连接方式 
	 * @access public
	 * @param bool $status
	 * @return XF_Db_Table_Select_Paginator
	 */
	public function setDefaultLink($status = TRUE)
	{
		$this->_is_default_link = $status;
		return $this;
	}
	
	/**
	 * 执行分页
	 */
	public function run()
	{
		$this->_request = XF_Controller_Request_Http::getInstance();
		$this->_params = $this->_request->getCustomParams(false);
		$page = $this->_request->getParam($this->_paginator_param, FALSE) ? intval($this->_request->getParam($this->_paginator_param)) : 1;
		$this->_page = $page;
		
		//重置最大页数
		if (is_numeric($this->_max_page_size) && $this->_max_page_size > 0 && $this->_page_count > $this->_max_page_size)
		{
			$this->_page_count = $this->_max_page_size;
		}
		
		$this->_getDiyResultByArray();
		$this->nowbar();
		
		//重置相关设置
		$this->setCustomUrl();
		$this->setPaginatorParamName();
		$this->setNowBarCount();
	}
	
	/**
	 * 设置分页显示的连接数量
	 * @param int $count
	 * @return XF_Db_Table_Select_Paginator
	 */
	public function setNowBarCount($count = 6)
	{
		$this->_now_bar_count = (int)$count;
		return $this;
	}
	
	/**
	 * 设置分页参数名称
	 * @param string $name
	 * @return XF_Db_Table_Select_Paginator
	 */
	public function setPaginatorParamName($name = 'page')
	{
		$name = (string) $name;
		$this->_paginator_param = $name;
		return $this;
	}
	
	/**
	 * 获取分页参数名称
	 * @return string
	 */
	public function getPaginatorParamName()
	{
		return $this->_paginator_param;
	}
	
	/**
	 * 重置参数
	 * @param array $params
	 * @return XF_Db_Table_Select_Paginator
	 */
	public function setParams(Array $params)
	{
		$this->_params = $params;
		return $this;
	}

	/**
	 * 获取下一页
	 * @return string
	 */
	public function _getNext()
	{
		if ($this->_page < $this->_page_count)
			return $this->_getLink($this->_page+1,$this->_next);
		else
			return $this->_span($this->_next);
	}

	/**
	 * 获取上一页
	 * @return string
	 */
	private function _getPrevious()
	{
		if ($this->_page > 1)
			return $this->_getLink($this->_page-1,$this->_previous);
		else
			return $this->_span($this->_previous);
	}

	/**
	 * 获取第一页
	 * @return string
	 */
	private function _getFirst()
	{
		if ($this->_page == 1)
		{
			return $this->_span($this->_first_page);
		}
		else
		{
			return $this->_getLink($this->_first_page_url == '' ? 1 : $this->_first_page_url, $this->_first_page);
		}
	}

	/**
	 * 获取最后一页
	 * @return string
	 */
	private function _getLast()
	{
		if ($this->_page == $this->_page_count || $this->_page_count == 0)
			return $this->_span($this->_last_page);
		else
			return $this->_getLink($this->_page_count,$this->_last_page);
	}

	/**
	 * 显示分页
	 * @return string
	 */
	public function show()
	{
		return '共有'.$this->_data_count.'条记录 '.$this->_getFirst().$this->_getPrevious().$this->_getNext().$this->_getLast();
	}

	/**
	 * 显示分页：下拉列表
	 * @return string
	 */
	public function select()
	{
		$str = '<select onchange="window.location.href=this.value">';
		for ($i = 1; $i <= $this->_page_count; $i++)
		{
			if ($this->_page == $i)
				$str.='<option value="'.$this->_getUrl($i).'" selected>'.$i.'</option>';
			else
				$str.='<option value="'.$this->_getUrl($i).'">'.$i.'</option>';
		}
		$str.='</select>';
		return $str;
	}

    /**
	 * 类似百度翻页
	 * @access public
	 */
	public function nowbar()
	{
		$plus = ceil($this->_now_bar_count/2);
		if($this->_now_bar_count - $plus+$this->_page > $this->_page_count)
			$plus = $this->_now_bar_count - $this->_page_count+$this->_page;
			
		$begin = $this->_page-$plus+1;
		$begin = ($begin >= 1) ? $begin : 1;
		
		$return='';
		$params = $this->_params;
		for($i=$begin; $i<$begin+$this->_now_bar_count; $i++)
		{
			if($i <= $this->_page_count)
			{
				if($i != $this->_page)
				{
					$return .= $this->_getLink($i, $i);
				}
				else
					$return .=' <span>'.$i.'</span> ';
			}
			else
				break;
		   $return .="\n";
		  }

		 unset($begin);
		 $str = $this->_getPrevious().'&nbsp;'.$return.'&nbsp;'.$this->_getNext();
		 isset($params[$this->_paginator_param]) ? $params[$this->_paginator_param]++ : '';
		 $this->_params = $params;
		 XF_View::getInstance()->assign($this->_paginator_param.'_Paginator_Html', $str);
		 return $str;
	 }
	 
	 /**
	  * 获取分页所必要的资料，用户可以根据该资料自定议的分页样式
	  * @access private
	  */
	private function _getDiyResultByArray()
	{
		 	
		$plus = ceil($this->_now_bar_count / 2);
		if( $this->_now_bar_count - $plus + $this->_page > $this->_page_count)
			$plus = $this->_now_bar_count - $this->_page_count + $this->_page;
			
		$begin = $this->_page - $plus + 1;
		$begin = ($begin >= 1) ? $begin : 1;
		$return = array();
		$return['no_page_current_url'] = $this->_getUrl();
		$return['html']['first'] = $this->_getUrl(1,1);
		$return['data']['first'] = 1;
		$return['html']['last'] = $this->_getUrl($this->_page_count, $this->_page_count);
		$return['data']['last'] = $this->_page_count;
			
		$params = $this->_params;
			
		$return['html']['nonce'] = $this->_page;
		$return['data']['nonce'] = $this->_page;
			
		//上一页
		$ps = $this->_page ==1 ? 1 : $this->_page-1;
		$return['html']['previous'] = $this->_getUrl($ps,$ps);
		$return['data']['previous'] = $ps;
			
		//下一页
		$ps = $this->_page == $this->_page_count ? $this->_page_count : $this->_page+1;
		$return['html']['next'] = $this->_getUrl($ps,$ps);
		$return['data']['next'] = $ps;
		$return['url'] = $this->_custom_url;
			
		//总页
		$return['data_count'] = $this->_data_count;
		$return['page_count'] = $this->_page_count;
		for($i=$begin; $i<$begin+$this->_now_bar_count; $i++)
		{
			if($i <= $this->_page_count)
			{
				$return['html']['item'][] = array('html'=>$this->_getUrl($i,$i),'number'=>$i);
				$return['data']['item'][] = $i;
			}
			else
				break;
		}
		//生成额外的参数连接
		$string='';
		$values = array_values($params);
		$keys = array_keys($params);
		for ($i=0; $i<count($params); $i++)
		{
			if ($keys[$i] != $this->_paginator_param)
				$string.='/'.$keys[$i].'='.$values[$i];
		}
		
		$return['args_string'] = $string;
		$return['args'] = $params;
		$this->_custom_params = $return; 
		XF_View::getInstance()->assign($this->_paginator_param.'_Paginator_Data', $return);
		return $return;
	}

	/**
	 * 获取链接地址
	 * @param int $page 页码
	 * @param string $text 显示名称
	 */
	private function _getLink($page, $text)
	{
		return ' <a href="'.$this->_getUrl($page).'">'.$text.'</a> ';
	}

 	private function _span($text)
	{
		return  ' <span>'.$text.'</span>';
	}

	
	 private function _getUrl($page = null)
	 {
	 	if ($page != null)
			$this->_params[$this->_paginator_param] = $page;
		else 
			unset($this->_params[$this->_paginator_param]);
			
	 	if ($page == 1 && $this->_first_page_url != '')
		{
			return $this->_first_page_url;
		}
		
		$url = $this->_custom_url;
		if ($this->_custom_url == null)
		{
			$module = strtolower($this->_request->getModule()) == 'default' ? '' : '/'.$this->_request->getModule();
			$url = $module.'/'.$this->_request->getController().'/'.$this->_request->getAction();
			
			if ($this->_is_default_link === TRUE)
			{
				$i = 0;
				foreach ($this->_params as $key => $val)
				{
					$i++;
					if ($i == 1)
						$url.= '/?'.$key.'='. urlencode($val);
					else
						$url.= '&'.$key.'='. urlencode($val);
				}
			}
			else
			{
				foreach ($this->_params as $key => $val)
				{
					$url.= '/'.$key.'/'. urlencode($val);
				}
			}
		}
		else 
		{
			$url = str_replace('{page}', $page, $url);
		}
	 	return $url;
	 }
}