<?php
include_once('./_common.php');

/* ============================================================================== */
/* =   라이브러리 파일 Include                                                  = */
/* = -------------------------------------------------------------------------- = */

require "./ct_cli_lib.php";

/* = -------------------------------------------------------------------------- = */
/* =   라이브러리 파일 Include END                                              = */
/* ============================================================================== */

/* ============================================================================== */
/* =   null 값을 처리하는 메소드                                                = */
/* = -------------------------------------------------------------------------- = */
function f_get_parm_str( $val )
{
    if ( $val == null ) $val = "";
    if ( $val == ""   ) $val = "";
    return  $val;
}

function f_get_parm_int( $val )
{
    $ret_val = "";

    if ( $val == null ) $val = "00";
    if ( $val == ""   ) $val = "00";

    $ret_val = strlen($val) == 1? ("0" . $val) : $val;

    return  $ret_val;
}
/* ============================================================================== */

$home_dir      = G4_BBS_PATH."/kcp"; // ct_cll 절대경로 ( bin 전까지 )

if($config['cf_kcpcert_test']) {
    $cert_action_url = "https://testcert.kcp.co.kr/kcp_cert/cert_view.jsp";
} else {
    $cert_action_url = "https://cert.kcp.co.kr/kcp_cert/cert_view.jsp";
}
?>