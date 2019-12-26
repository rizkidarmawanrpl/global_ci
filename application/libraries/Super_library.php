<?php
defined('BASEPATH') OR exit('no direct script access allowed');
/**
 * Created by PhpStorm.
 * User: Rizki Darmawan
 * Date: 04/01/2019
 * Time: 8:30
 */

Class Super_library
{
    public function __construct()
    {
        $this->CI =& get_instance();
    }

    public function login($level)
    {
        $levels = $this->CI->session->userdata($this->CI->config->item('level'));

        if($this->CI->session->userdata($this->CI->config->item('login')) == TRUE) {
            $exp = explode(',', $level);

            if(count($exp) > 0) {
                if(in_array($levels, $exp)) {
                    return true;

                } else {

                    $this->proses_sess();
                    return false;
                }

            } else {

                $this->proses_sess();
                return false;
            }

        } else {

            $this->proses_sess();
            return false;
        }
    }

    public function hak_akses($komponen, $akses, $messageProses = true, $id_user = '')
    {
        if($this->login('superadmin,pegawai')) {
            $session = sessions(); //apps_helper ->load by config
            if(empty($id_user)) {
                $id_user = $session['id_user'];
            }
			
			//roles untuk insert update delete pada tabel users
			//contoh select role from users where id_user=$id_user
			//hasil disimpan di array dengan josn_decode
            $roles = $this->CI->super_model->get_type_name_by_id('users', 'id_user', $id_user, 'role');

            $result_array = json_decode($roles);

            if(!empty($result_array)) {
                $status = '';

                foreach($result_array as $k=>$v) {
                    if(strtolower(trim($komponen)) == strtolower(trim($v->komponen))) {
                        //jika ada variable $akses dan role akses = 1
						//return true		
						if(isset($v->$akses) && $v->$akses == '1') {
                            $status = '1';
                            break;
                        }

                        break;
                    }
                }

                if($status == '1') {
                    return TRUE;

                } else {
                    if($messageProses) {
                        $this->proses_sess();
                    }
                    return FALSE;
                }

            } else {
                if($messageProses) {
                    $this->proses_sess();
                }
                return FALSE;
            }
        }
    }

    public function proses_sess()
    {
        if($this->CI->session->userdata($this->CI->config->item('login')) == TRUE) {

            echo "<script>alert('Maaf, Anda tidak memiliki akses untuk fitur ini!');</script>";
            echo "<script>window.location.href='" . site_url('beranda') . "'</script>";

        } else {

            //session_start();

            echo"<script>alert('Maaf, Anda tidak sedang login.');</script>";

            echo"<script>document.location.href='".site_url('panellogin')."'</script>";

            //$this->CI->session->sess_destroy();
        }
    }

    public function cetak_pdf($view, $data = '', $filename = '')
    {
        $this->CI->load->library('pdf');

        $html   = $this->CI->load->view($view, $data, TRUE);
        $pdf    = $this->CI->pdf->load();

        $pdf->SetFooter($_SERVER['HTTP_HOST'].'|{PAGENO}|'.date(DATE_RFC822)); // Add a footer for good measure <img src="http://davidsimpson.me/wp-includes/images/smilies/icon_wink.gif" alt=";)" class="wp-smiley">
        $pdf->WriteHTML($html); // write the HTML into the PDF

        if($filename == '') {
            $pdf->Output();
        } else {
            $pdf->Output($filename.'.pdf', 'I'); /*save to file because we can*/
        }
    }

    public function datetime_now()
    {
        date_default_timezone_set("Asia/Jakarta");

        $getDate= getdate();
        $data['hari']           = $getDate['mday'];
        $data['bulan']          = $getDate['mon'];
        $data['tahun']          = $getDate['year'];
        $data['jam']            = $getDate['hours'];
        $data['menit']          = $getDate['minutes'];
        $data['detik']          = $getDate['seconds'];
        $data['datetimeNow']    = $getDate['year'].'-'.$getDate['mon'].'-'.$getDate['mday'].' '.$getDate['hours'].':'.$getDate['minutes'].':'.$getDate['seconds'];

        return ($data);
    }

    public function kirim_email($message = '', $sender = '', $receiver = '', $nama = 'Tanpa Nama', $subject = "Tanpa Subject", $data = '')
    {
        $this->CI->load->library('email');

        $config['protocol'] 	= 'smtp';

        $config['smtp_port']	= 587;
        $config['smtp_crypto']	= 'tls';
        $config['smtp_host']	= getenv('SMTP_HOST');
        $config['smtp_user']	= getenv('SMTP_USER');
        $config['smtp_pass']	= getenv('SMTP_PASSWORD');

        $config['mailtype']		= "html";
        $config['newline']		= "\r\n";
        $config['charset']		= 'utf-8';
        $config['wordwrap']		= FALSE;

        $this->CI->email->initialize($config);
        //$this->email->set_newline("\r\n");

        $sender_mail= $sender;
        $receiver_mail= $receiver;
        $this->CI->email->from($sender_mail, $nama);
        $this->CI->email->subject($subject);
        $this->CI->email->to($receiver_mail);
        //$this->email->cc('rizki@erdeprof.co.id');

        $this->CI->email->message($message);
        //return $this->email->send();
        if($this->CI->email->send()) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function get_rss($link, $limit = 5)
    {
        $this->CI->load->library('Rss');

        $this->CI->rss->set_items_limit($limit); // Jumlah link
        $this->CI->rss->set_cache_life(5); // Seting Cache Time
        $this->CI->rss->set_cache_path('');
        $this->CI->rss->set_url(array($link));
        $data = $this->CI->rss->parse();
        return $data;
    }

    public function convert_date($tgl, $version = 'eng')
    {
        $char_tahun     = substr($tgl,0,4);
        $char_bulan     = substr($tgl,5,2);
        $char_tanggal   = substr($tgl,8,2);

        if($typ = 'ind')
            $bulan = $this->convert_month_ind($char_bulan);
        else
            $bulan = $this->convert_month_eng($char_bulan);

        $tanggal = $char_tanggal.' '.$bulan.' '.$char_tahun;

        return $tanggal;
    }

    public function convert_month_eng($bln)
    {
        switch($bln)
        {
            case"01": $bulan = "January";   break;
            case"02": $bulan = "February";  break;
            case"03": $bulan = "March";     break;
            case"04": $bulan = "April";     break;
            case"05": $bulan = "May";       break;
            case"06": $bulan = "June";      break;
            case"07": $bulan = "July";      break;
            case"08": $bulan = "August";    break;
            case"09": $bulan = "September"; break;
            case"10": $bulan = "October";   break;
            case"11": $bulan = "November";  break;
            case"12": $bulan = "December";  break;
            default : $bulan = "";          break;
        }
        return $bulan;
    }

    public function convert_month_ind($bln)
    {
        switch($bln)
        {
            case"01": $bulan = "Januari";   break;
            case"02": $bulan = "Februari";  break;
            case"03": $bulan = "Maret";     break;
            case"04": $bulan = "April";     break;
            case"05": $bulan = "Mei";       break;
            case"06": $bulan = "Juni";      break;
            case"07": $bulan = "Juli";      break;
            case"08": $bulan = "Agustus";   break;
            case"09": $bulan = "September"; break;
            case"10": $bulan = "Oktober";   break;
            case"11": $bulan = "November";  break;
            case"12": $bulan = "Desember";  break;
            default : $bulan = "";          break;
        }
        return $bulan;
    }

    public function convert_time($char)
    {
        $time = substr($char,11,5);
        return $time;
    }

    public function tinymce($width = '100%', $height = 400, $class = '.mceEditor', $base_url = '')
    {
        $content = '<script src="'.base_url().'file_manager_responsive/tinymce/tinymce.min.js"></script>';
        $content.= '<script type="text/javascript">';
        $content.= '
        tinymce.init({
            selector                    : "textarea'.$class.'",
            theme                       : "modern",
            width                       : "'.$width.'",
            height                      : "'.$height.'", /* height use px */
            plugins                     : [
                                            "advlist autolink link image lists charmap print preview hr anchor pagebreak",
                                            "searchreplace wordcount visualblocks code visualchars insertdatetime media nonbreaking spellchecker",
                                            "table contextmenu directionality emoticons paste textcolor responsivefilemanager "
                                           ],
           relative_urls                : false,
           
           remove_script_host           : false,
           
           filemanager_title            : "Responsive Filemanager",
           external_filemanager_path    : "'.base_url().'file_manager_responsive/filemanager/",
           external_plugins             : { "filemanager" : "'.base_url().'file_manager_responsive/tinymce/plugins/responsivefilemanager/plugin.min.js"},
           
           image_advtab                 : true,
    
           filemanager_crossdomain      : true,
    
           filemanager_access_key       : "9d1cde25b823e4bb37335e1e9a63c18a50c4838e" ,
           
           toolbar1                     : "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | styleselect",
           toolbar2                     : "| responsivefilemanager | image | media | link unlink anchor | print preview code"
           
       });
       ';

        $content.= '</script>';
        return $content;
    }

    public function tinymce_standar($width = '100%', $height = 400, $class = '.mceEditor', $base_url = '')
    {
        $content = '<script src="'.base_url().'file_manager_responsive/tinymce/tinymce.min.js"></script>';
        $content.= '<script type="text/javascript">';
        $content.= '
        tinymce.init({
            selector                        : "textarea'.$class.'",
            theme                           : "modern",
            width                           : "'.$width.'",
            height                          : "'.$height.'", /* height use px */
            menubar                         : false,
            plugins                         : [
                                                "advlist autolink link image lists charmap print preview hr anchor pagebreak",
                                                "searchreplace wordcount visualblocks code visualchars insertdatetime media nonbreaking spellchecker",
                                                "table contextmenu directionality emoticons paste textcolor responsivefilemanager "
                                              ],
    
            relative_urls                   : false,
    
            remove_script_host              : false,
    
            filemanager_title               : "Responsive Filemanager",
            external_filemanager_path       : "'.base_url().'file_manager_responsive/filemanager/",
            external_plugins                : { "filemanager" : "'.base_url().'file_manager_responsive/tinymce/plugins/responsivefilemanager/plugin.min.js"},
    
            image_advtab                    : true,
    
            filemanager_crossdomain         : true,
    
            filemanager_access_key          : "9d1cde25b823e4bb37335e1e9a63c18a50c4838e" ,
            
            toolbar1                        : "undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | styleselect",
            toolbar2                        : "| link unlink anchor | print preview code"
        });
        ';
        $content.= '</script>';
        return $content;
    }

    public function hashing()
    {
        require_once './vendor/bitmannl/bcrypt/Crypt.php';
        require_once './vendor/bitmannl/bcrypt/Bcrypt.php';
        return new Bcrypt();
    }

    public function get_web_page( $url )
    {
        $options = array(
            CURLOPT_RETURNTRANSFER => true,     // return web page
            CURLOPT_HEADER         => false,    // don't return headers
            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
            CURLOPT_ENCODING       => "",       // handle all encodings
            CURLOPT_USERAGENT      => "spider", // who am i
            CURLOPT_AUTOREFERER    => true,     // set referer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
            CURLOPT_TIMEOUT        => 120,      // timeout on response
            CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
            CURLOPT_SSL_VERIFYPEER => false,     // Disabled SSL Cert checks
        );

        $ch      = curl_init( $url );
        curl_setopt_array( $ch, $options );
        $content = curl_exec( $ch );
        $err     = curl_errno( $ch );
        $errmsg  = curl_error( $ch );
        $header  = curl_getinfo( $ch );
        curl_close( $ch );

        $header['errno']   = $err;
        $header['errmsg']  = $errmsg;
        $header['content'] = $content;

        return $header;
    }

    public function recaptcha_responses($str)
    {
        $google_url = "https://www.google.com/recaptcha/api/siteverify";
        $secret     = $this->CI->config->item('recaptcha_secret');
        $ip         = $_SERVER['REMOTE_ADDR'];
        $url        = $google_url."?secret=".$secret."&response=".$str."&remoteip=".$ip;
        $curl       = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.16) Gecko/20110319 Firefox/3.6.16");
        $res        = curl_exec($curl);
        curl_close($curl);
        $res        = json_decode($res, true);
        //reCaptcha success check
        if($res['success'])
        {
            return TRUE;
        }
        else
        {
            $this->CI->form_validation->set_message('recaptcha', 'The reCAPTCHA field is telling me that you are a robot. Shall we give it another try?');

            return FALSE;
        }
    }

}
