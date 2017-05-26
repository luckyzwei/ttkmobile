<?php
/**
 * Lamb Framework
 * @author 灏忕緤
 * @package Lamb_IO
 */
class Lamb_IO_File
{
	/**
	 * @var source 
	 */
	protected $_mFileHandle = null;
	
	/**
	 * @var string 淇濆瓨镓揿紑鏂囦欢镄勮矾寰?
	 */
	protected $_mPath = '';
	
	/**
	 * @param string $path
	 * @param string $mode
	 */
	public function __construct($path = '', $mode = '')
	{
		if ($path) {
			$this->setOrGetPath($path);
		}
			
		if ($path && $mode) {
			$this->open($path, $mode);
		}
	}
	
	/**
	 * Destruct the Lamb_IO_File
	 */
	public function __destruct()
	{
		$this->close();
	}
	
	/**
	 * Set or retrieve the value of '_mPath'
	 *
	 * @param string $path
	 * @return Lamb_IO_File | string
	 */
	public function setOrGetPath($path = null)
	{
		if (null === $path) {
			return $this->_mPath;
		}
		$this->_mPath = (string)$path;
		return $this;
	}
	
	/**
	 * Open the file
	 *
	 * @param string $path
	 * @param string $mode
	 * @return Lamb_IO_File
	 * @throws Lamb_IO_Exception
	 */
	public function open($path, $mode, $useIncludePath = false)
	{
		$handle = fopen($path, $mode, $useIncludePath);
		if (false === $handle) {
			throw new Lamb_IO_Exception("Can not open the file on the \"$path\" path");
		}
		$this->_mFileHandle = $handle;
		$this->setOrGetPath($path);
		return $this;
	}
	
	/**
	 * Close the opened file 
	 *
	 * @return boolean
	 */
	public function close()
	{
		$bRet = false;
		if ($this->_mFileHandle) {
			$bRet = fclose($this->_mFileHandle);
			$this->_mFileHandle = null;
		}
		return $bRet;
	}
	
	/**
	 * @return source
	 */
	public function getHandle()
	{
		return $this->_mFileHandle;
	}
	
	/**
	 * Get a list files in path
	 *
	 * @param string $path
	 * @return array
	 */
	public function toArray($path = '')
	{
		if (!$path) {
			$path = $this->setOrGetPath();
		}
		return file($path);
	}
	
	/**
	 * Read data from file
	 *
	 * @param string $path
	 * @return string
	 * @throws Lamb_IO_Exception
	 */
	public function read($size = 0)
	{
		if (!$this->_mFileHandle) {
			throw new Lamb_IO_Exception("Invaild file handle,must be open file first");
		}
		if ($size <= 0) {
			$size = $this->getFileSize($path);
		}
		return fread($this->_mFileHandle, $size);
	}
	
	/**
	 * Get the file's size 
	 *
	 * @param string $path if $path is empty,then use the default path
	 * @return int
	 */
	public function getFileSize($path = '')
	{
		if (!$path) {
			$path = $this->setOrGetPath();
		}
		return self::fileSize($path);
	}
	
	/**
	 * The wrapper of gets function
	 * 
	 * @param int $size
	 * @return stirng
	 * @throws Lamb_IO_Exception
	 */
	public function gets($size = 1024)
	{
		if (!$this->_mFileHandle) {
			throw new Lamb_IO_Exception("Invaild file handle,must be open file first");
		}
		return fgets($this->_mFileHandle, $size);
	}
	
	/**
	 * Clear the specifiec file's data
	 *
	 * @param string $path
	 */
	public function clear($path = '')
	{
		if (!$path) {
			$path = $this->setOrGetPath();
		}
		self::clearBuffer($path);
		return $this;
	}
	
	/**
	 * The wrapper of file_exists() function
	 *
	 * @param string $path
	 * @return boolean
	 */
	public static function exists($path)
	{
		return file_exists($path);
	}
	
	/**
	 * The wrapper of fileszie function
	 * 
	 * @param string $path
	 * @return int
	 */
	public static function fileSize($path)
	{
		return filesize($path);
	}
	
	/**
	 * The wrapper of file_put_contents() function
	 *
	 * @param stirng $strPath
	 * @param string $strContents
	 * @param int $nFlag
	 * @return int
	 */
	public static function putContents($strPath, $strContents, $nFlag=0)
	{
		return $nFlag<=0 ? file_put_contents($strPath, $strContents) 
						 : file_put_contents($strPath, $strContents, $nFlag);
	}
	
