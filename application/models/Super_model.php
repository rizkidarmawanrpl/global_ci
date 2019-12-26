<?php
defined('BASEPATH') OR exit('no direct script access allowed');

class Super_model extends CI_Model
{
    var $gallery_path;

    public function __construct()
    {
        parent::__construct();
    }
	
	public function get_siteconfig($table = 'settings')
	{
		//$this->db->limit(1);
		$query = $this->db->get($table);
		if(!$query) {
            $data['conf_sitetitle']         = '';
            $data['conf_description']       = '';
            //$data['conf_keyword']           = $query->keyword;
            $data['conf_email']             = '';
            $data['conf_logo']              = '';
            $data['conf_footer']            = '© 2019 - Material Design.';
            $data['conf_version']           = '?';
            $data['mail_head']              = '';
            $data['mail_from']              = '';
            $data['mail_footer']            = ' Copyright Y. All Right Reserved.';
            $data['mail_name']              = '';

        } else {
		    $row = $query->row();

            $data['conf_sitetitle']         = $row->sitetitle;
            $data['conf_description']       = $row->description;
            //$data['conf_keyword']           = $query->keyword;
            $data['conf_email']             = $row->alamat_email;
            $data['conf_logo']              = $row->logo;
            $data['conf_footer']            = $row->footer;
            $data['conf_version']           = $row->version;
            $data['mail_head']              = $row->mail_head;
            $data['mail_from']              = $row->mail_from;
            $data['mail_footer']            = $row->mail_footer;
            $data['mail_name']              = $row->mail_name;
        }

        $data['conf_release'] = 'release';

		return ($data);
	}

    public function get_pagination($sql, $baseurl, $limit, $uri_segment, $perpage = '')
    {
        $num_rows = $this->db->query($sql)->num_rows();
        $config['base_url']     = $baseurl;
        $config['total_rows']   = $num_rows;
        $config['per_page']     = $limit;
        $config['uri_segment']  = $uri_segment;

        if($perpage != ''){
            $config['page_query_string']    = TRUE;
            $config['use_page_numbers']     = TRUE;
            $config['query_string_segment'] = $perpage;
        }

        /* style */
        $config['first_link']       = 'First';
        $config['first_tag_open']   = '<li>';
        $config['first_tag_close']  = '<li>';

        $config['last_link']        = 'Last';
        $config['last_tag_open']    = '<li>';
        $config['last_tag_close']   = '<li>';

        $config['full_tag_open']    = '<ul class="pager">';
        $config['full_tag_close']   = '</ul>';

        $config['next_tag_open']    = '<li class="next">';
        $config['next_tag_close']   = '</li>';
        $config['next_link']        = 'Next <span aria-hidden="true">&rarr;</span>';

        $config['prev_tag_open']    = '<li class="previous">';
        $config['prev_tag_close']   = '</li>';
        $config['prev_link']        = '<span aria-hidden="true">&larr;</span> Previous';

        $config['cur_tag_open']     = '<li class="active"><a href="#" class="waves-effect bg-cyan">';
        $config['cur_tag_close']    = '</a></li>';

        $config['num_tag_open']     = '<li>';
        $config['num_tag_close']    = '</li>';

        $config['attributes']       = array('class' => 'waves-effect');

        return $this->pagination->initialize($config);
    }
	
	public function make_captcha($conf = array())
	{
		$this->load->helper('string'); 
		$this->load->helper('captcha');

        $folder     = isset($conf['folder'])? $conf['folder'] : 'uploads';
        $img_width  = isset($conf['img_width'])? $conf['img_width'] : 200;
        $img_height = isset($conf['img_height'])? $conf['img_height'] : 46;
        $font       = isset($conf['font'])? $conf['font'] : 'roboto.ttf';
        $font_size  = isset($conf['font_size'])? $conf['font_size'] : 20;
        $word_len   = isset($conf['word_len'])? $conf['word_len'] : 6;
        $word_type  = isset($conf['word_type'])? $conf['word_type'] : 'numeric';

		$vals = array(
		    'img_path'      => './'.$folder.'/captcha/', // PATH for captcha ( *Must mkdir (htdocs)/captcha ) , kita setting mobile/templates/images/chaptcha
            'img_url'       => base_url().$folder.'/captcha/', // URL for captcha img
            'img_width'     => $img_width, // width
            'img_height'    => $img_height, // height
            'font_path'     => './system/fonts/'.$font,
            'font_size'     => $font_size,
            'expiration'    => 3200 ,
            'word'          => random_string($word_type, $word_len)
        );
		// Create captcha
		$cap = create_captcha( $vals );
		// Write to DB
		if ( $cap ) {
		    $data = array(
                'captcha_time'  => $cap['time'],
                'ip_address'    => $this -> input -> ip_address(),
                'word'          => $cap['word'] ,
			);
		    $query = $this -> db -> insert_string( 'captcha', $data );
		    $this -> db -> query( $query );
		} else {
		    return "Captcha not work" ;
		}

		return $cap['image'] ;
	}
 
