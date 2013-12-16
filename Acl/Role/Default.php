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
 * @desc 访问控制列表基于数据库默认的适配器
 * @author jingke
 */
class XF_Acl_Role_Default extends XF_Db_Table_Abstract implements XF_Acl_Role_Interface 
{
	
	/**
	 * 角色ID
	 * @var string
	 */
	protected $_role_id;
	
	/**
	 * 角色名称
	 * @var string
	 */
	protected $_name;
	
	/**
	 * 角色访问控制列表
	 * @var array
	 */
	protected $_acls;
	
	/**
	 * 角色权限说明/描述
	 * @var string
	 */
	protected $_content;
	
	/**
	 * 录前角色是否存在
	 * @var bool
	 */
	protected $_is_exists = false;
	
	public function __construct($databaseName)
	{
		$this->_primary_key = 'xf_role_id';
		$this->_table_name = 'xf_roles';
		$this->_db_name = XF_Db_Tool::getDBName($databaseName);
	}
	
	public function getRoleId()
	{
		return $this->_role_id;
	}
	
	public function getName()
	{
		return $this->_name;
	}
	
	public function setName($name)
	{
		$this->_name = $name;
		return $this;
	}
	
	public function getAcls()
	{
		return $this->_acls;
	}
	
	public function setAcls($acls)
	{
		$this->_acls = $acls;
		return $this;
	}
	
	public function getContent()
	{
		return $this->_content;
	}
	
	public function setContent($content)
	{
		$this->_content = $content;
		return $this;
	}
	
	public function load($role_id)
	{	
		$role = $this->getTableSelect()->findRow($role_id);
		if ($role == false) 
			return;
		$this->_is_exists = true;
		$this->_role_id = $role->xf_role_id;
		$this->_name = $role->name;
		$this->_content = $role->content;
		if ($role->map == '*')
		{
			$this->_acls = array('*');
			return;
		}
		$this->_acls = explode(',', $role->map);
	}
	
	public function hasRole()
	{
		return $this->_is_exists;
	}
	
	public function save()
	{
		if (!is_array($this->_acls) && $this->_acls !='*')
			throw new XF_Acl_Exception('控制列表必需是数组或"*"');
	
		$data = '';
		if ($this->_acls == '*')
			$data = '*';
		else 
			$data = implode(',', $this->_acls);
		
		$this->fillDataFromArray(array(
			'name' => $this->_name,
			'map' => $data,
			'content' => $this->_content
		));
		
		//新增
		if (empty($this->_role_id))
			$this->insert();
		else
		{		
			//更新
			$this->fillDataFromArray(array('xf_role_id' => $this->_role_id));
			$this->update();
		}
	}
}