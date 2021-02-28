<?php //if (!defined('BASEPATH')) exit('No direct script access allowed');
//https://kunitsuji.hatenadiary.org/entry/20090717/1247816823

defined('BASEPATH') or exit('No direct script access allowed');


class MY_Image_lib extends CI_Image_lib
{

    private $CI;

    private $upload_base   = 'uploads/';
    private $max_size      = '500';        //500KByteまで
    private $max_width     = '1024';      //1024px
    private $max_height    = '1024';      //1024px
    private $overwrite     = TRUE;        //上書きするか
    private $allowed_types = 'gif|jpg|png';

    public  $upload_error_message = ''; //エラーがあった場合、エラーメッセージを格納する

    private $old_filename = '';       //オリジナルのファイル名
    private $new_filename = '';       //生成された新しいファイル名

    public  $upload_filename = array();  //アップロードされたファイル名を格納する配列
    /**
     * Constructor
     *
     * @access  public
     * @param   string
     * @return  void
     */
    public function __construct($params = array())
    {
        parent::__construct();

        if (count($params) > 0) {
            $this->initialize($params);
        }

        //file upload classをロード
        $this->CI = &get_instance();

        //log_message('debug', "Image Lib Class Initialized");
    }

    // --------------------------------------------------------------------

    /**
     * ファイルアップロードクラスをロードする
     * 必須はupload_path モジュール名以下、命名規則に沿って指定
     * 例）日記の場合は、'diary/XXXXX/XXXXX'など
     *
     */
    public function execute($config = array())
    {
        if (isset($config['upload_path'])) {
            $config['upload_path'] = 'PUBPATH' . $this->upload_base . $config['upload_path'];
            if (!$this->_path($config['upload_path'])) {
                return FALSE;
            }
        } else {
            //パスの指定がないものはエラーとする
            $this->upload_error_message = 'ファイルを格納する場所が未定義です。';
            return FALSE;
        }

        if (!isset($config['allowed_types'])) {
            $config['allowed_types'] = $this->allowed_types;
        }

        if (!isset($config['max_size'])) {
            $config['max_size']      = $this->max_size;
        }

        if (!isset($config['max_width'])) {
            $config['max_width']     = $this->max_width;
        }

        if (!isset($config['max_height'])) {
            $config['max_height']    = $this->max_height;
        }

        if (!isset($config['overwrite'])) {
            $config['overwrite']     = $this->overwrite;
        }

        $this->CI->load->library('upload', $config);
    }

