<?php
/**
 * 
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2013-08-30
 * -+-----------------------------------
 *
 * @desc 访问控制列表
 * @author jingke
 */
class XF_Acl
{
	
	/**
	 * 角色适配器
	 * @var XF_Acl_Role_Interface
	 */
	private $_role_adapter;
	
	/**
	 * 控制列表存储器
	 * @var XF_Acl_Storage_Interface
	 */
	private $_storage;
	
	/**
	 * 当前用户的Acl
	 * @var array
	 */
	private $_acl;
	
	/** 
	 * 忽略列表(不受Acl控制)
	 * @var array
	 */
	private $_ignore;
	
	/**
	 * 白名单列表
	 * @var array
	 */
	private $_allows;
	
	/**
	 * 可控制列表
	 * @var array
	 */
	private $_list;
	
	public function __construct()
	{
		$this->_storage = new XF_Acl_Storage_Session();	
		if ($this->_storage->isEmpty() == false) 
		{
			$this->_acl = $this->_storage->read();
		}
		$this->loadConfig();
	}
	
	/**
	 * 添加配置文件
	 */
	private function loadConfig()
	{
		$file = APPLICATION_PATH.'/configs/acl.php';
		if (!is_file($file)) 
			return;
		$config = require $file;
		$allows = isset($config['allow']) && is_array($config['allow']) ? $config['allow'] : null;
		$ignore = isset($config['ignore']) && is_array($config['ignore']) ? $config['ignore'] : null;
		$this->_list = isset($config['list']) && is_array($config['list']) ? $config['list'] : null;
		
		if (is_array($allows)) 
		{
			foreach ($allows as $r)
			{
				$this->_allows[] = md5(strtolower($r));
			}
		}
		
		if (is_array($ignore)) 
		{
			foreach ($ignore as $r)
			{
				$this->_ignore[] = md5(strtolower($r));
			}
		}
	}
	
	/**
	 * 获存储器
	 * @return XF_Acl_Storage_Session
	 */
	public function getStorage()
	{
		return $this->_storage;
	}
	
	/**
	 * 设置角色适配器
	 * @access public
	 * @param XF_Acl_Role_Interface $adapter
	 * @return XF_Acl
	 */
	public function setRoleAdapter(XF_Acl_Role_Interface $adapter)
	{
		$this->_role_adapter = $adapter;
		return $this;
	}
	
	/**
	 * 设置白名单
	 * @accesspublic
	 * @param array $list
	 * @return XF_Acl
	 */
	public function setAllows(Array $list)
	{
		foreach ($list as $l)
		{
			$this->_allows[] = md5($l);
		}
		return $this;
	}
	
	/**
	 * 设置当前用户为指定的角色
	 * @access public
	 * @param int $role_id
	 * @return void
	 */
	public function setLoginRole($role_id)
	{
		if ($this->_role_adapter == null)
			throw new XF_Acl_Exception('角色适配器为空');
			
		$this->_role_adapter->load($role_id);
		if ($this->_role_adapter->hasRole())
		{
			$data = array(
				'role_id' => $this->_role_adapter->getRoleId(),
				'name' => $this->_role_adapter->getName(),
				'acls' => $this->_role_adapter->getAcls()
			);
			$this->_storage->write($data);
		}
	}
	
	/**
	 * 验证访问权限，无权限时将会抛出异常
	 * @access public
	 * @param string $acl 例:default.index.index
	 * @throws XF_Acl_Exception
	 * @return void
	 */
	public function validate($acl = NULL)
	{

		$request = XF_Controller_Request_Http::getInstance();
		$module = $request->getModule();
		$controller = $request->getController();
		$action = $request->getAction();
		
		if ($acl != null)
		{
			$tmp = explode('.', $acl);
			$module = $tmp[0];
			$controller = $tmp[1];
			$action = $tmp[2];
		}
		
		//当前请求的Acl
		$nowAcl = $module.'.'.$controller.'.'.$action;
		$acl = $acl == NULL ? $nowAcl : $acl;
		
		$acl = md5(strtolower(trim($acl)));
		//是否在忽略列表中
		if (is_array($this->_ignore) && in_array($acl, $this->_ignore))
			return true;
			
		if (!is_array($this->_acl))
			throw new XF_Acl_Exception('无效的访问，您当前权限不足');
			
		if ($this->_acl['acls'][0] == '*') return true;
		
		//是否拥有该action权限
		if (in_array($acl, $this->_acl['acls']))
			return true;
	
		//是否拥有该控制器所有的权限
		$controller_tmp = strtolower($controller);
		if (strtolower($module) != 'default')
			$controller_tmp = strtolower($module.'_'.$controller);

		if (in_array(md5($controller_tmp), $this->_acl['acls']))
			return true;

		//是否在白名单中
		if (is_array($this->_allows) && in_array($acl, $this->_allows))
			return true;

		throw new XF_Acl_Exception('无效的访问，您当前权限不足');
	}
	
	/**
	 * 验证访问权限
	 * @access public
	 * @param string $acl 例:default.index.index
	 * @return bool 如果有权限则返回true 否则false
	 */
	public function vali($acl = NULL)
	{
		try{
			$this->validate($acl);
			return true;
		}catch (XF_Acl_Exception $e){
			return false;
		}
	}
}