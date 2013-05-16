<?php
/**
 * 
 * -+-----------------------------------
 * |PHP5 Framework - 2012
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2012-12-23
 * -+-----------------------------------
 * 
 * @desc HTTP请求操作类
 * @author jingke
 */
class XF_Controller_Request_Http extends XF_Controller_Request_Abstract
{
	
	/**
	 * 当前实例
	 * @var XF_Controller_Request_Http
	 */
	private static $_instance = NULL;
	
	/**
	 * 获取当前实例
	 * @return XF_Controller_Request_Http
	 */
	public static function getInstance()
	{
		if (self::$_instance === null)
		{
			self::$_instance = new self();
			if (self::$_instance->isPost() && is_array($_POST))
			{
				foreach ($_POST as $key => $val)
				{
					self::$_instance->setPost($key, $val);
				}
                unset($_POST);
			}
			if (is_array($_FILES))
			{
				foreach ($_FILES as $key => $val)
				{
					self::$_instance->setPost($key, $val, false);
				}
			}
		}
		return self::$_instance;
	}
	
	/**
	 * 设置$_POST请求资料
	 * @access public
	 * @param string|array $spec
	 * @param null|mixed $value
	 * @param bool $filtration 是否过滤内容 默认为true
	 * @return XF_Controller_Request_Http
	 */
	public function setPost($spec, $value = null, $filtration = true)
	{
		if ((null === $value) && is_array($spec)) 
		{
            foreach ($spec as $key => $value) 
            {
                $this->setPost($key, $value);
            }
            return $this;
        }
        $this->_post_params[(string) $spec] = XF_String::clearJs($value);
        $this->setParam((string) $spec, $value, $filtration);
        return $this;
	}
	
	/**
	 * 获取$_POST资料，如果key为空，将返回所有的POST资料
	 * @access public
	 * @param string|null $key
	 * @param null|mixed $default
	 * @return mixed
	 */
	public function getPost($key = null, $default = null)
    {
        if (null === $key) 
        {
            return $this->_post_params;
        }
        return (isset($this->_post_params[$key])) ? $this->_post_params[$key] : $default;
    }
	
    /**
     * 获取$_COOKIE资料，如果key为空，将返回所有的Cookie资料
     * @access public
	 * @param string|null $key
	 * @param null|mixed $default
	 * @return mixed
     */
	public function getCookie($key = null, $default = null)
    {
        if (null === $key) 
        {
            return $_COOKIE;
        }
        return (isset($_COOKIE[$key])) ? $_COOKIE[$key] : $default;
    }
    
    /**
     * 获取$_SERVER资料，如果key为空，将返回所有的SERVER资料
     * @access public
	 * @param string|null $key
	 * @param null|mixed $default
	 * @return mixed
     */
	public function getServer($key = null, $default = null)
    {
        if (null === $key) 
        {
            return $_SERVER;
        }
        return (isset($_SERVER[$key])) ? $_SERVER[$key] : $default;
    }
    
    /**
     * 获取环境变量资料，如果key为空，将返回所有的$_ENV资料
     * @access public
	 * @param string|null $key
	 * @param null|mixed $default
	 * @return mixed
     */
	public function getEnv($key = null, $default = null)
    {
        if (null === $key) 
        {
            return $_ENV;
        }
        return (isset($_ENV[$key])) ? $_ENV[$key] : $default;
    }
	
	/**
	 * 是否POST请求
	 * @access public
	 * @return bool
	 */
	public function isPost()
	{
		if ('POST' == $this->getMethod()) 
		{
			return true;
		}
		return false;
	}
	
	/**
	 * 是否为GET请求
	 * @access public
	 * @return bool
	 */
	public function isGet()
	{
		if ('GET' == $this->getMethod()) 
		{
			return true;
		}
		return false;
	}
	
	/**
	 * 是否为一个请求头
	 * @access public
	 * @return bool
	 */
	public function isHead()
	{
		if ('HEAD' == $this->getMethod()) 
		{
			return true;
		}
		return false;
	}
	
	/**
	 * 是否为一个请求选项
	 * @access public
	 * @return bool
	 */
	public function isOptions()
	{
		if ('OPTIONS' == $this->getMethod()) 
		{
			return true;
		}
		return false;
	}
	
	/**
	 * 是否为Javascript XMLHttpRequest
	 * @access public
	 * @return bool
	 */
	public function isXmlHttpRequest()
    {
		return ($this->getHeader('X_REQUESTED_WITH') == 'XMLHttpRequest');
    }

    /**
	 * 获取当前请求类型
	 * @access public
	 * @return string
	 */
    public function getMethod()
    {
        return $this->getServer('REQUEST_METHOD');
    }
    
    /**
     * 获取客户端最终的IP
     * @access public 
     * @param bool $checkProxy 是否检测代理
     * @return string
     */
	public function getClientIp($checkProxy = true)
    {
        if ($checkProxy && $this->getServer('HTTP_CLIENT_IP') != null) 
        {
            $ip = $this->getServer('HTTP_CLIENT_IP');
        } 
        else if ($checkProxy && $this->getServer('HTTP_X_FORWARDED_FOR') != null) 
        {
            $ip = $this->getServer('HTTP_X_FORWARDED_FOR');
        } 
        else 
        {
            $ip = $this->getServer('REMOTE_ADDR');
        }
        return $ip;
    }
    
	/**
     * 获取指定的HTTP头信息的值
     * @access public
     * @param string $header
     * @return mixed
     */
	public function getHeader($header)
    {
        if (empty($header)) 
        {
            throw new XF_Controller_Request_Exception('An HTTP header name is required');
        }

        //尝试在$_SERVER中获取head
        $temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
        if (isset($_SERVER[$temp])) 
        {
            return $_SERVER[$temp];
        }

        // Apache
        if (function_exists('apache_request_headers')) 
        {
            $headers = apache_request_headers();
            if (isset($headers[$header])) 
            {
                return $headers[$header];
            }
            $header = strtolower($header);
            foreach ($headers as $key => $value) 
            {
                if (strtolower($key) == $header) 
                {
                    return $value;
                }
            }
        }
        return false;
    }
}