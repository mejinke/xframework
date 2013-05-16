<?php
/**
 * 
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2012-02-17
 * -+-----------------------------------
 * 
 * @desc 基本图形处理类
 * @author jingke
 */
class XF_Images
{

	/**
	 * 生成图形验证码
	 * @access public
	 * @param int $Width 宽度[可选]
	 * @param int $Height 高度[可选]
	 * @return void
	 */
	public static function verify($Width = 50, $Height = 20 , $options = null)
	{
		if ($options == NULL)
		{
			$options = array(
				'R' => 200,
				'G'	=> 200,
				'B' => 200
			);
		}
		$mCheckCodeNum=4;
		$mCheckCode = '';
		for ($i=0; $i<$mCheckCodeNum; $i++)
		{
			$mCheckCode.=strtoupper(chr(rand(97,122)));
		}
		header ("Content-type: image/png");
		
		$mCheckImage = @imagecreate ($Width,$Height);
		$session = new XF_Session('XF_ImageVerify');
		$session->write(strtolower($mCheckCode));
		imagecolorallocate ($mCheckImage, $options['R'], $options['G'], $options['B']);
		for ($i=0;$i<=128;$i++)
		{
			$mDisturbColor = imagecolorallocate ($mCheckImage, rand(0,255), rand(0,255), rand(0,255));
			imagesetpixel($mCheckImage,rand(2,128),rand(2,38),$mDisturbColor);
		}
		for ($i=0;$i<$mCheckCodeNum;$i++)
		{
			$bg_color = imagecolorallocate ($mCheckImage, rand(0,255), rand(0,128), rand(0,255));
			$x = floor($Width/$mCheckCodeNum)*$i+3;
			$y = rand(0,$Height-15);
			imagechar ($mCheckImage, 5, $x, $y, $mCheckCode[$i], $bg_color);
		}
		imagepng($mCheckImage);
		imagedestroy($mCheckImage);

	}
	
	
	public static function newVerify()
	{
		
		mt_srand((double)microtime() * 1000000);
		$seccode = sprintf('%04d', mt_rand(0, pow(10, 4) - 1));
		$session = new XF_Session('XF_ImageVerify');
		$session->write(strtolower($seccode));
		$im = imagecreate(62, 25);
		$backgroundcolor = imagecolorallocate ($im, 255, 255, 255);
		
		$numorder = array(1, 2, 3, 4);
		shuffle($numorder);
		$numorder = array_flip($numorder);
		
		for($i = 1; $i <= 4; $i++) {
			$imcodefile = '../images/number'.$seccode[$numorder[$i]].'.gif';
			$x = $numorder[$i] * 13 + mt_rand(0, 4) - 2;
			$y = mt_rand(0, 3);
			$imcode = imagecreatefromgif($imcodefile);
			$data = getimagesize($imcodefile);
			imagecolorset($imcode, 0 ,mt_rand(50, 255), mt_rand(50, 128), mt_rand(50, 255));
			imagecopyresized($im, $imcode, $x, $y, 0, 0, $data[0] + mt_rand(0, 6) - 3, $data[1] + mt_rand(0, 6) - 3, $data[0], $data[1]);
		}
		
		$linenums = mt_rand(10, 32);
		for($i=0; $i <= $linenums; $i++) {
			$linecolor = imagecolorallocate($im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
			$linex = mt_rand(0, 62);
			$liney = mt_rand(0, 25);
			imageline($im, $linex, $liney, $linex + mt_rand(0, 4) - 2, $liney + mt_rand(0, 4) - 2, $linecolor);
		}
		
		for($i=0; $i <= 64; $i++) {
			$pointcolor = imagecolorallocate($im, mt_rand(50, 255), mt_rand(50, 255), mt_rand(50, 255));
			imagesetpixel($im, mt_rand(0, 62), mt_rand(0, 25), $pointcolor);
		}
		
		$bordercolor = imagecolorallocate($im , 150, 150, 150);
		imagerectangle($im, 0, 0, 61, 24, $bordercolor);
		
		header('Content-type: image/png');
		imagepng($im);
		imagedestroy($im);
	}




	/*等比显示图片*/
	/**
	 * 等比显示图片
	 * @access public
	 * @param array $options  数组参数:<br>$file 图片地址<br>$width 宽度<br>$height 高度<br>$R $G $B 背景色<BR>
	 * $dir 保存目录<BR>除file width height 外其它Key均为可选
	 */
	public static function resizeImage(Array $options)
	{
		$file = $options['file'];
		$maxwidth = $options['width'];
		$maxheight = $options['height'];
		$resizeimage = isset($options['resize']) ? isset($options['resize']) : false;
		isset($options['R']) ? $R=$options['R'] : $R=255;
		isset($options['G']) ? $G=$options['G'] : $G=255;
		isset($options['B']) ? $B=$options['B'] : $B=255;
	
		//保存目录
		$dir = isset($options['dir']) ? $options['dir'] : pathinfo($file, PATHINFO_DIRNAME);
		$saveFile = null;
		if (isset($options['saveFile']))
		{
			$dir = null;
			$saveFile = $options['saveFile'];
		}
			
		/*取得文件后缀*/
		$extend = pathinfo($file);
		$filetype = strtolower($extend["extension"]);
		switch($filetype)
		{
			case 'jpeg':
			case 'jpg':
				$im=imagecreatefromjpeg($file);
				break;
			case 'png':
				$im= imageCreateFromPng($file);
				break;
			case 'gif':
				$im=imageCreateFromGif($file);
				break;
			default:return;
		}
	
	
		/*计算宽度*/
		$pic_width = imagesx($im);
		/*高度*/
		$pic_height = imagesy($im);
	
		
		$resizewidth_tag = false;
		$resizeheight_tag = false;
		if(($maxwidth && $pic_width > $maxwidth) || ($maxheight && $pic_height > $maxheight))
		{
			if($maxwidth && $pic_width>$maxwidth)
			{
				$widthratio = $maxwidth/$pic_width;
				$resizewidth_tag = true;
			}
	
			if($maxheight && $pic_height>$maxheight)
			{
				$heightratio = $maxheight/$pic_height;
				$resizeheight_tag = true;
			}
	
			if($resizewidth_tag && $resizeheight_tag)
			{
				if($widthratio<$heightratio)
					$ratio = $widthratio;
				else
					$ratio = $heightratio;
			}
	
			if($resizewidth_tag && !$resizeheight_tag)
				$ratio = $widthratio;
			if($resizeheight_tag && !$resizewidth_tag)
				$ratio = $heightratio;
	
			$newwidth = $pic_width * $ratio;
			$newheight = $pic_height * $ratio;
	
			//if(function_exists("imagecopyresampled")){
				$newim = imagecreatetruecolor($newwidth,$newheight);
				imagecopyresampled($newim,$im,0,0,0,0,$newwidth,$newheight,$pic_width,$pic_height);
			//}
			//else{
			//	$newim = imagecreate($newwidth,$newheight);
			//	imagecopyresized($newim,$im,0,0,0,0,$newwidth,$newheight,$pic_width,$pic_height);
			//}
			/*创建一张指定大小的画布*/
			$img = imagecreatetruecolor($maxwidth,$maxheight);
			/*获取画布背景色*/
			$color = ImageColorAllocate($img,$R,$G,$B);
			/*填充颜色*/
			ImageFill($img,0,0,$color);
			/*缩略图填充至画布，并垂直居中*/
			$x = ($maxwidth-$newwidth)/2;
			$y = ($maxheight-$newheight)/2;
			imagecopyresampled($img,$im,$x,$y,0,0,$newwidth,$newheight,$pic_width,$pic_height);
			/*输出图片*/
			if(empty($dir))
			{
				if (empty($saveFile)){
					@header("Content-Type:image/png");
					if ($resizeimage == false)
						imagejpeg($newim);
					else
						imagejpeg($img);
					
				}else{
					if ($resizeimage == false)
						imagejpeg($newim,$saveFile);
					else
						imagejpeg($img,$saveFile);
				}
			}
			else
			{
				imagejpeg($newim,$dir.'/'.$maxwidth.'x'.$maxheight.'_'.$extend['filename'].'.'.$extend['extension']);
			}
	
			imagedestroy($img);
			imagedestroy($im);
			return true;
		}
		else
		{
				if(empty($dir))
				{
					if (empty($saveFile)){
						@header("Content-Type:image/png");
						imagejpeg($im);
					}else{
						imagejpeg($im,$saveFile);
					}
				}
				else
				{
					imagejpeg($im,$dir.'/'.$maxwidth.'x'.$maxheight.'_'.$extend['filename'].'.'.$extend['extension']);
				}
				imagedestroy($im);
				return true;
		}
		return false;
	}


}