<?php
defined('BASEPATH') OR exit('no direct script access allowed');
/**
 * Created by PhpStorm.
 * User: Rizki Darmawan
 * Date: 04/01/2018
 * Time: 14:55
 */

function message_alert($type, $text = '', $style = '')
{
    if($type == "danger") {
        $icon = "fa-ban";
    } elseif($type == "info") {
        $icon = "fa-info";
    } elseif($type == "warning") {
        $icon = "fa-warning";
    } elseif($type == "success") {
        $icon = "fa-check";
    } else { $icon = ''; }

    $message = '';
    if($text != ''){
        $message .= '<div class="row clearfix">
                        <div class="col-md-12" '.$style.'>
                            <div class="alert alert-'.$type.'" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <i class="fa '.$icon.'"></i>&nbsp;
                                <strong>Info!</strong> '.$text.'
                            </div>
                        </div>
                    </div>';
    }else{
        $message.= '<div class="alert alert-'.$type.'" role="alert"><i class="fa '.$icon.'"></i>&nbsp;';
    }

    return ($message);
}

function publish($status, $modul = '', $id = '', $fungsi = 'publish')
{
    $data = '';

    if($status == '1') {
        $status = 'AKTIF';
        $bg = 'bg-teal';
        $add_link = 'unpub';

    } else {

        $status = 'NON AKTIF';
        $bg = 'bg-orange';
        $add_link = 'pub';
    }

    if(!empty($modul)) {
        $data .= '<a href="'.site_url($modul.'/'.$fungsi.'/'.base64_encode($id).'/'.$add_link).'">';
    }

    $data .= '<span class="badge '.$bg.'">'.$status.'</span>';

    if(!empty($modul)) {
        $data .= '</a>';
    }

    return $data;
}

function cek_session($sess_name, $sess_value = '')
{
    $ci =& get_instance();
    if($sess_value == '') {
        if($ci->session->has_userdata($ci->config->item($sess_name))) {
            return true;
        } else {
            return false;
        }
    } else {
        if($ci->session->userdata($ci->config->item($sess_name)) == $sess_value) {
            return true;
        } else {
            return false;
        }
    }
}

function datediff($tgl1, $tgl2)
{
    $tgl1 = strtotime($tgl1);
    $tgl2 = strtotime($tgl2);
    $diff_secs = abs($tgl1 - $tgl2);
    $base_year = min(date("Y", $tgl1), date("Y", $tgl2));
    $diff = mktime(0, 0, $diff_secs, 1, 1, $base_year);
    return array(
        "years"         => date("Y", $diff) - $base_year,
        "months_total"  => (date("Y", $diff) - $base_year) * 12 + date("n", $diff) - 1,
        "months"        => date("n", $diff) - 1,
        "days_total"    => floor($diff_secs / (3600 * 24)),
        "days"          => date("j", $diff) - 1,
        "hours_total"   => floor($diff_secs / 3600),
        "hours"         => date("G", $diff),
        "minutes_total" => floor($diff_secs / 60),
        "minutes"       => (int) date("i", $diff),
        "seconds_total" => $diff_secs,
        "seconds"       => (int) date("s", $diff)
    );
}

function get_selisih_waktu($w, $h, $j, $m, $d)
{
    if($h >= 7) {
        $data = $w;
    } elseif($h >= 1) {
        $data = $h.' hari lalu';
    } elseif($j >= 1) {
        $data = $j.' jam lalu';
    } elseif($m >= 6) {
        $data = 'Beberapa menit lalu';
    } elseif($m >= 1 && $m <= 5) {
        $data = $m . ' menit lalu';
    } elseif($d >= 15) {
        $data = 'Beberapa detik lalu';
    } else {
        $data = $d.' detik lalu';
    }
    return $data;
}

function acak_angka_huruf($panjang, $tipe_karakter = array())
{
    $angka = '123456789';
    $hurufkecil = 'abcdefghjklmnpqrstuvwxyz';
    $hurufbesar = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    $warna = '0123456789ABCDEF';

    $string = '';
    $karakter = '';
    if(empty($tipe_karakter)) {
        $karakter = $hurufkecil.$hurufbesar.$angka;
    } else {
        if(in_array('angka', $tipe_karakter)) {
            $karakter .= $angka;
        }
        if(in_array('hurufkecil', $tipe_karakter)) {
            $karakter .= $hurufkecil;
        }
        if(in_array('hurufbesar', $tipe_karakter)) {
            $karakter .= $hurufbesar;
        }
        if(in_array('warna', $tipe_karakter)) {
            $karakter .= $warna;
            $string = '#';
        }
    }

    for ($i = 0; $i < $panjang; $i++)
    {
        $pos = rand(0, strlen($karakter)-1);
        $string .= $karakter{$pos};
    }
    return $string;
}

