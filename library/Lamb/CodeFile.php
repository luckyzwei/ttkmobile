<?php
class Lamb_CodeFile
{
 //楠岃瘉镰佷綅鏁?
 private $mCheckCodeNum  = 4;

 //浜х敓镄勯獙璇佺爜
 private $mCheckCode   = '';
 
 //楠岃瘉镰佺殑锲剧墖
 private $mCheckImage  = '';

 //骞叉壈镀忕礌
 private $mDisturbColor  = '';

 //楠岃瘉镰佺殑锲剧墖瀹藉害
 private $mCheckImageWidth = '80';

 //楠岃瘉镰佺殑锲剧墖瀹藉害
 private $mCheckImageHeight  = '20';
 
 //楠岃瘉镰佽儗鏅鑹茬殑RGB链?
 private $mBgRGB = array(
 	'r' => 200,
 	'g' => 200,
 	'b' => 200
 );
 
 public $mSessionVarName = 'randval';

 /**
 *
 * @brief  杈揿嚭澶?
 *
 */
 private function OutFileHeader()
 {
  header ("Content-type: image/png");
 header ("Cache-Control: no-cache");
 }

 /**
 *
 * @brief  浜х敓楠岃瘉镰?
 *
 */
 private function CreateCheckCode()
 {
  $this->mCheckCode = strtoupper(substr(md5(rand()),0,$this->mCheckCodeNum)); 
  //session_save_path(dirname(__FILE__)."/session");
  @session_start(); 
  $randval=$this->mCheckCode;
  $_SESSION[$this->mSessionVarName]=$this->mCheckCode;
  return $this->mCheckCode;
 }

 /**
 *
 * @brief  浜х敓楠岃瘉镰佸浘鐗?
 *
 */
 private function CreateImage()
 {
  $this->mCheckImage =@imagecreate ($this->mCheckImageWidth,$this->mCheckImageHeight);
  imagecolorallocate ($this->mCheckImage, $this->mBgRGB['r'], $this->mBgRGB['g'], $this->mBgRGB['b']);
  return $this->mCheckImage;
 }

 /**
 *
 * @brief  璁剧疆锲剧墖镄勫共镓板儚绱?
 *
 */
 private function SetDisturbColor()
 {
  for ($i=0;$i<=128;$i++)
  {
   $this->mDisturbColor = imagecolorallocate ($this->mCheckImage, rand(0,255), rand(0,255), rand(0,255));
   imagesetpixel($this->mCheckImage,rand(2,128),rand(2,38),$this->mDisturbColor);
  }
 }
 
 /**
  * 璁剧疆楠岃瘉镰佽儗鏅浘鐗囩殑鑳屾櫙棰滆壊镄凴GB链?
  * 
  * @param int $r 濡傛灉涓簄ull鍒欎笉淇敼
  * @param int $g
  * @param int $b
  */
 public function setBgRGB($r = null, $g = null, $b = null)
 {
 	foreach (array('r', 'g', 'b') as $key) {
	 	if (null !== $$key && Lamb_Utils::isInt($$key, true) && $$key >= 0 and $$key <= 255) {
	 		$this->mBgRGB[$key] = $$key;
	 	}
	}
	return $this;
 }

 /**
 *
 * @brief  璁剧疆楠岃瘉镰佸浘鐗囩殑澶у皬
 *
 * @param  $width  瀹?
 *
 * @param  $height 楂?
 *
 */
 public function SetCheckImageWH($width, $height)
 {
 	if (Lamb_Utils::isInt($width, true)) {
  		$this->mCheckImageWidth  = $width;
	}
	
	if (Lamb_Utils::isInt($height, true)) {
  		$this->mCheckImageHeight = $height;
	}
  	return $this;
 }

 /**
 *
 * @brief  鍦ㄩ獙璇佺爜锲剧墖涓婇€愪釜鐢讳笂楠岃瘉镰?
 *
 */
 private function WriteCheckCodeToImage()
 {
  for ($i=0;$i<$this->mCheckCodeNum;$i++)
  {
   $bg_color = imagecolorallocate ($this->mCheckImage, rand(0,255), rand(0,128), rand(0,255));
   $x = floor($this->mCheckImageWidth/$this->mCheckCodeNum)*$i;
   $y = rand(0,$this->mCheckImageHeight-15);
   imagechar ($this->mCheckImage, 5, $x, $y, $this->mCheckCode[$i], $bg_color);
  }
 }

 /**
 *
 * @brief  杈揿嚭楠岃瘉镰佸浘鐗?
 *
 */
 public function OutCheckImage()
 {
  $this ->OutFileHeader();
  $this ->CreateCheckCode();
  $this ->CreateImage();
  $this ->SetDisturbColor();
  $this ->WriteCheckCodeToImage();
  imagepng($this->mCheckImage);
  imagedestroy($this->mCheckImage);
 }
}