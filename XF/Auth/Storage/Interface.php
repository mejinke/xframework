<?php
/**
 * 
 * -+-----------------------------------
 * |PHP5 Framework - 2011
 * |Web Site: www.iblue.cc
 * |E-mail: mejinke@gmail.com
 * |Date: 2012-01-11
 * -+-----------------------------------
 *
 * @desc 认证存储器接口
 * @author jingke
 */
interface XF_Auth_Storage_Interface
{
	/**
     * 存储是为空
     * @return bool
     */
    public function isEmpty();

    /**
     * 读取存储的内容
     * @return mixed
     */
    public function read();

    /**
     * 将内容写入到存储器中
     * @param  mixed $contents
     * @return void
     */
    public function write($contents);

    /**
     * 清空当前存储器中的内容
     * @return void
     */
    public function clear();
}