<?php
/**
 * 
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2010-03-08
 * -+-----------------------------------------
 *
 * @desc 文件处理类
 * @author jinke
 */
class XF_File
{

	/**
	 * 读取一个文 件
	 * @access public
	 * @param string $file 文件路径
	 * @throws XF_File_Exception
	 * @return mixed
	 */
	public static function read($file)
	{
		if(!$fp = fopen($file, 'rb'))
		{
			throw new XF_Exception('Could not read this file!');
		}
		else
		{
			$data = fread($fp, filesize($file));
			fclose($fp);
			return $data;
		}
	}

	/**
	 * 写入内容到文件
	 * @access public
	 * @param string $file 文件路径
	 * @param string $content 将 要写入的内容
	 * @param string $mod 写入模式
	 * @throws XF_File_Exception
	 * @return bool
	 */
	public static function write($file, $content, $mod = 'w')
	{
		if(!$fp = fopen($file, $mod))
		{
			throw new XF_Exception('Could not read this file!');
		}
		else
		{
			flock($fp, 2);
			fwrite($fp, $content);
			fclose($fp);
			return true;
		}
	}

	/**
	 * 删除文件或目录
	 * @access public
	 * @param string $var 文件或目录路径
	 * @return bool
	 */
	public static function del($var)
	{	
		if (empty($var)) return;
		if(is_file($var))
			unlink($var);

		if(is_dir($var))
		{
			$handle = opendir($var);
			while(false !== ($myFile = readdir($handle)))
			{
				if($myFile != '.' && $myFile != '..')
				{
					$myFile= $var.DIRECTORY_SEPARATOR. $myFile;
				 	if(is_dir($myFile))
						XF_File::del($myFile);
                     else
				 		@unlink($myFile);
				}
			}
			closedir($handle);
			@rmdir($var);
		}
		unset($var);
		return true;
	}

	/**
	 * 递规的创建目录
	 * @access public
	 * @param string $path 目录
	 * @return bool
	 */
	public  static function  mkdirs($path, $mode = 0755)
	{
		is_dir(dirname($path)) || XF_File::mkdirs(dirname($path), $mode);
		return is_dir($path) || mkdir($path, $mode);
	}

	/**
	 * 取得字节数所对应的相关单位值
	 * @access public
	 * @param string $lenght
	 * @return string
	 */
	public static function setupSize($lenght) 
	{
		$units = array (
				'B',
				'KB',
				'MB',
				'GB',
				'TB',
				'PB',
				'EB',
				'ZB',
				'YB'
				);
				foreach ($units as $unit) 
				{
					if ($lenght > 1024)
					$lenght = round($lenght / 1024, 1);
					else
					break;
				}
				if (intval($lenght) == 0) 
				{
					return ("0 Bytes");
				}
				return $lenght . ' ' . $unit;
	}

	/**
	 * 获取远程HTTP文件大小
	 * @access public
	 * @param string $url 网络地址
	 * @return string
	 */
	public static function getHttpFileSize($url)
	{
		$url = parse_url($url);
		if($fp = @fsockopen($url['host'], empty($url['port']) ? 80 : $url['port'], $error))
		{
			fputs($fp, "GET ".(empty($url['path']) ? '/' : $url['path'])." HTTP/1.1\r\n");
			fputs($fp, "Host:$url[host]\r\n\r\n");
			while(!feof($fp))
			{
				$tmp = fgets($fp);
				if(trim($tmp) == '')
				{
					break;
				}
				else if(preg_match('/Content-Length:(.*)/si', $tmp, $arr))
				{
					return XF_File::setupSize(trim($arr[1]));
				}
			}
			return null;
		}
		else
		{
			return null;
		}
	}

