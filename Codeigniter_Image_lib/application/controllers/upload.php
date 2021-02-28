<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Upload extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('form', 'url'));
        $this->load->library('image_lib');
    }

    public function index()
    {
        $this->load->view('upload_form', array('error' => ' '));
    }

    //http://codeigniter.jp/user_guide/3/libraries/file_uploading.html
    //http://codeigniter.osdn.jp/kenji/ci-ja/user_guide_ja/libraries/file_uploading.html(日本語訳)
    public function do_upload()
    {

        //formから来た値の処理はここから。
        //アップロードを配置するディレクトリへのパス。ディレクトリは書き込み可能である必要があり、パスは絶対パスでも相対パスでもかまわない。
        $config['upload_path'] = './uploads/';
        //アップロードを許可するファイルのタイプに対応するmimeタイプ。
        $config['allowed_types'] = 'gif|jpg|png';
        //ファイルの最大サイズ（キロバイト単位）。制限なしの場合はゼロに設定します。通常、デフォルトでは2 MB（または2048 KB）。
        $config['max_size'] = 500;
        //画像の最大幅（ピクセル単位）。制限なしの場合はゼロに設定。
        $config['max_width'] = 1024;
        //画像の最大の高さ（ピクセル単位）。制限なしの場合はゼロに設定。
        $config['max_height'] = 768;
        //設定されている場合、CodeIgniterはアップロードされたファイルの名前をこの名前に変更。
        //元のfile_nameに拡張子が指定されていない場合は、使用。
        $config['file_name'] = '!!!TEST!!!';
        //TRUEに設定すると、ファイル名はランダムに暗号化された文字列に変換。
        // $config['encrypt_name'] = TRUE;
        //ファイルアップロードライブラリの読み込み
        $this->load->library('upload', $config);

        /** 
         * do_uploadはセットした設定項目に従って、ファイルのアップロードが実行。 
         * Note: デフォルトでは、フォームで userfile という名前のフォームフィールドにファイルのデータが設定されるものとしてアップロード処理が実行。
         * また、フォームは multipart タイプである必要があります:
         */
        if (!$this->upload->do_upload('userfile')) {
            $error = array('error' => $this->upload->display_errors());

            $this->load->view('upload_form', $error);
        } else {
            $data = array('upload_data' => $this->upload->data());

            //resize
            $this->resize($data['upload_data']['full_path'], $data['upload_data']['file_name']);
            var_dump($data);
            $this->load->view('upload_success', $data);
        }
    }

    //https://codeigniter.jp/user_guide/3/libraries/image_lib.html
    //http://codeigniter.osdn.jp/kenji/ci-ja/user_guide_ja/libraries/image_lib.html(日本語訳)

    //ファイルの場所を示すパス(ファイルの名前を含む)とファイルの名前を受け取る
    public function resize($path, $file)
    {
        $config['image_library'] = 'gd2';
        //処理を施すもとになる画像の ファイル名/パス を指定します。パスは、URLではなく、サーバの相対、または、絶対パスを指定する必要があります。
        $config['source_image'] = $path;
        //画像処理メソッドに、サムネイルを作成するかどうかを設定します。FALSEにするとリサイズされた画像のみ保存される。
        $config['create_thumb'] = TRUE;
        //リサイズされるときや、固定の値を指定したとき、もとの画像のアスペクト比を維持するかどうかを指定します。
        $config['maintain_ratio'] = TRUE;
        //サムネイルの識別子を指定します。ここで指定したものが拡張子の直前に挿入されます。mypic.jpg の場合はmypic_thumb.jpg になります。
        // $config['thumb_marker'] = '_kanemoto';
        $config['width'] = 150;
        $config['height'] = 75;
        //次の設定項目にパスまたは新しいファイル名(あるいはその両方)を指定すると、 リサイズメソッドでは画像ファイルのコピーが作成されます(元画像はそのまま保存されます):
        $config['new_image'] = './uploads/' . $file;

        $this->image_lib->initialize($config);
        $this->image_lib->resize();
    }


    public function base64()
    {
        //base64テスト https://techacademy.jp/magazine/47478
        $post = $this->input->post(NULL, FALSE);
        $fileData = base64_decode($post['original_image']);
        // finfo_bufferでMIMEタイプを取得
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_buffer($finfo, $fileData);

        var_dump($mime_type);
        //MIMEタイプをキーとした拡張子の配列
        $extensions = [
            'image/gif' => 'gif',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/tif' => 'tif',
            'image/heic' => 'heic'
        ];
        //MIMEタイプから拡張子を選択してファイル名を作成
        $filename = './save_image/' . 'base64_image3.' . $extensions[$mime_type];

        file_put_contents($filename, $fileData);
    }

    //オリジナルライブラリ
    public function base64mk()
    {
        $post = $this->input->post(NULL, FALSE);
        $fileData = $post['original_image'];
        // --------------------------------------------------
        // 画像書き込みライブラリ
        // --------------------------------------------------
        $this->load->library('image_func');

        // 月日ディレクトリ
        $md = date("md");

        // 画像保存サブディレクトリ
        $this->image_func->sub_dir_name($md);

        // ファイル名指定（指定しないとランダム名）
        // $this->image_func->set_file_name('TEST!!!!!!いらすとや');

        // オリジナル画像バイナリデータ
        $image_path = $this->image_func->save_image($fileData);

        //デバック用
        echo 'ライブラリ(オリジナル画像)実行結果:';
        var_dump($image_path);
        echo '<br>';
    }
}