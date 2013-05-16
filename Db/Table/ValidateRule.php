<?php
/**
 * 
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2012-02-16
 * -+-----------------------------------
 *
 * @desc 表字段验证规则对象
 * @author jingke
 */
class XF_Db_Table_ValidateRule
{
	/**
	 * 表字段规则列表
	 * @var array
	 */
	protected $_rules;
	
	/**
	 * 规则错误消息信息列表
	 * @var array
	 */
	protected $_messages;
	
	
	/**
	 * 初化始
	 * @param array $default　
	 */
	public function __construct(Array $default = null)
	{
		if (isset($default['rules']) && is_array($default['rules']))
		{
			$this->_rules = $default['rules'];
		}
		if (isset($default['messages']) && is_array($default['rules']))
		{
			$this->_messages = $default['messages'];
		}
	}
	
	/**
	 * 设置规则［如果存在，则忽略］
	 * @param string $fieldName 字段名称
	 * @param string $ruleString 规则字符串，如：'required:true,number:true'
	 * @param array $messages　规则错误消息列表，键名与$ruleString对应。如：array('required' => '必填！', 'number' => '只能是数字！')
	 * @return XF_Db_Table_ValidateRule
	 */
	public function addRules($fieldName, $ruleString, Array $messages)
	{
		if (isset($this->_rules[$fieldName]))
			return $this;
		else
			return $this->setRule($fieldName, $ruleString, $messages);
	}
	
	/**
	 * 设置规则［如果存在，则复盖］
	 * @param string $fieldName 字段名称
	 * @param string $ruleString 规则字符串，如：'required:true,number:true'
	 * @param array $messages　规则错误消息列表，键名与$ruleString对应。如：array('required' => '必填！', 'number' => '只能是数字！')
	 * @return XF_Db_Table_ValidateRule
	 */
	public function setRules($fieldName, $ruleString, Array $messages)
	{
		$this->_rules[$fieldName] = $ruleString;
		$this->_messages[$fieldName] = $messages;
		return $this;
	}
	
	/**
	 * 在已存在规则基础上追加［如果存在，则复盖］
	 * @param string $fieldName 字段名称
	 * @param array $rule 规则，如：array('require'=>'true', 'number' => 'true')
	 * @param array $messages　规则错误消息列表，键名与$rule对应。如：array('required' => '必填！', 'number' => '只能是数字！')
	 * @return XF_Db_Table_ValidateRule
	 */
	public function appendRule($fieldName, Array $rule, Array $messages)
	{
		if (isset($this->_rules[$fieldName]) && isset($this->_messages[$fieldName]))
		{
			$rules = explode(',', $this->_rules[$fieldName]);
			foreach ($rule as $key => $val)
			{
				$rules[] = $key.':'.$val;
			}
			$this->_rules[$fieldName] = implode(',', $rules);
			
	 
			$oldMessages = $this->_messages[$fieldName];
			$this->_messages[$fieldName] = null;
			foreach ($rules as $val)
			{
				$var = explode(':', $val);
			 
				foreach ($messages as $key => $mval)
				{ 
					if ($var[0] != $key)
					{
						$this->_messages[$fieldName][$var[0]] = is_array($oldMessages) && $oldMessages[$var[0]] ? $oldMessages[$var[0]] : $oldMessages;
					}
					else
						$this->_messages[$fieldName][$key] = $mval;
				}
			}
		}
		return $this;
	}
	
	/**
	 * 删除一条规则
	 * @param string $fieldName 字段名称
	 * @param string $ruleName 规则名称，如:required、number、<、>、email、tel、moblie、zip、minlen、maxlen、in、not_in、confirm 
	 * @return XF_Db_Table_ValidateRule
	 */
	public function removeRule($fieldName, $ruleName)
	{
		if (isset($this->_rules[$fieldName]))
		{
			$rules = explode(',', $this->_rules[$fieldName]);
			foreach ($rules as $key => $val)
			{
				$v = explode(':', $val);
				if ($v[0] == $ruleName)
				{
					unset($rules[$key]);
					if(is_array($this->_messages[$fieldName]) && isset($this->_messages[$fieldName][$v[0]]))
						unset($this->_messages[$fieldName][$v[0]]);
				}
				
			}
			$this->_rules[$fieldName] = implode(',', $rules);
		}
		return $this;
	}
	
	/**
	 * 删除指定字段的所有规则
	 * @param string $fieldName 字段名称
	 * @return XF_Db_Table_ValidateRule
	 */
	public function removeFieldRule($fieldName)
	{
		if (isset($this->_rules[$fieldName]))
			unset($this->_rules[$fieldName]);
		if (isset($this->_messages[$fieldName]))
			unset($this->_messages[$fieldName]);
		return $this;
	}
	
	/**
	 * 删除所有字段的规则
	 * @return XF_Db_Table_ValidateRule
	 */
	public function removeAllFieldRule()
	{
		$this->_rules = $this->_messages = array();
		return $this;
	}
	
	/**
	 * 返回对象的数组形式
	 * @return array
	 */
	public function toArray()
	{
		return array('rules' => $this->_rules, 'messages' => $this->_messages);	
	}
}