    /**
     * 指定のフィールド名のファイルがアップロードされているかどうかを返却
     * @params  string  field name
     * @params  bool    TRUE or FALSE
     *
     */
    public function is_upload($name)
    {

        if ($this->CI->upload->do_upload($name)) {
            //変更箇所（is_uploaded → do_upload)
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /**
     * 保存ディレクトリが存在しているかを調査する
     *
     *
     */
    private function _path($path)
    {
        if (is_dir($path)) {
            if (is_writable($path) !== TRUE) {
                $this->upload_error_message = 'ファイルを書き込む権限がありません。';
                return FALSE;
            } else {
                return TRUE;
            }
        } else {
            //存在していない場合は、その場所を作成する
            if (mkdir($path, 0777, TRUE)) {
                return TRUE;
            }
            $this->upload_error_message = 'ファイルを格納する場所が作成できません。';
            return FALSE;
        }
    }

    /**
     * 画像を複数枚にコンバートする。変換する画像のサイズを配列で受け取る。
     * array('120', 180', '76',50)の場合、それぞれのサイズにリサイズする
     * オリジナル画像を保存する場合は、リネームして新しく保存する
     * @params  string field name
     * @params  array  convert size
     * @params  bool   original image deleted default FALSE
     * @params  bool   original image renamed default TRUE
     *
     */
    public function convert_image($column, $option, $deleted = FALSE, $rename = TRUE)
    {
        if (!$column) {
            return FALSE;
        }
        if (!$option) {
            return '';      //何もしない
        }

        if (!is_array($option)) {
            $option = (array)$option;
        }

        $this->new_filename = '';
        $this->old_filename = '';

        if (!$this->CI->upload->do_upload($column)) {
            $this->upload_error_message = $this->CI->upload->display_errors();
            return FALSE;
        } else {
            //ファイル名を生成
            $this->_filename();

            $image_data = $this->CI->upload->data($column);

            //元のサイズを保存する場合（縮小なしで保存）
            if ($rename) {
                $option[] = '';
            }

            foreach ($option as $val) {
                if (!$this->_image_risize($image_data, $val)) {
                    return FALSE;
                }
                $this->upload_filename[$column]['new_filename'] = $this->new_filename;
                $this->upload_filename[$column]['old_filename'] = $this->old_filename;
            }

            if ($deleted) {
                unlink($image_data['full_path']);
            }

            return TRUE;
        }
    }

    private function _filename()
    {
        $u_id = $this->CI->session->userdata('user_id');

        $filename_prefix = date('Y') . date('m') . $u_id;
        $file_token = md5(uniqid($filename_prefix));

        $this->file_token = $file_token;
    }

    private function _image_risize($img_data, $size = '')
    {
        //サイズが指定されていない場合は、ファイルそのものをコピーする
        if (!$size) {
            if (!copy($img_data['full_path'], $img_data['file_path'] . $this->file_token . $img_data['file_ext'])) {
                $this->upload_error_message = 'ファイルのコピーに失敗しました。';
                return FALSE;
            } else {
                return TRUE;
            }
        } else {
            //画像操作
            $img_conf['image_library']  = 'gd2';
            $img_conf['source_image']   = $img_data['full_path'];
            $img_conf['create_thumb']   = FALSE;
            $img_conf['maintain_ratio'] = TRUE;
            $img_conf['new_image']      = $img_data['file_path'] . $this->file_token . $img_data['file_ext'];

            $s_width  = $img_data['image_width'];
            $s_height = $img_data['image_height'];
            $w = $size;
            $h = $size;
            if (!$w) $w = $s_width;
            if (!$h) $h = $s_height;
            $image_resize = $this->_create_image_size($s_width, $s_height, $w, $h);

            //指定サイズで処理
            $img_conf['width']  = $image_resize['width'];
            $img_conf['height'] = $image_resize['height'];
            $img_conf['new_image'] = $img_data['file_path'] . $size . '_' . $this->file_token . $img_data['file_ext'];
            $this->initialize($img_conf);
            if ($this->resize()) {
                $this->new_filename = $this->file_token . $img_data['file_ext'];
                $this->old_filename = $img_data['file_name'];
                return TRUE;
            } else {
                $this->clear();
                $this->upload_error_message = '画像リサイズ時にエラーが発生しました。';
                return FALSE;
            }
        }
    }

    private function _create_image_size($s_w, $s_h, $w, $h)
    {
        // リサイズの必要がない場合
        if ($s_w <= $w && $s_h <= $h) {
            //そのまま
            $img_resize['width']  = $s_w;
            $img_resize['height'] = $s_h;
        } else {
            // 出力サイズ変更
            $o_width  = $s_w;
            $o_height = $s_h;

            if ($w < $s_w) {
                $o_width  = $w;
                $o_height = $s_h * $w / $s_w;
                if ($o_height < 1) {
                    $o_height = 1;
                }
            }
            if ($h < $o_height && $h < $s_h) {
                $o_width  = $s_w * $h / $s_h;
                $o_height = $h;
                if ($o_width < 1) {
                    $o_width = 1;
                }
            }

            $img_resize['width']  = $o_width;
            $img_resize['height'] = $o_height;
        }

        return $img_resize;
    }
}
// END MYNETS_Image_lib Class

/* End of file MYNETS_Image_lib.php */
/* Location: ./system/mynets/libraries/MYNETS_Image_lib.php */