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

	// ベース名
	private $base_name;

	// 拡張子
	private $type;

	// ディレクトリ名
	private $dir_name;

	// サブディレクトリ名
	private $subdir_name;

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

		// ディレクトリ名
		$this->dir_name = $config['dir_name'];

		// サブディレクトリ名
		$this->subdir_name = '';
	}

	/**
	 * ファイル設定処理
	 *
	 * @access		public
	 * @param		$base_name	ベース名
	 * @param		$type	拡張子
	 * @return		-
	 */
	public function set_file_name($base_name, $type)
	{
		$this->base_name = $base_name;
		$this->type = $type;
	}

	/**
	 * サブディレクトリ名設定処理
	 *
	 * @access		public
	 * @param		$subdir_name	ベース名
	 * @return		-
	 */
	public function set_subdir_name($subdir_name)
	{
		$this->subdir_name = $subdir_name;
	}

	/**
	 * ディレクトリ名設定処理
	 *
	 * @access		public
	 * @param		$subdir_name	サブディレクトリ名
	 * @return		-
	 */
	public function set_dir_name($dir_name)
	{
		$this->dir_name = $dir_name;
	}


	/**
	 * 画像保存 (先に画像保存した画像のファイル名を継承して画像保存したい場合は、param['sub_name']に値を入れる。)
	 *
	 * @access		public
	 * @param		$param			パラメータ
	 * @return		$path			画像ファイル名
	 */
	public function save_image($param)
	{

		$img_name = '';
		$md = $this->subdir_name;

		if ($this->subdir_name) {
			// 画像保存先
			$dir_path = $this->dir_name . '/' . $md . '/';
		}

		// ディレクトリ確認
		if (!file_exists($dir_path)) {
			$res = mkdir($dir_path, 0777, TRUE);
		}

		//メンバ変数とサブネームに値があるか確認。
		if (empty($this->base_name && $param['sub_name'])) {
			if (isset($param['serial_no']) && !empty($param['serial_no'])) {
				// ランダムベース名 +　連番
				$base_name = $this->_makeRandStr(8) . $param['serial_no'];
			} else {
				// ランダムベース名
				$base_name = $this->_makeRandStr(8);
			}
		} else {
			$base_name = $this->base_name . $param['sub_name'];
		}

		// 拡張子
		$ext = '';
		$param['extension'] = strtolower($param['type']);

		if ($param['extension'] = 'jpg') {
			$ext = '.jpg';
		} else if ($param['extension'] = 'jpeg') {
			$ext = '.jpeg';
		} else if ($param['extension'] = 'png') {
			$ext = '.png';
		} else if ($param['extension'] = 'tif') {
			$ext = '.tif';
		} else if ($param['extension'] = 'heic') {
			$ext = '.heic';
		} else {
			$ext = '.jpg';
		}

		// アップロードファイル名
		$image_path = sprintf('%s%s%s', $dir_path, $base_name, $ext);

		$img_name = sprintf('%s/%s%s', $md, $base_name, $ext);

		// ファイル保存
		$res = 0;
		$fileData = base64_decode($param['original_image']);
		$res = file_put_contents($image_path, $fileData);

		if ($res == 0) {
			// TODO:エラー
			return '';
		}

		$this->set_file_name($base_name, $ext);
		return $image_path;
	}

	/**
	 * 画像リサイズ
	 *
	 * @access		public
	 * @param		$p_width	画像幅
	 * @param		$p_height	画像高さ
	 * @return		array $result	サムネ画像情報
	 */
	public function resize_image($p_width, $p_height)
	{
		if (!empty($this->subdir_name)) {
			// 画像元URL
			$relative_path = $this->dir_name . '/' . $this->subdir_name . '/' . $this->base_name . $this->type;
		} else {
			// サブディレクトリ名指定ない場合
			$relative_path = $this->dir_name . '/' . $this->base_name . $this->type;
		}

		// ベース名	
		$base_name = $this->base_name;
		// 拡張子
		$type = $this->type;

		// 画像サイズ取得
		list($width, $hight) = getimagesize($relative_path);

		// 新しい画像をファイルあるいは URL から作成する
		$baseImage = imagecreatefromjpeg($relative_path);

		// サイズを指定して新しい画像のキャンバスを作成
		$image = imagecreatetruecolor($p_width, $p_height);

		// 画像のコピーと伸縮（コピー先, コピー元, コピー先のx座標, コピー先のy座標, コピー元のx座標, コピー元のy座標, コピー先の幅, コピー先の高さ, コピー元の幅, コピー元の高さ）
		imagecopyresampled($image, $baseImage, 0, 0, 0, 0, $p_width, $p_height, $width, $hight);

		// 重ねる透過背景色作成

		// サイズを指定して新しい画像のキャンバスを作成
		$image_backcolor = imagecreatetruecolor($p_width, $p_height);
		// 画像で使用する色を透過度を指定して作成する
		$backcolor = imagecolorallocatealpha($image_backcolor, 255, 182, 193, 70);
		// 塗り潰す
		imagefill($image_backcolor, 0, 0, $backcolor);

		imageLayerEffect($image, IMG_EFFECT_ALPHABLEND); // 合成する際、透過を考慮する
		imagecopy($image, $image_backcolor, 0, 0, 0, 0, $p_width, $p_height); // 合成する

		imagedestroy($image_backcolor); // 破棄

		$thumbnail_path = str_replace($base_name . $type, $base_name . '_thumbnail' . $type, $relative_path);

		// 画像をブラウザあるいはファイルに出力する（画像リソース, ファイル保存先パスあるいはオープン中のリソース）
		$check = imagejpeg($image, $thumbnail_path);

		if ($check) {
			$result['original_url'] = base_url() . $relative_path;
			$result['original_path'] = $relative_path;
			$result['thumbnail_url'] = base_url() . $thumbnail_path;
			$result['thumbnail_path'] = $thumbnail_path;
			// $result['thumbnail_image'] = base64_encode(file_get_contents($thumbnail_dir_path));

			return $result;
		}
		return FALSE;
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
