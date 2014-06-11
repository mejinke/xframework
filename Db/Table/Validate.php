<?php
/**
 * 
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2011-10-12
 * -+-----------------------------------
 *
 * @desc 数据验证类 验证表单及实体类数据
 * @author jingke
 */
class XF_Db_Table_Validate
{


	/**
	 * 当前实例
	 * @var XF_Db_Table_Validate
	 */
	private static $_instance = null;
	
	/**
	 * 验证数据，需要验证总数
	 * @var int
	 */
	private $_validate_count = 0;

	/**
	 * 验证成功数
	 * @var int
	 */
	private $_validate_success_count = 0;

	private function __construct(){}
	private function __clone(){}
	
	/**
	 * 获取当前实例
	 * @return XF_Db_Table_Validate
	 */
	public static function getInstance()
	{
		if (self::$_instance === null)
			self::$_instance = new self();
		return self::$_instance;
	}
	
	/**
	 * 验证数据完整性 Validate
	 * @access public
	 * @param array $data 将要验证的数组
	 * @param array $validate_rules 验证规则数组
	 * @param bool $validate_all　是否同时验证所有规则
	 * @param XF_Db_Table_Abstract $db_table　主要支持unique的验证，默认为null
	 * @param bool $is_insert 是否为添加数据操作？如果不是，则不强制执行required
	 * @return	mixed 一般返回验证后的数据［数组］
	 */
	public function validateData(&$data, $validate_rules, $validate_all = false, XF_Db_Table_Abstract $db_table = NULL, $is_insert = TRUE)
	{
		if(!empty($validate_rules))
		{
			//检测是否为有效的Validate
			if(!isset($validate_rules['rules']))
				return false;
			//获取验证规则
			$rules = $validate_rules['rules'];

			//获取错误消息
			$message = $validate_rules['messages'];

			$vali_keys = array_keys($rules);

			$tmp_array = array();
			
			//依次对规则进行分析
			foreach($rules as $key => $val)
			{ 
				//如果当规则字段存在
				if(isset($data[$key]) || $validate_all == true)
				{
					//将规则转换为数组
					$tmp_array = explode(',', $val);

					foreach ($tmp_array as $index => $vals)
					{
						//获取规则名称及值
						$tep = explode(':', $vals);
						$status = $this->_switchValidateFormData($data, $key, strtolower($tep[0]), $tep[1], $message, $db_table, $is_insert);
						if ($status === false )
						{
							//是否有设置默认值？
							if ($defaultKey = XF_Functions::searchValueFromArray($tmp_array,'default:'))
							{
								$defaultArray = explode(':', $tmp_array[$defaultKey]);
								if(isset($defaultArray[1]) && $defaultArray[0] == 'default')
								{
									$data[$key] = $defaultArray[1];
									XF_DataPool::getInstance()->replace('TableFieldDataValidateError', NULL);
									return $data;
								}
							}
			
							return false;
						}
					}
				}
			}
		}
		return $data;
	}


