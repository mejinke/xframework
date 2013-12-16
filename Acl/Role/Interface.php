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
 * @desc 访问控制列表适配器接口
 * @author jingke
 */
interface XF_Acl_Role_Interface
{
	/**
	 * 加载角色资料
	 * @param string $role_id 角色ID
	 * @return void
	 */
	public function load($role_id);
	
	/**
	 * 获取角色ID
	 * @return string
	 */
	public function getRoleId();
	
	/**
	 * 获取角色名称
	 * @return string
	 */
	public function getName();
	
	/**
	 * 设置角色名称
	 * @param string $name
	 * @return XF_Acl_Role_Interface
	 */
	public function setName($name);
	
	/**
	 * 获取角色访问控制列表
	 * @return array
	 */
	public function getAcls();
	
	/**
	 * 设置角色访问控制列表
	 * @param mixed $acls
	 * @return XF_Acl_Role_Interface
	 */
	public function setAcls($acls);
	
	/**
	 * 获取角色权限说明/描述
	 * @return string
	 */
	public function getContent();
	
	/**
	 * 设置角色权限说明/描述
	 * @param string $content
	 * @return XF_Acl_Role_Interface
	 */
	public function setContent($content);
	
	/**
	 * 是否存在当前角色
	 * @return bool
	 */
	public function hasRole();
	
	/**
	 * 保存角色信息
	 * @return bool
	 */
	public function save();
}