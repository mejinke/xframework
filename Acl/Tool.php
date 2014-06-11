<?php
class XF_Acl_Tool
{
	private $_map;
	
	public function makeConfig()
	{
		$this->make(APPLICATION_PATH.'/controllers/');
		$dirs = XF_File::getFolder(APPLICATION_PATH.'/modules/');
		if (is_array($dirs))
		{
			foreach ($dirs as $dir)
			{
				$this->make(APPLICATION_PATH.'/modules/'.$dir.'/controllers/');
			}
		}
		
		if (!is_array($this->_map))
			return;
			
		$phpCode = '';
		
		foreach ($this->_map as $k => $ms)
		{
			$str = '';
			foreach ($ms as $m)
			{
				if ($str == '')
					$str .="'{$m}:'";
				else 
					$str .=",\n\t'{$m}:'";
			}
			$phpCode .= "'{$k}:' => array(\n\t{$str}\n),\n";
		}
		echo $phpCode;
	}
	
	public function make($dir)
	{
		$moduleName = 'Default';
		if (strpos($dir, 'modules/') !== false)
		{
			$tmp = explode('modules/', $dir);
			$moduleName = ucfirst(str_replace('/controllers/', '', $tmp[1]));
		}
		$list = XF_File::fileAll($dir);

		if (!is_array($list))
			return;
		foreach ($list as $l)
		{
			if (strpos($l, 'Controller.php') !== false)
			{
				@require_once $dir.$l;
				$classname = $cname = str_replace('.php', '', $l);
				if ($moduleName !='Default')
					$classname = $moduleName.'_'.$classname;

				if (class_exists($classname) && strpos($classname, 'Abstract') === false)
				{
					$this->_map[$classname] = array();
					$ref = new ReflectionClass(new $classname());
					$methods = $ref->getMethods(ReflectionMethod::IS_PUBLIC);
					if (is_array($methods) && count($methods) >0)
					{
						foreach ($methods as $m)
						{
							if (strpos($m->name, 'Action') !== false && $m->name !='doAction' && $m->name != 'hasAction')
							{
								$this->_map[$classname][] = strtolower($moduleName.'.'.str_replace('Controller', '', $cname).'.'.str_replace('Action', '', $m->name));
							}
						}
					}
				}
				
			}
		}
	}
}