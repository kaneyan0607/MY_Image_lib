<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Upload extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper(array('form', 'url'));
        $this->load->library('image_lib');
        date_default_timezone_set('Asia/Tokyo');
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
        $config['file_name'] = '!!!TEST!!!確認';
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
        $config['create_thumb'] = FALSE;
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


    //ただのbase64保存テスト
    public function base64()
    {
        //base64テスト https://techacademy.jp/magazine/47478
        $post = $this->input->post(NULL, FALSE);
        $fileData = base64_decode($post['original_image']);
        // finfo_bufferでMIMEタイプを取得
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_buffer($finfo, $fileData);

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

        $result = file_put_contents($filename, $fileData);

        if (!$result) {
            echo '失敗';
        } else {
            echo '成功';
        }
    }

    // --------------------------------------------------
    //オリジナルライブラリ(一つのライブラリの中で画像をリサイズする)
    // --------------------------------------------------
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

        // 画像保存サブディレクトリ(指定しないとディレクトリ直下)
        $this->image_func->sub_dir_name($md);

        // ファイル名指定（指定しないとランダム名）
        $this->image_func->set_file_name('irasutoya!test29');

        //拡張子（指定しないと適したものを自動取得）
        // $this->image_func->file_type('jpg');

        //画像リサイズ処理
        //画像処理メソッドに、サムネイルを作成するかどうかを設定します。FALSEにするとリサイズされた画像のみ保存される。
        $config['create_thumb'] = TRUE;
        //リサイズされるときや、固定の値を指定したとき、もとの画像のアスペクト比を維持するかどうかを指定します。
        $config['maintain_ratio'] = TRUE;
        //サムネイルの識別子を指定します。ここで指定したものが拡張子の直前に挿入されます。mypic.jpg の場合はmypic_thumb.jpg になります。
        // $config['thumb_marker'] = '_kanemoto';
        $config['width'] = 150;
        $config['height'] = 75;
        //次の設定項目にパスまたは新しいファイル名(あるいはその両方)を指定すると、 リサイズメソッドでは画像ファイルのコピーが作成されます(元画像はそのまま保存されます):
        // $config['new_image'] = './upload3/';

        // オリジナル画像バイナリデータ(画像アップロード、リサイズ作業。※configに値が無ければ画像アップロードのみ)
        $image_path = $this->image_func->save_image($fileData, $config);

        //デバック用
        echo 'ライブラリ(オリジナル画像)実行結果:';
        var_dump($image_path);
        echo '<br>';
    }

    // --------------------------------------------------
    //オリジナルライブラリ2（リサイズはコントローラーで再度ライブラリを読み込み処理する）
    // --------------------------------------------------
    public function base64mk2()
    {
        $post = $this->input->post(NULL, FALSE);
        $fileData = $post['original_image'];
        // --------------------------------------------------
        // 画像書き込みライブラリ
        // --------------------------------------------------
        $this->load->library('image_func2');

        // 月日ディレクトリ
        $md = date("md");

        // 画像保存サブディレクトリ(指定しないと定義値のディレクトリ直下)
        $this->image_func2->sub_dir_name($md);

        // ファイル名指定（指定しないとランダム名）
        $this->image_func2->set_file_name('irasutoya!test17');

        //拡張子（指定しないと適したものを自動取得）
        // $this->image_func2->file_type('jpg');

        // オリジナル画像バイナリデータ(画像アップロード作業)
        $image_path = $this->image_func2->save_image2($fileData);

        if ($image_path === FALSE) {
            echo 'アップロード失敗';
        } else {
            //デバック用
            echo 'ライブラリ(オリジナル画像)実行結果:';
            var_dump($image_path);
            echo '<br>';

            //画像リサイズ
            //resize
            $this->resize2($image_path);
        }
    }

    //画像リサイズ2
    //ファイルの場所を示すパス(ファイルの名前を含む)とファイルの名前を受け取る
    public function resize2($image_path)
    {
        $config['image_library'] = 'gd2';
        //処理を施すもとになる画像の ファイル名/パス を指定します。パスは、URLではなく、サーバの相対、または、絶対パスを指定する必要があります。
        $config['source_image'] = $image_path['full_path'];
        //画像処理メソッドに、サムネイルを作成するかどうかを設定します。FALSEにするとリサイズされた画像のみ保存される。
        $config['create_thumb'] = TRUE;
        //リサイズされるときや、固定の値を指定したとき、もとの画像のアスペクト比を維持するかどうかを指定します。
        $config['maintain_ratio'] = TRUE;
        //サムネイルの識別子を指定します。ここで指定したものが拡張子の直前に挿入されます。mypic.jpg の場合はmypic_thumb.jpg になります。
        // $config['thumb_marker'] = '_kanemoto';
        $config['width'] = 150;
        $config['height'] = 75;
        //次の設定項目にパスまたは新しいファイル名(あるいはその両方)を指定すると、 リサイズメソッドでは画像ファイルのコピーが作成されます(元画像はそのまま保存されます):
        // $config['new_image'] = './upload2/' . $file;

        echo 'リサイズ処理の結果:';
        $this->image_lib->initialize($config);
        $result = $this->image_lib->resize();
        var_dump($result);
        echo '<br>';

        if (empty($config['new_image'])) {
            if (empty($config['thumb_marker'])) {
                echo 'サムネパス1';
                $thumb_path = $image_path['image_path'] . $image_path['base_name'] . '_thumb' . "." . $image_path['type'];
                var_dump($thumb_path);
            } else {
                echo 'サムネパス2';
                $thumb_path = $image_path['image_path'] . $image_path['base_name'] . $config['thumb_marker'] . "." . $image_path['type'];
                var_dump($thumb_path);
            }
            //もしも新しい保存場所にサムネを保存してた場合
        } else {
            if (empty($config['thumb_marker'])) {
                echo 'サムネパス3';
                $thumb_path = $config['new_image'] . '_thumb' . "." . $image_path['type'];
                var_dump($thumb_path);
            } else {
                echo 'サムネパス4';
                $thumb_path = $config['new_image'] . $config['thumb_marker'] . "." . $image_path['type'];
                var_dump($thumb_path);
            }
        }
    }
}