function get_smileys($konten = '')
{
    $ci =& get_instance();
    $ci->load->helper('smiley');

    $data = parse_smileys($konten, base_url('uploads/smileys'));
    echo $data;
}

function get_params($kecuali = array())
{
    $url = parse_url(base_url($_SERVER['REQUEST_URI']));
    if(isset($url['query'])) {
        if(empty($kecuali)) {
            $getParam = '?'.$url['query'];
        } else {
            $q = parse_query($_SERVER['REQUEST_URI'], $kecuali);
            if(!empty($q)) {
                foreach ($q as $r => $v) {
                    $uri[] = $r.'='.$v;
                }
                $getParam = '?'.implode('&', $uri);
            } else {
                $getParam = '';
            }
        }
    } else {
        $getParam = '';
    }

    return $getParam;
}

function get_key_val($url = '', $kecuali = array(), $ganti = array())
{
    if($url == '') {
        $url = parse_url(base_url($_SERVER['REQUEST_URI']));
        $req_uri = $_SERVER['REQUEST_URI'];
    } else {
        $exp = explode('/', $url);
        for($i = 0; $i < count($exp); $i++) {
            if($i > 2) {
              $l[] = $exp[$i];
            }
        }
        $req_uri = '/'.implode('/', $l);

        $url = parse_url($url);
    }
    if(isset($url['query'])) {
        $q = parse_query($req_uri, $kecuali);
        if(!empty($q)) {
            foreach ($q as $r => $v) {
                if(!empty($ganti)) {
                    if(isset($ganti[$r])) {
                        $v = $ganti[$r];
                    }
                }
                $uri[] = $r.'='.$v;
                $key[] = $r;
                $val[] = $v;
            }
            $getParam = '?'.implode('&', $uri);
            $getKey = implode('#', $key);
            $getVal = implode('#', $val);
        } else {
            $getParam = '';
            $getKey = '';
            $getVal = '';
        }
    } else {
        $getParam = '';
        $getKey = '';
        $getVal = '';
    }

    return array('getParam' => $getParam, 'getKey' => $getKey, 'getVal' => $getVal);
}

function parse_query($var, $kecuali = array())
{
    /**
     *  Use this function to parse out the query array element from
     *  the output of parse_url().
     */
    $var  = parse_url($var, PHP_URL_QUERY);
    $var  = html_entity_decode($var);
    $var  = explode('&', $var);
    $arr  = array();

    foreach($var as $val)
    {
        $x = explode('=', $val);

        if(!in_array($x[0], $kecuali)) {
            $arr[$x[0]] = $x[1];
        }

    }
    unset($val, $x, $var);
    return $arr;
}

function cek_page($table, $id, $val)
{
    $ci =& get_instance();

    $cek = $ci->super_model->get_table_where($table, $id, $val)->num_rows();
    if($cek > 0) {
        return TRUE;
    } else {
        return FALSE;
    }
}

function array_orderby()
{
    $args = func_get_args();
    $data = array_shift($args);
    foreach ($args as $n => $field) {
        if (is_string($field)) {
            $tmp = array();
            foreach ($data as $key => $row)
                $tmp[$key] = $row[$field];
            $args[$n] = $tmp;
        }
    }
    $args[] = &$data;
    call_user_func_array('array_multisort', $args);
    return array_pop($args);
}

function format_rentang_tanggal($tgl1, $tgl2)
{
    $ci =& get_instance();

    $tgl1 = $ci->super_library->convert_date($tgl1, 'ind');
    $tgl1_expl = explode(' ', $tgl1);
    $tgl2 = $ci->super_library->convert_date($tgl2, 'ind');
    $tgl2_expl = explode(' ', $tgl2);

    if($tgl1_expl[2] == $tgl2_expl[2]) {
        if($tgl1_expl[1] == $tgl2_expl[1]) {
            $tanggal = $tgl1_expl[0] .' s.d. '. $tgl2;
        } else {
            $tanggal = $tgl1_expl[0] .' '.$tgl1_expl[1] .' s.d. '. $tgl2;
        }

    } else {
        $tanggal = $tgl1 .' s.d. '. $tgl2;
    }

    return $tanggal;
}