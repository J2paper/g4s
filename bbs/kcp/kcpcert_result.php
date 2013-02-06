<?php
include_once('./_common.php');
include_once('./kcpcert_common.php');

// 변수값 초기화
$site_cd       = "";
$ordr_idxx     = "";

$cert_no       = "";
$cert_enc_use  = "";
$enc_info      = "";
$enc_data      = "";
$req_tx        = "";

$enc_cert_data = "";
$cert_info     = "";

$tran_cd       = "";
$res_cd        = "";
$res_msg       = "";

$dn_hash       = "";
$up_hash       = "";
/*------------------------------------------------------------------------*/
/*  :: 전체 파라미터 남기기                                               */
/*------------------------------------------------------------------------*/

// request 로 넘어온 값 처리
$key = array_keys($_POST);
$sbParam ="";

for($i=0; $i<count($key); $i++)
{
    $nmParam = $key[$i];
    $valParam = $_POST[$nmParam];

    if ( $nmParam == "site_cd" )
    {
        $site_cd = f_get_parm_str ( $valParam );
    }

    if ( $nmParam == "ordr_idxx" )
    {
        $ordr_idxx = f_get_parm_str ( $valParam );
    }

    if ( $nmParam == "res_cd" )
    {
        $res_cd = f_get_parm_str ( $valParam );
    }

    if ( $nmParam == "cert_enc_use" )
    {
        $cert_enc_use = f_get_parm_str ( $valParam );
    }

    if ( $nmParam == "req_tx" )
    {
        $req_tx = f_get_parm_str ( $valParam );
    }

    if ( $nmParam == "cert_no" )
    {
        $cert_no = f_get_parm_str ( $valParam );
    }

    if ( $nmParam == "enc_cert_data" )
    {
        $enc_cert_data = f_get_parm_str ( $valParam );
    }

    if ( $nmParam == "dn_hash" )
    {
        $dn_hash = f_get_parm_str ( $valParam );
    }

    if ( $nmParam == "up_hash" )
    {
        $up_hash = f_get_parm_str ( $valParam );
    }

    $sbParam .= "<input type='hidden' name='" . $nmParam . "' value='" . f_get_parm_str( $valParam ) . "'/>";
}


$ct_cert = new C_CT_CLI;
$ct_cert->mf_clear();

// 결과 처리
if( $req_tx == "result" )
{
    if( $cert_enc_use == "Y" )
    {
        if( $res_cd == "0000" )
        {
            // dn_hash 검증
            $veri_str = $site_cd.$ordr_idxx.$cert_no; // 사이트 코드 + 주문번호 + 인증거래번호

            if ( $ct_cert->check_valid_hash ( $home_dir , $dn_hash , $veri_str ) != "1" )
            {
                 alert_close("dn_hash 값이 변조되었습니다.");
            }

            if(!$enc_cert_data)
                alert_close("정상적인 접근이 아닙니다.");

            // enc_cert_data 복호화
            $ct_cert->decrypt_enc_cert( $home_dir , $site_cd , $cert_no , $enc_cert_data );

            $comm_id        = $ct_cert->mf_get_key_value("comm_id"    ); // 이동통신사 코드
            $phone_no       = $ct_cert->mf_get_key_value("phone_no"   ); // 전화번호
            $user_name      = $ct_cert->mf_get_key_value("user_name"  ); // 이름
            $birth_day      = $ct_cert->mf_get_key_value("birth_day"  ); // 생년월일
            $sex_code       = $ct_cert->mf_get_key_value("sex_code"   ); // 성별코드
            $local_code     = $ct_cert->mf_get_key_value("local_code" ); // 내/외국인 정보
            $ci             = $ct_cert->mf_get_key_value("ci"         ); // CI
            $di             = $ct_cert->mf_get_key_value("di"         ); // DI 중복가입 확인값
            $res_cd         = $ct_cert->mf_get_key_value("res_cd"     ); // 암호화된 결과코드
            $res_msg        = $ct_cert->mf_get_key_value("res_msg"    ); // 암호화된 결과메시지
        }
        else if( $res_cd != "0000" )
        {
            $msg = iconv("cp949", "utf8", $res_msg);
            alert_close("[".$res_cd."] ".$res_msg);
        }
    }
}
else if( $req_tx != "result" )
{
    alert_close("잘못된 접근입니다.");
}

$ct_cert->mf_clear();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>*** KCP Online Payment System [PHP Version] ***</title>
        <script>
            window.onload=function()
            {
                var frm = opener.document.fregisterform;

                // up_hash 비교
                var up_hash = frm.veri_up_hash.value;
                if(up_hash != "<?=$up_hash?>") {
                    alert("up_hash 값이 변조되었습니다.");
                    window.close();
                }

                // res_cd 기록
                frm.res_cd.value = "<?=$res_cd?>";
            }
        </script>
    </head>
    <body oncontextmenu="return false;" ondragstart="return false;" onselectstart="return false;">
        <form name="form_auth" method="post">
            <?= $sbParam ?>
        </form>
    </body>
</html>