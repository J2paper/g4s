<?
    /* ============================================================================== */
    /* =   라이브러리 파일 Include                                                  = */
    /* = -------------------------------------------------------------------------- = */

    require "./ct_cli_lib.php";

    /* = -------------------------------------------------------------------------- = */
    /* =   라이브러리 파일 Include END                                               = */
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
    /* ============================================================================== */
    $home_dir      = "/home/chicpro/www/g4s/bbs/kcp"; // ct_cll 절대경로 ( bin 전까지 )

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
                    echo "dn_hash 변조 위험있음";
                    // 오류 처리 ( dn_hash 변조 위험있음)
                }

                // 가맹점 DB 처리 페이지 영역
		        echo "사이트 코드"    .    $site_cd;
		        echo "인증 번호"      .    $cert_no;
		        echo "암호된 인증정보".    $enc_cert_data;

		        $ct_cert->decrypt_enc_cert( $home_dir , $site_cd , $cert_no , $enc_cert_data );

  		        echo "이동통신사 코드"    . $ct_cert->mf_get_key_value("comm_id"    )."<br>"; // 이동통신사 코드
		        echo "전화번호"           . $ct_cert->mf_get_key_value("phone_no"   )."<br>"; // 전화번호
		        echo "이름"               . $ct_cert->mf_get_key_value("user_name"  )."<br>"; // 이름
		        echo "생년월일"           . $ct_cert->mf_get_key_value("birth_day"  )."<br>"; // 생년월일
		        echo "성별코드"           . $ct_cert->mf_get_key_value("sex_code"   )."<br>"; // 성별코드
		        echo "내/외국인 정보 "    . $ct_cert->mf_get_key_value("local_code" )."<br>"; // 내/외국인 정보
		        echo "CI"                 . $ct_cert->mf_get_key_value("ci"         )."<br>"; // CI
	            echo "DI 중복가입 확인값" . $ct_cert->mf_get_key_value("di"         )."<br>"; // DI 중복가입 확인값
	            echo "암호화된 결과코드"  . $ct_cert->mf_get_key_value("res_cd"     )."<br>"; // 암호화된 결과코드
                echo "암호화된 결과메시지". $ct_cert->mf_get_key_value("res_msg"    )."<br>"; // 암호화된 결과메시지
	        }
            else/*if( res_cd.equals( "0000" ) != true )*/
            {
               // 인증실패
            }
	    }
        else/*if( cert_enc_use.equals( "Y" ) != true )*/
        {
            // 암호화 인증 안함
        }
    }
    else/*if( req_tx.equals( "result" ) != true )*/
    {
        // 잘못된 접근 입니다
    }

    $ct_cert->mf_clear();
?>



<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=EUC-KR">
        <title>*** KCP Online Payment System [PHP Version] ***</title>
        <script type="text/javascript">
            window.onload=function()
            {
                parent.auth_data( document.form_auth ); // 값 전달
            }
        </script>
    </head>
    <body oncontextmenu="return false;" ondragstart="return false;" onselectstart="return false;">
        <form name="form_auth" method="post">
            <?= $sbParam ?>
        </form>
    </body>
</html>
