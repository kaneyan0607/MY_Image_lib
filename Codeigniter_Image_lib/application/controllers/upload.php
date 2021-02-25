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

    public function do_upload()
    {
        $config['upload_path']          = './uploads/';
        $config['allowed_types']        = 'gif|jpg|png';
        $config['max_size']             = 500;
        $config['max_width']            = 1024;
        $config['max_height']           = 768;

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('userfile')) {
            $error = array('error' => $this->upload->display_errors());

            $this->load->view('upload_form', $error);
        } else {
            $data = array('upload_data' => $this->upload->data());

            //resize
            $this->resize($data['upload_data']['full_path'], $data['upload_data']['file_name']);

            $this->load->view('upload_success', $data);
        }
    }

    public function resize($path, $file)
    {
        $config['image_library'] = 'gd2';
        $config['source_image'] = $path;
        $config['create_thumb'] = TRUE;
        $config['maintain_ratio'] = TRUE;
        $config['width'] = 150;
        $config['height'] = 75;
        $config['new_image'] = './uploads/' . $file;

        $this->image_lib->initialize($config);
        $this->image_lib->resize();
    }
}