	/**
	 * 验证表单 分析规则 Validate
	 * @access private
	 * @param array $data 将要验证的数组资料
	 * @param string $key	当前需要验证的字段[key]
	 * @param string $rules_name	规则名称
	 * @param mixed $rules_value	规则值
	 * @param array	 $message	自定义错误信息
	 * @param XF_Db_Table_Abstract $db_table　主要支持unique的验证，默认为null
	 * @param bool $is_insert 是否为添加数据操作？如果不是，则不强制执行required
	 * @return bool
	 */
	private function _switchValidateFormData($data, $key, $rules_name, $rules_value, $message, XF_Db_Table_Abstract $db_table = NULL, $is_insert = TRUE)
	{
		$this->_validate_count++;
		//判断规则，分析结果
		$validateOk = true;
	
		switch($rules_name)
		{
			//验证 必填
			case 'required':
				if($rules_value == 'true')
				{
					if (isset($data[$key]))
					{
						if (XF_Functions::isEmpty($data[$key]))
							$validateOk = false;
					}
					elseif ($is_insert == true)
					{
						$validateOk = false;
					}						
				}
				break;

			//验证 数字
			case 'number':
				if(isset($data[$key]))
				{
					if($rules_value=='true')
					{
						if(!is_numeric($data[$key]))
							$validateOk = false;
					}
				}
				break;

			//数字小于
			case 'lt':
				if(isset($data[$key]))
				{
					if (!is_numeric($data[$key]) || !is_numeric($rules_value))
						$validateOk = false;
					elseif($data[$key] >= $rules_value)
						$validateOk = false;
				}
				break;

			//数字大于
			case 'gt':
				if(isset($data[$key]))
				{
					if (!is_numeric($data[$key]) || !is_numeric($rules_value))
						$validateOk = false;
					elseif($data[$key] <= $rules_value)
						$validateOk = false;
				}
				break;
				
			//等于
			case 'equal':
				if(isset($data[$key]))
				{
					if($data[$key] !== $rules_value)
						$validateOk = false;
				}
				break;
				
			//不等于
			case 'unequal':
				if(isset($data[$key]))
				{
					if($data[$key] === $rules_value)
						$validateOk = false;
				}
				break;
				
			//验证 是否为正确的email
			case 'email':
				if(!empty($data[$key]) && $rules_value == 'true')
				{
					$validateOk = XF_String_Validate_Email::validate($data[$key]);
				}
				break;

			//验证 是否为正确的电话号码
			case 'tel':
				if(!empty($data[$key]) && $rules_value == 'true')
				{
					
						$validateOk = XF_String_Validate_Phone::validate($data[$key]);
				}
				break;

			//验证 是否为正确的手机号码
			case 'mobile':
				if(!empty($data[$key]) && $rules_value == 'true')
				{
					$validateOk = XF_String_Validate_Mobile::validate($data[$key]);
				}
				break;

			//验证 是否为正确的身份证号码
			case 'card':
				if(!empty($data[$key]) && $rules_value == 'true')
				{
					$validateOk = XF_String_Validate_Card::validate($data[$key]);
				}
				break;

			//验证 是否为正确邮政编码
			case 'zip':
				if(!empty($data[$key]) && $rules_value == 'true')
				{
					$validateOk = XF_String_Validate_ZipCode::validate($data[$key]);
				}
				break;

			//验证 最小长度
			case 'minlen':
				if(!empty($data[$key]))
				{
					if(mb_strlen($data[$key],'utf8') < $rules_value)
						$validateOk = false;
				}
				break;

			//验证 最大长度
			case 'maxlen':
				if(!empty($data[$key]))
				{
					if(mb_strlen($data[$key],'utf8') > $rules_value)
						$validateOk = false;
				}
				break;
				
			//验证 ip地址
			case 'ip':
				if(!empty($data[$key]) && $rules_value == 'true')
				{
					$validateOk = XF_String_Validate_Ip::validate($data[$key]);
				}
				break;

			/**
			 *  验证 取值范围在列表中
			 *  例如：in:2|23|9|0
			 *
			 */
			case 'in':
				if(XF_Functions::isEmpty($data[$key]) == FALSE)
				{
					if (!in_array($data[$key], explode('|', $rules_value)))
					{
						$validateOk = false;
					}	
				}
				break;

			/**
			 *  验证 取值范围必需不在列表中
			 *  例如：not_in:2|23|9|0
			 *
			 */
			case 'not_in':
				if(XF_Functions::isEmpty($data[$key]) == FALSE)
				{
					if (!in_array($data[$key], explode('|', $rules_value)))
					{
						$validateOk = false;
					}
				}
				break;

			//验证与指定的字段值是否相同
			case 'confirm':
				if(isset($data[$key]))
				{
					if($data[$key] != $data[$rules_value])
						$validateOk = false;
				}
				break;
				
			case 'date':
				if (isset($data[$key]) && $rules_value == 'true')
				{
					if(!XF_String_Validate_Date::validate($data[$key]))
						$validateOk = false;
				}
				break;
				
			case 'time':
				if (isset($data[$key]) && $rules_value == 'true')
				{
					if(!XF_String_Validate_Time::validate($data[$key]))
						$validateOk = false;
				}
				break;
				
			case 'unique':
				if (isset($data[$key]) && $rules_value == 'true')
				{
					if ($db_table == NULL)
						$validateOk = false;
					elseif(!XF_Db_Table_Validate_Unique::validate($data[$key], $key, $db_table, $is_insert))
						$validateOk = false;
				}
				break;
		}

		//没有错误
		if($validateOk)
		{
			$this->_validate_success_count++;
			return true;
		}
		else //检测到错误
		{
			//记录错误
			$errorMessage = $key.'=>'.$rules_name.':'.$rules_value;
			if (isset($message[$key]) && is_array($message[$key]) && isset($message[$key][$rules_name]))
				$errorMessage = $message[$key][$rules_name];
			elseif (isset($message[$key]))
				$errorMessage = (string)$message[$key];
			XF_DataPool::getInstance()->replace('TableFieldDataValidateError', $errorMessage);
			return false;
		}
	}
}