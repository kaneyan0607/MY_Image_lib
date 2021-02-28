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

	//ファイル名
	private $file_name;

	//ディレクトリ名
	private $img_path_root;

	//サブディレクトリ名
	private $sub_dir_name;

	//拡張子
	private $type;

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

		//サブディレクトリ
		$this->sub_dir_name = "";

		//拡張子
		$this->type = "";

		//ファイル名
		$this->file_name = "";

		//image_libライブラリをロード
		$this->CI = &get_instance();
	}

	/**
	 * ファイル設定名処理
	 *
	 * @access		public
	 * @param		$file_name	ファイル名
	 * @return		-
	 */
	public function set_file_name($file_name)
	{
		$this->file_name = $file_name;
	}

	/**
	 * 画像保存先ディレクトリ設定処理
	 *
	 * @access		public
	 * @param		$img_path_root	保存先ディレクトリ名
	 * @return		-
	 */
	public function set_image_path($img_path_root)
	{
		// ここが呼ばれない場合は定義値を参照
		$this->img_path_root = $img_path_root;
	}

	/**
	 * サブディレクトリ設定処理
	 *
	 * @access		public
	 * @param		$sub_dir_name	サブディレクトリ名
	 * @return		-
	 */
	public function sub_dir_name($sub_dir_name)
	{
		// ここが呼ばれない場合は定義値を参照（空白なのでサブディレクトリが作成されない）
		$this->sub_dir_name = $sub_dir_name;
	}

	/**
	 * 拡張子設定処理
	 *
	 * @access		public
	 * @param		$type	拡張子
	 * @return		-
	 */
	public function file_type($type)
	{
		// ここが呼ばれない場合は拡張子を取得してここを呼ぶ
		$this->type = $type;
	}


	/**
	 * 画像保存
	 *
	 * @access		public
	 * @param		$fileData		パラメータ
	 * @param		$config			リサイズ処理
	 * @return		$path			画像ファイル名
	 */
	public function save_image($fileData, $config = FALSE)
	{

		//ファイルのデコード
		$decode_Data = base64_decode($fileData);

		//画像保存先（サブディレクトリの指定が無い場合は空白がくる）
		if ($this->sub_dir_name === "") {
			$image_path = $this->img_path_root . "/";
		} else {
			$md = $this->sub_dir_name;
			$image_path = $this->img_path_root . "/" . $md . "/";
		}

		// ディレクトリ確認
		if (!file_exists($image_path)) {
			$res = mkdir($image_path, 0777, TRUE);
		}

		//もしもファイル名の指定が無ければランダムファイル名を命名
		if ($this->file_name === "") {
			// ランダムファイル名
			$random_name = $this->_makeRandStr(8);
			$this->set_file_name($random_name);
		}

		// 拡張子判定
		//もしも拡張子の指定がなければ拡張子を取得
		if ($this->type === "") {
			// finfo_bufferでMIMEタイプを取得
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mime_type = finfo_buffer($finfo, $decode_Data);

			//MIMEタイプをキーとした拡張子の配列
			$extensions = [
				'image/gif' => 'gif',
				'image/jpeg' => 'jpg',
				'image/png' => 'png',
				'image/tif' => 'tif',
				'image/heic' => 'heic'
			];

			$type = $extensions[$mime_type];
			$this->file_type($type);
		}

		//パス、ファイル名、拡張子からフルパスを作成
		$file_path = $image_path . $this->file_name . "." . $this->type;
		$file_name = $this->file_name . "." . $this->type;
		// ファイル保存
		$res = 0;
		$res = file_put_contents($file_path, $decode_Data);

		if ($res == 0) {
			// TODO:エラー
			return "";
		}

		if (!($config === FALSE)) {
			//処理を施すもとになる画像の ファイル名/パス を指定します。パスは、URLではなく、サーバの相対、または、絶対パスを指定する必要があります。
			$config['source_image'] = $file_path;

			//次の設定項目にパスまたは新しいファイル名(あるいはその両方)を指定すると、 リサイズメソッドでは画像ファイルのコピーが作成されます(元画像はそのまま保存されます)
			if (!empty($config['new_image'])) {
				$config['new_image'] = $config['new_image'] . $file_name;
			}
			//画像リサイズ
			$this->resize2($config);
			echo '画像リサイズ処理';
		}

		$param = [
			'full_path' => $file_path,
			'file_name' => $file_name,
		];

		return $param;
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


	/**
	 * 画像リサイズ
	 *
	 * @access		public
	 * @param		$path リサイズ元画像パス
	 * @param       $file リサイズ元画像名
	 */
	public function resize2($config)
	{
		$this->CI->image_lib->initialize($config);
		$this->CI->image_lib->resize();
	}
}