	/**
	 * 获取远程FTP文件大小
	 * @access public
	 * @param string $host FTP地址
	 * @param string $file 文件名称
	 * @param string $user 账号 默认[anonymous]
	 * @param string $pass 密码 默认[anonymous]
	 */
	public static function getFtpFileSize($host, $file, $user = 'anonymous', $pass = 'anonymous')
	{
		$conn = ftp_connect($host);
		ftp_login( $conn, $user, $pass );
		$size = XF_File::setupSize(ftp_size( $conn, $file ));
		ftp_close($conn);

		return $size;
	}

	/**
	 * 获取一个文件夹大小
	 * @access public
	 * @param string $dir 文件夹目录
	 * @return string
	 */
	public static function folderSize($dir) 
	{
		if (!preg_match('#/$#', $dir)) 
		{
			$dir .= '/';
		}
		$totalsize = 0;
		foreach (XF_File::fileList($dir) as $name) 
		{
			$totalsize += (@ is_dir($dir . $name) ? XF_File::folderSize("$dir$name/") : (int) @ filesize($dir .
			$name));
		}
		return $totalsize;
	}
	
	/**
	 * 判断目录是否为空
	 * @access public
	 * @param unknown_type $dir
	 */
	public static function isEmptyDir( $dir ){
		$dh=opendir($dir);
		while(false!==($f=readdir($dh)))
		{
			if($f!="." && $f!=   ".." )
			{
				return false;
			}
		}
		return true;
	}

	/**
	 * 获取目录中所有文件
	 * @access public
	 * @param string $path 目录路径
	 */
	public static function fileAll($path) 
	{
		$list = array ();
		if (($hndl = @ opendir($path)) === false) 
		{
			return $list;
		}
		while (($file = readdir($hndl)) !== false) 
		{
			if ($file != '.' && $file != '..') 
			{
				$list[] = $file;
			}
		}
		closedir($hndl);
		return $list;
	}
	
	/**
	 * 获取某个目录中的文件及目录
	 * @access public
	 * @param string $path 路径
	 */
	public static function fileList($path) 
	{
		if (!preg_match('#/$#', $path)) 
		{
			$path .= '/';
		}
		
		$f = $d = array ();
		
		foreach (XF_File::fileAll($path) as $name) 
		{
			if (@ is_dir($path . $name)) 
			{
				$d[] = $name;
			} 
			elseif (@ is_file($path . $name)) 
			{
				$f[] = $name;
			}
		}
		natcasesort($d);
		natcasesort($f);
		return array_merge($d, $f);
	}

	/**
	 * 获取文件信息资料
	 * @access public
	 * @param string $filename 文件路径
	 */
	public static function getExt($filename) 
	{
		if (strstr($filename, "\\") || strstr($filename, "/")) 
		{
			$filename = basename($filename);
		}
		if (strstr($filename, ".")) 
		{
			return mb_substr(strrchr($filename, '.'), 1);
		} 
		else 
		{
			return false;
		}
	}
	
	/**
	 * 获取某个目录中的所有目录
	 * @access public
	 * @param string $dir 目录路径
	 */
	public static function getFolder($dir) 
	{
		$folder = array ();
		if ($handle = opendir($dir)) 
		{
			while (false !== ($file = readdir($handle))) 
			{
				if ($file != "." && $file != ".." && substr($file, 0, 1) != ".") 
				{
					if (is_dir($dir . $file)) 
					{
						$folder[] = $file;
					}
				}
			}
			closedir($handle);
		}
		return $folder;
	}
	
	/**
	 * 获取某个目录中的所有文件
	 * @access public
	 * @param string $dir 目录路径
	 */
	public static function getFile($dir) 
	{
		$files = array ();
		if ($handle = opendir($dir)) 
		{
			while (false !== ($file = readdir($handle))) 
			{
				if ($file != "." && $file != ".." && substr($file, 0, 1) != ".") 
				{
					if (is_file($dir . $file)) 
					{
						$files[] = $file;
					}
				}
			}
			closedir($handle);
		}
		return $files;
	}
	
