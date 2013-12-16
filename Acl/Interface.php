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
 * @desc 访问控制列表接口
 * @author jingke
 */
interface XF_Acl_Interface
{
	public function setAdapter();
	public function validate();
}