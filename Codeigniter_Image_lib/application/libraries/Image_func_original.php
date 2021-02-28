<?php

/**
 * Name:    Image_func
 *
 * Requirements: PHP5 or above
 *
 * @package    画像
 * @author     
 * @link       
 * @filesource
 */
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class ImageFunc
 */
class Image_func
{
	/**
	 * @var int
	 */
	private $rounds;

	/**
	 * @var string
	 */
	private $img_path_root;
	private $img_path;
	private $sub_dir_name;

	/**
	 * @var int|string|null
	 */
	private $randomState;

	/**
	 * コンストラクタ
	 *
	 * @param array $params
	 *
	 * @throws Exception
	 */
	public function __construct()
	{

		$config = &get_config();

		// 画像保存先
		$this->img_path_root = $config['img_path_root'];

		// 画像保存先
		$this->img_path = $config['img_path'];

		$this->sub_dir_name = "";
	}

	/**
	 * 画像保存先設定処理
	 *
	 * @access		public
	 * @param		$img_path_root	保存先ディレクトリ名
	 * @param		$img_path		サブディレクトリ名
	 * @return		-
	 */
	public function set_image_path($img_path_root, $img_path = "")
	{
		// ここが呼ばれない場合は定義値を参照
		$this->img_path_root = $img_path_root;
		$this->img_path = $img_path;
	}

	/**
	 * ディレクトリ設定処理
	 *
	 * @access		public
	 * @param		$sub_dir_name	サブディレクトリ名
	 * @return		-
	 */
	public function dir_name($sub_dir_name)
	{
		$this->sub_dir_name = $sub_dir_name;
	}


	/**
	 * 画像保存
	 *
	 * @access		public
	 * @param		$param			パラメータ
	 * @return		$path			画像ファイル名
	 */
	public function save_image($param)
	{

		$img_name = "";
		$md = $this->sub_dir_name;

		if ($this->sub_dir_name) {
			// 画像保存先
			$image_path = $this->img_path_root . $md . "/";
		}

		// ディレクトリ確認
		if (!file_exists($image_path)) {
			$res = mkdir($image_path, 0777, TRUE);
		}

		// ランダムファイル名
		$random_name = $this->_makeRandStr(8);

		// 拡張子
		$ext = "";
		$param['extension'] = strtolower($param['type']);

		if ($param['extension'] = "jpg") {
			$ext = ".jpg";
		} else if ($param['extension'] = "jpeg") {
			$ext = ".jpeg";
		} else if ($param['extension'] = "png") {
			$ext = ".png";
		} else if ($param['extension'] = "tif") {
			$ext = ".tif";
		} else if ($param['extension'] = "heic") {
			$ext = ".heic";
		} else {
			$ext = ".jpg";
		}

		// アップロードファイル名
		$upload_path = sprintf("%s%s%s", $image_path, $random_name, $ext);

		$img_name = sprintf("%s/%s%s", $md, $random_name, $ext);

		// ファイル保存
		$res = 0;
		$fileData = base64_decode($param['base64']);
		$res = file_put_contents($upload_path, $fileData);

		if ($res == 0) {
			// TODO:エラー
			return "";
		}

		return $img_name;
	}

	/**
	 * ランダム文字列作成
	 *
	 * @access		public
	 * @param		$length		文字数
	 * @return		ランダム文字列
	 */
	private function _makeRandStr($length)
	{
		$str = array_merge(range('a', 'z'), range('0', '9'), range('A', 'Z'));
		$r_str = null;
		for ($i = 0; $i < $length; $i++) {
			$r_str .= $str[rand(0, count($str) - 1)];
		}
		return $r_str;
	}
}
