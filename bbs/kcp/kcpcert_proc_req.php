<?
    /* ============================================================================== */
    /* =   ���̺귯�� ���� Include                                                  = */
    /* = -------------------------------------------------------------------------- = */

    require "./ct_cli_lib.php";

    /* = -------------------------------------------------------------------------- = */
    /* =   ���̺귯�� ���� Include END                                               = */
    /* ============================================================================== */
?>
<?
    /* ============================================================================== */
    /* =   null ���� ó���ϴ� �޼ҵ�                                                = */
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
?>
<?
    $home_dir      = "/home/chicpro/www/g4s/bbs/kcp"; // ct_cll ������ ( bin ������ )

    $req_tx        = "";

    $site_cd       = "";
    $ordr_idxx     = "";

    $year          = "";
    $month         = "";
    $day           = "";
    $user_name     = "";
    $sex_code      = "";
    $local_code    = "";

    $up_hash       = "";
	/*------------------------------------------------------------------------*/
    /*  :: ��ü �Ķ���� �����                                               */
    /*------------------------------------------------------------------------*/

    $ct_cert = new C_CT_CLI;
    $ct_cert->mf_clear();


    // request �� �Ѿ�� �� ó��
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

        if ( $nmParam == "req_tx" )
        {
            $req_tx = f_get_parm_str ( $valParam );
        }

        if ( $nmParam == "ordr_idxx" )
        {
            $ordr_idxx = f_get_parm_str ( $valParam );
        }

        if ( $nmParam == "user_name" )
        {
            $user_name = f_get_parm_str ( $valParam );
        }

        if ( $nmParam == "year" )
        {
            $year = f_get_parm_int ( $valParam );
        }

        if ( $nmParam == "month" )
        {
            $month = f_get_parm_int ( $valParam );
        }

        if ( $nmParam == "day" )
        {
            $day = f_get_parm_int ( $valParam );
        }

        if ( $nmParam == "sex_code" )
        {
            $sex_code = f_get_parm_int ( $valParam );
        }

        if ( $nmParam == "local_code" )
        {
            $local_code = f_get_parm_int ( $valParam );
        }

        $sbParam .= "<input type='hidden' name='" . $nmParam . "' value='" . f_get_parm_str( $valParam ) . "'/>";
    }

    if ( $req_tx == "cert" )
    {
        $hash_data = $site_cd.$ordr_idxx.$user_name.$year.$month.$day.$sex_code.$local_code; // site_cd �� ordr_idxx �� �ʼ� ���Դϴ�.

        $up_hash = $ct_cert->make_hash_data( $home_dir, $hash_data );

        $sbParam .= "<input type='hidden' name='up_hash' value='" . $up_hash . "'/>";
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
                var frm = document.form_auth;

                // ���� ��û �� ȣ�� �Լ�
                if ( frm.req_tx.value == "cert" )
                {
                    parent.document.form_auth.veri_up_hash.value = frm.up_hash.value;   // up_hash ������ ������ ���� �ʵ�

                    frm.action="https://testcert.kcp.co.kr/kcp_cert/cert_view.jsp  "; // KCP ���� ��û ������ URL
                    frm.submit();
                }
                // ���� ��� ������ ���� ������ ȣ�� �Լ�
                else if ( ( frm.req_tx.value == "auth" || frm.req_tx.value == "otp_auth" ) )
                {
                    frm.req_tx.value = "result";
                    frm.action ="./kcpcert_proc_res.php";
                    frm.target ="kcp_cert";
                    frm.submit();

                    window.close();
                }
                else
                {
                    //alert ("req_tx ���� Ȯ���� �ּ���");
                }

            }
        </script>
    </head>
    <body oncontextmenu="return false;" ondragstart="return false;" onselectstart="return false;">
        <form name="form_auth" method="post">
            <?= $sbParam ?>
        </form>
    </body>
</html>