	/**
	 * The wrapper of file_get_contents() function
	 *
	 * @param string $strPath
	 * @param boolean $bCreate
	 * @return string
	 */
	public static function getContents($strPath, $bCreate=false)
	{
		if ($bCreate && !self::exists($strPath)) {
			self::clearBuffer($strPath);
		}
		return file_get_contents($strPath);
	}
	
	/**
	 * The wrapper of unlink() function
	 *
	 * @param string $strPath
	 * @return boolean
	 */
	public static function delete($strPath)
	{
		return @unlink($strPath);
	}
	
	/**
	 * The wrapper of filemtime function
	 *
	 * @param string $strPath
	 * @return int
	 */
	public static function getLastModifytime($strPath)
	{
		return filemtime($strPath);
	}
	
	/**
	 * 鍒犻櫎鎸囧畾鐩綍涓嬬殑镓€链夋枃浠?
	 *
	 * @param string $dir
	 * @param boolean $isRmdir 鏄惁鍒犻櫎褰揿墠鐩綍
	 */
	public static function delFileUnderDir($dir, $isRmdir = false)
	{
		$oDir=opendir($dir);
		while($file = readdir($oDir))
		{
			if($file != "." && $file != "..")
			{
				$file=$dir . DIRECTORY_SEPARATOR . $file;
				is_dir($file) ? self::delFileUnderDir($file, $isRmdir) : self::delete($file);
			}
		}
		closedir($oDir);
		
		if ($isRmdir) {
			rmdir($dir);
		}
	}
	
	/**
	 * 闱欐€佹柟娉?鍐欐枃浠?param1 鏂囦欢鍚?param2 鍐椤叆瀛楃涓?param3 阃夐」
	 *
	 * @param string $file
	 * @param string $str
	 * @param string $option
	 * @return void
	 */
	public static function write($file, $str="", $option="w")
	{
		$oFile=fopen($file, $option);
		fwrite($oFile, $str);
		fclose($oFile);
	}
	
	/**
	 * Get the specific file's extendtion
	 *
	 * @param string $strPath
	 * @return string
	 */
	public static function getFileExt($strPath)
	{
		$ret = '';
		if (($pos = strripos($strPath, '.')) !== false) {
			$ret = substr($strPath, $pos);
		}
		return $ret;
	}
	
	/**
	 * Clear the specific path data
	 *
	 * @param string $strPath
	 * @param boolean $bLock
	 */
	public static function clearBuffer($strPath, $bLock=false)
	{
		$bLock ? self::putContents($strPath, '', LOCK_EX) : fclose(fopen($strPath, 'w'));
	}
	
	/**
	 * 銮峰彇瀵瑰簲璺缎涓崭细閲嶅镄勬枃浠跺悕
	 *
	 * @param string $filepath
	 * @param string $delimiter 
	 * @param int $_index [reserve]
	 * @return string
	 */
	public static function getUniqueName($filepath, $delimiter = '_', $_index = 0)
	{
		if (!self::exists($filepath)) {
			return $filepath;
		}
		$ext = self::getFileExt($filepath);
		$filepath = dirname($filepath) . DIRECTORY_SEPARATOR . basename($filepath, $ext) . $delimiter . $_index++ . $ext;
		
		return self::getUniqueName($filepath, $delimiter, $_index);
	}
	
	/** 
	 * 灏呜矾寰勪腑镄勬枃浠跺悕CRC32缂栫爜
	 * eg:F:\dir1\file.txt => F:\dir1\(file镄刢rc32锷犲瘑鍚庣殑鏁版嵁).txt
	 *
	 * @param string $path
	 * @param string $suffix
	 * @return string
	 */
	public static function generateCrc32EncodeFileNamePath($path, $suffix = '.txt')
	{
		$dirname = dirname($path);
		$filename = Lamb_Utils::crc32FormatHex(basename($path, $suffix));
		return $dirname . DIRECTORY_SEPARATOR . $filename . $suffix;
	}
	
	/**
	 * 鑳藉瀹夊叏鍒涘缓璺缎 鍗充娇瀛愯矾寰勪笉瀛桦湪
	 *
	 * @param string $path
	 * @return boolean
	 */
	public static function mkdir($path)
	{
		$cache = array();
		
		while (!file_exists($path)) {
			array_push($cache, $path);
			$path = dirname($path);
		}
		
		for ($j = 0, $i = count($cache) - 1; $i >= $j; $i--) {
			if (!mkdir($cache[$i])) {
				return false;
			}
		}
		
		return true;	
	}	
}