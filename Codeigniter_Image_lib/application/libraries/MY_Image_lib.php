<?php //if (!defined('BASEPATH')) exit('No direct script access allowed');

defined('BASEPATH') or exit('No direct script access allowed');


class MY_Image_lib extends CI_Image_lib
{
    // /**
    //  * @var int
    //  */
    // private $rounds;

    // /**
    //  * @var string
    //  */
    // private $img_path_root;
    // private $img_path;
    // private $sub_dir_name;

    // /**
    //  * @var int|string|null
    //  */
    // private $randomState;

    // /**
    //  * コンストラクタ
    //  *
    //  * @param array $params
    //  *
    //  * @throws Exception
    //  */
    // public function __construct()
    // {

    //     $config = &get_config();

    //     // 画像保存先
    //     $this->img_path_root = $config['img_path_root'];

    //     // 画像保存先
    //     $this->img_path = $config['img_path'];

    //     $this->sub_dir_name = "";
    // }

    // /**
    //  * 画像保存先設定処理
    //  *
    //  * @access		public
    //  * @param		$img_path_root	保存先ディレクトリ名
    //  * @param		$img_path		サブディレクトリ名
    //  * @return		-
    //  */
    // public function set_image_path($img_path_root, $img_path = "")
    // {
    //     // ここが呼ばれない場合は定義値を参照
    //     $this->img_path_root = $img_path_root;
    //     $this->img_path = $img_path;
    // }

    // /**
    //  * ディレクトリ設定処理
    //  *
    //  * @access		public
    //  * @param		$sub_dir_name	サブディレクトリ名
    //  * @return		-
    //  */
    // public function dir_name($sub_dir_name)
    // {
    //     $this->sub_dir_name = $sub_dir_name;
    // }


    // /**
    //  * 画像保存
    //  *
    //  * @access		public
    //  * @param		$param			パラメータ
    //  * @return		$path			画像ファイル名
    //  */
    // public function save_image($param)
    // {

    //     $img_name = "";
    //     $md = $this->sub_dir_name;

    //     if ($this->sub_dir_name) {
    //         // 画像保存先
    //         $image_path = $this->img_path_root . $md . "/";
    //     }

    //     // ディレクトリ確認
    //     if (!file_exists($image_path)) {
    //         $res = mkdir($image_path, 0777, TRUE);
    //     }

    //     // ランダムファイル名
    //     $random_name = $this->_makeRandStr(8);

    //     // 拡張子
    //     $ext = "";
    //     $param['extension'] = strtolower($param['type']);

    //     if ($param['extension'] = "jpg") {
    //         $ext = ".jpg";
    //     } else if ($param['extension'] = "jpeg") {
    //         $ext = ".jpeg";
    //     } else if ($param['extension'] = "png") {
    //         $ext = ".png";
    //     } else if ($param['extension'] = "tif") {
    //         $ext = ".tif";
    //     } else if ($param['extension'] = "heic") {
    //         $ext = ".heic";
    //     } else {
    //         $ext = ".jpg";
    //     }

    //     // アップロードファイル名
    //     $upload_path = sprintf("%s%s%s", $image_path, $random_name, $ext);

    //     $img_name = sprintf("%s/%s%s", $md, $random_name, $ext);

    //     // ファイル保存
    //     $res = 0;
    //     $fileData = base64_decode($param['base64']);
    //     $res = file_put_contents($upload_path, $fileData);

    //     if ($res == 0) {
    //         // TODO:エラー
    //         return "";
    //     }

    //     return $img_name;
    // }

    // /**
    //  * ランダム文字列作成
    //  *
    //  * @access		public
    //  * @param		$length		文字数
    //  * @return		ランダム文字列
    //  */
    // private function _makeRandStr($length)
    // {
    //     $str = array_merge(range('a', 'z'), range('0', '9'), range('A', 'Z'));
    //     $r_str = null;
    //     for ($i = 0; $i < $length; $i++) {
    //         $r_str .= $str[rand(0, count($str) - 1)];
    //     }
    //     return $r_str;
    // }

    // public function resize($path, $file)
    // {
    //     $config['image_library'] = 'gd2';
    //     //処理を施すもとになる画像の ファイル名/パス を指定します。パスは、URLではなく、サーバの相対、または、絶対パスを指定する必要があります。
    //     $config['source_image'] = $path;
    //     //画像処理メソッドに、サムネイルを作成するかどうかを設定します。FALSEにするとリサイズされた画像のみ保存される。
    //     $config['create_thumb'] = TRUE;
    //     //リサイズされるときや、固定の値を指定したとき、もとの画像のアスペクト比を維持するかどうかを指定します。
    //     $config['maintain_ratio'] = TRUE;
    //     //サムネイルの識別子を指定します。ここで指定したものが拡張子の直前に挿入されます。mypic.jpg の場合はmypic_thumb.jpg になります。
    //     // $config['thumb_marker'] = '_kanemoto';
    //     $config['width'] = 150;
    //     $config['height'] = 75;
    //     //次の設定項目にパスまたは新しいファイル名(あるいはその両方)を指定すると、 リサイズメソッドでは画像ファイルのコピーが作成されます(元画像はそのまま保存されます):
    //     $config['new_image'] = './uploads/' . $file;

    //     $this->initialize($config);
    //     $this->resize();
    // }
}