	public function check_captcha()
	{
		// Delete old data ( 1hours)
		$expiration = time()-3600 ;
		$sql        = " DELETE FROM captcha WHERE captcha_time < ? ";
		$binds      = array($expiration);
		$query      = $this->db->query($sql, $binds);
		
		//checking input
		$sql        = "SELECT COUNT(*) AS count FROM captcha WHERE word = ? AND ip_address = ? AND captcha_time > ?";
		$binds      = array(@$_POST['captcha'], $this->input->ip_address(), $expiration);
		$query      = $this->db->query($sql, $binds);
		$row        = $query->row();
		
		if ( $row -> count > 0 )
		{
		  return true;
		}
		else {
			return false;
		}
	
	}

    function get_table_simple($select, $table, $where, $order_field = '', $order_method = '', $limit = 10)
    {
        $this->db->select($select);
        $this->db->from($table);
        if($where != NULL){
            $this->db->where($where);
        }
        if($order_field != ''){
            $this->db->order_by($order_field,$order_method);
        }
        if($limit != NULL){
            $this->db->limit($limit);
        }
        $query = $this->db->get();
        return $query;
    }
	
	public function get_table_all($table)
	{
		return $this->db->get($table);
	}
	
	public function get_table_pagination($table, $limit = array())
	{
		if($limit == NULL)
		return $this->db->query("select * from ".$table."")->result();
		else
		return $this->db->query("select * from ".$table." LIMIT ".$limit['offset'].",".$limit['perpage']."")->result();
	}
	
	public function get_table_pencarian($table, $key_field, $keyword)
	{
		$query = $this->db->like($key_field,$keyword)->get($table);
		return $query->result();
	}
	
	public function get_table_where($table, $nama_field, $id_field)
	{
		$this->db->where($nama_field,$id_field);
		$query = $this->db->get($table);
		return $query;
	}
	
	public function get_table_where_array($table, $data)
	{
		$query = $this->db->get_where($table,$data);
		return $query;
	}
	
	public function get_last_id($table, $nama_field, $order_by = 'DESC')
	{
		$this->db->limit(1);
		$this->db->order_by($nama_field,$order_by);
		$query  = $this->db->get($table);
		$result = $query->row();
		if($query->num_rows()>0) {
			$id = $result->$nama_field + 1;
		} else {
			$id = 1;
		}
		return $id;
	}
	
	public function add_table($table, $tambah)
	{
		$query = $this->db->insert($table,$tambah);
		return $query;
	}
	
	public function update_table($table, $nama_field, $id_field, $ubah)
	{
		$query = $this->db->where($nama_field, $id_field)->update($table, $ubah);
		return $query;
	}

	public function update_table_array($table, $where, $ubah)
    {
        $query = $this->db->where($where)->update($table, $ubah);
        return $query;
    }
	
	public function delete_table($table, $nama_field, $id_field)
	{
		$query = $this->db->where($nama_field, $id_field)->delete($table);
		return $query;
	}

	public function delete_table_array($table, $where = array())
    {
        $query = $this->db->where($where)->delete($table);
        return $query;
    }
	
	public function get_type_name_by_id($type, $id_type, $type_id = '', $field = 'nama') {
        $query= $this->db->get_where($type, array($id_type => $type_id))->row();
        if(!empty($query)) {
            $data= $query->$field;
        } else {
            $data= null;
        }
        return $data;
    }

