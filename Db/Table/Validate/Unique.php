<?php
/**
 * 
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2013-11-29
 * -+-----------------------------------
 *
 * @desc 验证表字段值唯一性
 * @author jingke
 */
class XF_Db_Table_Validate_Unique implements XF_Validate_Interface
{
	public static function validate($var)
	{
		$field_name = func_get_arg(1);
		$db_table = func_get_arg(2);
		$is_insert = func_get_arg(3);
		
		//insert
		if ($is_insert === true)
		{
			$row = $db_table->getTableSelect()->setInitAutoLoad(false)->setWhere(array($field_name => $var))->fetchRow();
			return $row == false ? TRUE : FALSE;
		}
		//update
		else
		{
			$where = array(
				$field_name => $var, 
				$db_table->getPrimaryKey().',<>' => $db_table->getPrimaryKeyValue()
			);
			$row = $db_table->getTableSelect()->setInitAutoLoad(false)->setWhere($where)->fetchRow();
			return $row == false ? TRUE : FALSE;
		}
	}
}