	/**
	  * 检测某个文件是否过期   过期返回true
	  * @param string	$file	文件
	  * @param int	$now	给定时间
	  * @return bool
	  */
	 public  static function isDated($file, $now)
	 {
		if(is_file($file))
		{
			$time = fileatime($file);
			
			$time = $time+$now*60;
			echo $time;
			if(time() > $time)
			{
				@unlink($file);
				return true;
			}
		}
		return false;
	 }
	 
	 /**
	  * 将文件上传到指定的位置
	  * @param array $options 参数数组 <br/>
	  * inputName 上传控件名称<br/>
	  * maxSize 最大允许上传大小[单位：kb]<br/>
	  * folder 保存的目录， 从项目根目录开始<br/>
	  * extension  允许上传的文件类型
	  */
	 public static function upload(Array $options)
	 {
		//HTML上传控件名称
	    $fileName = isset($options['inputName']) ? $options['inputName'] : 'upload_input';
		//允许上传大小
		$maxlimit = isset($options['maxSize']) ? $options['maxSize']*1024*1024 : 8 * 1024 * 1024; //8M
		//保存目录
		$folder = isset($options['folder']) ? $options['folder'] : null;
		
		//保存的文件名
		$saveFileName = isset($options['saveFileName']) ? $options['saveFileName'] : null;
		
		//允许上传的文件类型
		$extensionArray = isset($options['extension']) ? $options['extension'] : array('jpg','jpeg','gif','png','bmp');
	    
		$return['status'] = 'error';
		
		if(isset($_FILES[$fileName]) && is_uploaded_file($_FILES[$fileName]['tmp_name']))
		{
			$file = $_FILES[$fileName];
			$name = $file['name'];
			$temp_name = $file['tmp_name'];
			$size = $file['size'];
			//限制文件上传大小
			if($size > $maxlimit)
			{
				$return['error_type'] = 'size';
				$return['error_message'] = 'size: '.ceil($size/100000).'/'.ceil($maxlimit/100000);
			}
			else 
			{
				$extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
				if(in_array(strtolower($extension), $extensionArray))
				{
					if ($extension == 'jpeg')
						$extension = 'jpg';
						
					$newFileName = '';
					if ($saveFileName == null)
						$newFileName = md5(time().'_'.mt_rand(1000, 9999)).'.'.$extension;
					else 
						$newFileName = $saveFileName.'.'.$extension;
						
					$md5 = md5_file($temp_name);
					//创建将要保存的目录 
					XF_File::mkdirs($folder);
					//上传图片
					if(move_uploaded_file($temp_name, $folder.'/'.$newFileName))
					{
						$path = str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', realpath($folder)).'/';
						$folder = realpath($folder.'/');
						$return['status'] = 'success';
						$return['fileDir'] = realpath($folder.'/');
						$return['folder'] = str_replace('\\','/',$path);;
						$return['filePath'] = 'http://'.$_SERVER['HTTP_HOST'].str_replace('\\','/',$path);
						$return['file'] = 'http://'.$_SERVER['HTTP_HOST'].str_replace('\\','/',$path).$newFileName;
						$return['file2'] = realpath(realpath($folder).'/'.$newFileName);
						$return['fileName'] = $newFileName;
						$return['fileExtension'] = $extension;
						$return['fileSize'] = $size;
						$return['md5'] = $md5;
						if (in_array(strtolower($extension), $extensionArray))
						{
							$r = getimagesize($folder.'/'.$newFileName);
							$return['fileWidth'] = $r[0];
							$return['fileHeight'] = $r[1];
						}
					}	
				}
				else
				{
					$return['error_type'] = 'type';
					$return['fileExtension'] = $extension;
					$return['error_message'] = '文件格式只能是:'.implode(',', $extensionArray);
				}
			}
		}
		else
			$return['error_message'] = 'To upload control name does not exist.';
		return $return;
	 }
}