    public function do_upload_file($data_gambar, $folder_gambar, $type = 'doc|docx|pdf|xls|xlsx|ppt|pptx|gif|jpg|png|swf|txt|flv|mp4|mov|mpeg|mp3|avi|zip|rar', $status = '', $resize = array(), $max_size = '500000')
    {
        //session_start();
        //$theme= $_SESSION['theme'];
        $this->gallery_path = realpath(APPPATH . '../'.$folder_gambar);
        $this->load->library('upload');
        $config['upload_path']      = './'.$folder_gambar.'/';
        $config['allowed_types']    = $type;
        $config['max_size']         = $max_size;
        /* encrypt name */
        $config['encrypt_name'] = TRUE;
        $this->upload->initialize($config);

        if($this->upload->do_upload($data_gambar)) {
            $datanya = $this->upload->data('file_name');
            $filename = $datanya;

            $source = $this->upload->data('full_path');
            $new_source = $this->gallery_path;

            if (strtoupper($status) == 'RESIZE') {
                for ($n = 0; $n < count($resize); $n++) {
                    if(isset($resize[$n]['new_source'])) { $new_source .= $resize[$n]['new_source']; }
                    $configs = array('image_library' => 'gd2', 'source_image' => $source, 'new_image' => $new_source, 'maintain_ration' => TRUE, 'height' => $resize[$n]['height'], 'width' => $resize[$n]['width']);
                    $this->load->library('image_lib');
                    $this->image_lib->clear();
                    $this->image_lib->initialize($configs);
                    $this->image_lib->resize();
                }
            }

            return $filename;

        } else {

            $error = $this->upload->display_errors();
            $filename='';
            return $filename;
        }

        unset($config);
    }

    public function do_upload_file_multiple($data_gambar, $folder_gambar, $type = 'doc|docx|pdf|xls|xlsx|ppt|pptx|gif|jpg|png|swf|txt|flv|mp4|mov|mpeg|mp3|avi|zip|rar', $status = '', $resize = array(), $max_size = '500000')
    {
        $number_of_files    = sizeof($_FILES[$data_gambar]['tmp_name']);
        $files              = $_FILES[$data_gambar];
        $errors             = array();

        // we first load the upload library
        $this->load->library('upload');
        // next we pass the upload path for the images
        $config['upload_path']      = FCPATH . $folder_gambar . '/';
        // also, we make sure we allow only certain type of images
        $config['allowed_types']    = $type;
        $config['max_size']         = $max_size;
        $config['encrypt_name']     = TRUE;

        for ($i = 0; $i < $number_of_files; $i++) {

            if ($_FILES[$data_gambar]['error'][$i] == 0) {
                $_FILES[$data_gambar]['name']       = $files['name'][$i];
                $_FILES[$data_gambar]['type']       = $files['type'][$i];
                $_FILES[$data_gambar]['tmp_name']   = $files['tmp_name'][$i];
                $_FILES[$data_gambar]['error']      = $files['error'][$i];
                $_FILES[$data_gambar]['size']       = $files['size'][$i];
                //now we initialize the upload library
                $this->upload->initialize($config);
                // we retrieve the number of files that were uploaded
                if ($this->upload->do_upload($data_gambar)) {
                    $datas['uploads'][$i] = $this->upload->data();

                    $source = $this->upload->data('full_path');
                    $new_source = $config['upload_path'];

                    if (strtoupper($status) == 'RESIZE') {
                        for ($n = 0; $n < count($resize); $n++) {
                            if(isset($resize[$n]['new_source'])) { $new_source .= $resize[$n]['new_source']; }
                            $configs = array('image_library' => 'gd2', 'source_image' => $source, 'new_image' => $new_source, 'maintain_ration' => TRUE, 'height' => $resize[$n]['height'], 'width' => $resize[$n]['width']);
                            $this->load->library('image_lib');
                            $this->image_lib->clear();
                            $this->image_lib->initialize($configs);
                            $this->image_lib->resize();
                        }
                    }

                } else {

                    $datas['uploads'][$i] = '';
                    $datas['upload_errors'][$i] = $this->upload->display_errors();
                }

            } else {
                $datas['uploads'][$i] = '';
            }
        }

        return ($datas);
    }

    function add_log_activity($activity, $usecase = '')
    {
        $session = sessions();

        $id_user = $session['id_user'];
        $now = $this->super_library->datetime_now();

        if(!empty($usecase)) {
            $usecase = $this->super_library->usecases($usecase);
        }

        $add['pengguna']= $id_user;
        $add['waktu']= $now['datetimeNow'];
        $add['activity']= $activity;
        $add['usecase']= $usecase;
        $add['ip']= $this->input->ip_address();

        $this->db->insert("activity_log", $add);
    }

}
