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

		//image_libライブラリをロード　補助的なクラス作成　https://codeigniter.jp/user_guide/3/general/ancillary_classes.html
		//クラスをコントローラから切り離して作りつつも CodeIgniter のあらゆるリソースを利用したい場合にget_instance()を使用
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
	 * 画像保存(リサイズ処理も含む)
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

		//返り値の連想配列
		$param = [
			'full_path' => "",
			'thumb_path' => "",
			'error_info' => ""
		];

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
			$this->file_name = $random_name;
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
			$this->type = $type;
		}

		//パス、ファイル名、拡張子からフルパスを作成
		$file_path = $image_path . $this->file_name . "." . $this->type;
		$file_name = $this->file_name . "." . $this->type;

		// 同一ファイル名があるか判定。同一ファイル名があれば連番を付ける
		$new_name = $this->unique_filename($file_path);

		//もしも同一ファイル名があった場合、ファイル名を更新
		if (!($new_name === $file_path)) {
			$name_info = pathinfo($new_name);
			$this->file_name = $name_info['filename'];
			$file_path = $image_path . $this->file_name . "." . $this->type;
			$file_name = $this->file_name . "." . $this->type;
		}

		// ファイル保存
		$res = 0;
		$res = file_put_contents($file_path, $decode_Data);

		if ($res === 0) {
			// TODO:エラー
			$param['error_info'] = 'オリジナル画像保存失敗';
			return $param;
		}

		// --------------------------------------------------
		//画像リサイズ処理($configがFAlSEならアップロード処理のみ)
		// --------------------------------------------------
		if (!$config) {
			$param['full_path'] = $file_path;
			return $param;
		} else {
			//gd2ライブラリ読み込み
			$config['image_library'] = 'gd2';
			//処理を施すもとになる画像の ファイル名/パス を指定。パスは、URLではなく、サーバの相対、または、絶対パスを指定する必要。
			$config['source_image'] = $file_path;
			//FALSEだとリサイズ処理した画像のみ保存(サムネの保存先パス返り値設定処理)
			if ($config['create_thumb']) {
				//new_imageにパスを指定すると、 そのパスにリサイズした画像を保存できる。
				if (empty($config['new_image'])) {
					if (empty($config['thumb_marker'])) {
						//何も設定がされてないサムネパス;
						$thumb_path = $image_path . $this->file_name . '_thumb' . "." . $this->type;
					} else {
						//サムネ名を指定したサムネパス;
						$thumb_path = $image_path . $this->file_name . $config['thumb_marker'] . "." . $this->type;
					}
				} else {
					if (empty($config['thumb_marker'])) {
						//新しい保存場所を指定しているサムネパス;
						$thumb_path = $config['new_image'] . $this->file_name . '_thumb' . "." . $this->type;
					} else {
						//新しい保存場所を指定してサムネ名も指定しているサムネパス;
						$thumb_path = $config['new_image'] . $this->file_name . $config['thumb_marker'] . "." . $this->type;
					}
				}
				$param['full_path'] = $file_path;
				$param['thumb_path'] = $thumb_path;
			} else {
				//リサイズした画像のみ保存する処理
				if (empty($config['new_image'])) {
					$resize_path = $file_path;
				} else {
					$resize_path = $config['new_image'] . $file_name;
				}
				$param['thumb_path'] = $resize_path;
			}
			//画像リサイズ処理
			$result = $this->resize($config);
			if ($result) {
				//リサイズエラー
				$param['error_info'] = $result;
			}
		}
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
	 * 画像リサイズ(ライブラリ読み込み)
	 *
	 * @access		public
	 * @param		$path リサイズ元画像パス
	 * @param       $file リサイズ元画像名
	 */
	public function resize($config)
	{
		$this->CI->image_lib->initialize($config);
		if (!$this->CI->image_lib->resize()) {
			//エラーは文字列で返す。
			return $this->CI->image_lib->display_errors();
		} else {
			//エラーは文字列で返すので成功時はFALSEを返す。
			return FALSE;
		}
	}

	/**
	 * 同一ファイル名の有無判定（あれば連番をつける）
	 *
	 * @access      public
	 * @param       $org_path　画像パス
	 * @param       $num    連番
	 */
	public function unique_filename($org_path, $num = 0)
	{
		if ($num > 0) {
			$info = pathinfo($org_path);
			$path = $info['dirname'] . "/" . $info['filename'] . "_" . $num;
			if (isset($info['extension'])) $path .= "." . $info['extension'];
		} else {
			$path = $org_path;
		}
		if (file_exists($path)) {
			$num++;
			//再帰
			return $this->unique_filename($org_path, $num);
		} else {
			return $path;
		}
	}
}
