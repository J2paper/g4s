<?php
include_once('./_common.php');

set_session('ss_direct', $sw_direct);

$uq_id = get_session('ss_uniqid');

if($sw_direct != 1)
    $sw_direct = 0;

// 장바구니가 비어있는가?
$cart_count = get_cart_count($uq_id, $sw_direct, $member['mb_id']);
if ($cart_count == 0)
    alert("장바구니가 비어 있습니다.", "./cart.php");

// 포인트 결제 대기 필드 추가
//sql_query(" ALTER TABLE `$g4[yc4_order_table]` ADD `od_temp_point` INT NOT NULL AFTER `od_temp_card` ", false);

$g4['title'] = '주문서 작성';

include_once('./_head.php');
?>

<img src="<?=$g4['shop_img_path']?>/top_orderform.gif" border="0"><p>

<?
$s_page = 'orderform.php';
$s_uq_id = $uq_id;
include_once('./cartsub.inc.php');

// 새로운 주문번호 생성
if(!get_session('ss_order_uniqid')) {
    set_session('ss_order_uniqid', get_uniqid());
}
$od_uq_id = get_session('ss_order_uniqid');

if (file_exists("./settle_{$default['de_card_pg']}.inc.php")) {
    include "./settle_{$default['de_card_pg']}.inc.php";
}

if ($g4['https_url'])
    $action_url = $g4['https_url'].'/'.$g4['shop'].'/orderformupdate.php';
else
    $action_url = './orderformupdate.php';
?>

<?
    /* ============================================================================== */
    /* =   Javascript source Include                                                = */
    /* = -------------------------------------------------------------------------- = */
    /* =   ※ 필수                                                                  = */
    /* = -------------------------------------------------------------------------- = */
?>
    <script type="text/javascript" src="<? echo $g_conf_js_url; ?>"></script>
<?
    /* = -------------------------------------------------------------------------- = */
    /* =   Javascript source Include END                                            = */
    /* ============================================================================== */
?>
<script type="text/javascript">
    /* 플러그인 설치(확인) */
    StartSmartUpdate();

    /*  해당 스크립트는 타브라우져에서 적용이 되지 않습니다.
    if( document.Payplus.object == null )
    {
        openwin = window.open( "chk_plugin.html", "chk_plugin", "width=420, height=100, top=300, left=300" );
    }
    */

    /* Payplus Plug-in 실행 */
    function  jsf__pay( form )
    {
        var RetVal = false;

        /* Payplus Plugin 실행 */
        if ( MakePayMessage( form ) == true )
        {
            openwin = window.open( "./kcp/proc_win.html", "proc_win", "width=449, height=209, top=300, left=300" );
            RetVal = true ;
        }

        else
        {
            /*  res_cd와 res_msg변수에 해당 오류코드와 오류메시지가 설정됩니다.
                ex) 고객이 Payplus Plugin에서 취소 버튼 클릭시 res_cd=3001, res_msg=사용자 취소
                값이 설정됩니다.
            */
            res_cd  = document.forderform.res_cd.value ;
            res_msg = document.forderform.res_msg.value ;

        }

        return RetVal ;
    }

    // Payplus Plug-in 설치 안내
    function init_pay_button()
    {
        /*
        if( document.Payplus.object == null )
            document.getElementById("display_setup_message").style.display = "block" ;
        else
            document.getElementById("display_pay_button").style.display = "block" ;
        */
        // 체크 방법이 변경
        if( GetPluginObject() == null ){
            document.getElementById("display_setup_message").style.display = "block" ;
        }
        else{
            document.getElementById("display_pay_button").style.display = "block" ;
        }
    }

    /*
     * 인터넷 익스플로러와 파이어폭스(사파리, 크롬.. 등등)는 javascript 파싱법이 틀리기 때문에 object 가 인식 전에 실행 되는 문제
     * 기존에는 onload 부분에 추가를 했지만 setTimeout 부분에 추가
     * setTimeout 에 2번째 변수 0은 딜레이 시간 0은 딜래이 없음을 의미
     * - 김민수 - 20101018 -
     */
    setTimeout("init_pay_button();",300);
</script>

<form name="forderform" method="post" action="<? echo $action_url; ?>" autocomplete="off">
<input type="hidden" name="w" value="<? echo $w; ?>" />
<input type="hidden" name="od_uq_id" value="<? echo $od_uq_id; ?>" />
<input type="hidden" name="od_amount"    value="<? echo $tot_sell_amount; ?>" />
<input type="hidden" name="od_send_cost" value="<? echo $send_cost; ?>" />
<input type="hidden" name="od_send_cost_area" value="0" />
<input type="hidden" name="od_coupon" value="" />
<input type="hidden" name="od_coupon_amount" value="0" />
<input type="hidden" name="od_send_coupon" value="" />
<input type="hidden" name="od_send_coupon_amount" value="0" />
<?php
$good_info = '';
$comm_tax_mny = 0; // 과세금액
$comm_vat_mny = 0; // 부가세
$comm_free_mny = 0; // 면세금액

// 상품수만큼 정보 필드 미리 만들어둠
for($k=0;$k<$goods_count; $k++) {
    echo '<input type="hidden" name="od_ct_id['.$k.']" value="'.$itemlist[$k]['ct_id'].'" />'."\n";
    echo '<input type="hidden" name="od_it_id['.$k.']" value="'.$itemlist[$k]['it_id'].'" />'."\n";
    echo '<input type="hidden" name="od_cp_id['.$k.']" value="" />'."\n";
    echo '<input type="hidden" name="od_ch_amount['.$k.']" value="" />'."\n";

    // 에스크로 상품정보
    if ($k>0)
        $good_info .= chr(30);
    $good_info .= "seq=".($k+1).chr(31);
    $good_info .= "ordr_numb={$od_uq_id}_".sprintf("%04d", $k).chr(31);
    $good_info .= "good_name=".addslashes(preg_replace("/\'|\"|\||\,|\&|\;/", "", $itemlist[$k]['it_name'])).chr(31);
    $good_info .= "good_cntx=".$itemlist[$k]['qty'].chr(31);
    $good_info .= "good_amtx=".$itemlist[$k]['amount'].chr(31);

    // 상품명
    if($k == 0) {
        $goods = preg_replace("/\'|\"|\||\,|\&|\;/", "", $itemlist[$k]['it_name']);
    }

    // 과세, 면세금액
    if($default['de_compound_tax_use']) {
        if($itemlist[$k]['notax']) { // 면세상품
            $comm_free_mny += (int)$itemlist[$k]['amount'];
        } else { // 과세상품
            $tax_mny = round((int)$itemlist[$k]['amount'] / 1.1);
            $vat_mny = (int)$itemlist[$k]['amount'] - $tax_mny;

            $comm_tax_mny += $tax_mny;
            $comm_vat_mny += $vat_mny;
        }
    }
}

// 과세 주문금액 합(결제할인 쿠폰 적용 때 사용)
if($default['de_compound_tax_use']) {
    $tot_tax_amount = $comm_tax_mny + $comm_vat_mny;
} else {
    $tot_tax_amount = $tot_sell_amount;
}

// 배송비 복합과세처리
if($default['de_compound_tax_use'] && $send_cost > 0) {
    $send_tax = round((int)$send_cost / 1.1);
    $send_vat = (int)$send_cost - $send_tax;

    $comm_tax_mny += $send_tax;
    $comm_vat_mny += $send_vat;
}

if($goods_count > 1) {
    $goods .= "외 ".($goods_count - 1);
}

$good_mny = (int)$tot_sell_amount + (int)$send_cost;
?>

<?
    /* ============================================================================== */
    /* =   2. 가맹점 필수 정보 설정                                                 = */
    /* = -------------------------------------------------------------------------- = */
    /* =   ※ 필수 - 결제에 반드시 필요한 정보입니다.                               = */
    /* = -------------------------------------------------------------------------- = */
    // 요청종류 : 승인(pay)/취소,매입(mod) 요청시 사용
?>
    <input type="hidden" name="req_tx"          value="pay" />
    <input type="hidden" name="site_cd"         value="<? echo $default['de_kcp_mid'];	?>" />
    <input type="hidden" name="site_key"        value="<? echo $default['de_kcp_site_key'];  ?>" />
    <input type="hidden" name="site_name"       value="<? echo $default['de_admin_company_name']; ?>" />

<?
    /*
    할부옵션 : Payplus Plug-in에서 카드결제시 최대로 표시할 할부개월 수를 설정합니다.(0 ~ 18 까지 설정 가능)
    ※ 주의  - 할부 선택은 결제금액이 50,000원 이상일 경우에만 가능, 50000원 미만의 금액은 일시불로만 표기됩니다
               예) value 값을 "5" 로 설정했을 경우 => 카드결제시 결제창에 일시불부터 5개월까지 선택가능
    */
?>
    <input type="hidden" name="pay_method"  value="" />
    <input type="hidden" name="ordr_idxx"   value="<? echo $od_uq_id; ?>" />
    <input type="hidden" name="good_name"   value="<? echo $goods; ?>" />
    <input type="hidden" name="good_mny"    value="<? echo $good_mny; ?>" />
    <input type="hidden" name="buyr_name"   value="" />
    <input type="hidden" name="buyr_mail"   value="" />
    <input type="hidden" name="buyr_tel1"   value="" />
    <input type="hidden" name="buyr_tel2"   value="" />

    <input type=hidden name='rcvr_name'     value="" />
    <input type=hidden name='rcvr_tel1'     value="" />
    <input type=hidden name='rcvr_tel2'     value="" />
    <input type=hidden name='rcvr_mail'     value="" />
    <input type=hidden name='rcvr_zipx'     value="" />
    <input type=hidden name='rcvr_add1'     value="" />
    <input type=hidden name='rcvr_add2'     value="" />

    <input type="hidden" name="quotaopt"    value="12"/>

	<!-- 필수 항목 : 결제 금액/화폐단위 -->
    <input type="hidden" name="currency"    value="WON"/>

<?
    /* = -------------------------------------------------------------------------- = */
    /* =   2. 가맹점 필수 정보 설정 END                                             = */
    /* ============================================================================== */
?>

<?
    /* ============================================================================== */
    /* =   3. Payplus Plugin 필수 정보(변경 불가)                                   = */
    /* = -------------------------------------------------------------------------- = */
    /* =   결제에 필요한 주문 정보를 입력 및 설정합니다.                            = */
    /* = -------------------------------------------------------------------------- = */
?>
    <!-- PLUGIN 설정 정보입니다(변경 불가) -->
    <input type="hidden" name="module_type"     value="01"/>
    <!-- 복합 포인트 결제시 넘어오는 포인트사 코드 : OK캐쉬백(SCSK), 베네피아 복지포인트(SCWB) -->
    <input type="hidden" name="epnt_issu"       value="" />
<!--
      ※ 필 수
          필수 항목 : Payplus Plugin에서 값을 설정하는 부분으로 반드시 포함되어야 합니다
          값을 설정하지 마십시오
-->
    <input type="hidden" name="res_cd"          value=""/>
    <input type="hidden" name="res_msg"         value=""/>
    <input type="hidden" name="tno"             value=""/>
    <input type="hidden" name="trace_no"        value=""/>
    <input type="hidden" name="enc_info"        value=""/>
    <input type="hidden" name="enc_data"        value=""/>
    <input type="hidden" name="ret_pay_method"  value=""/>
    <input type="hidden" name="tran_cd"         value=""/>
    <input type="hidden" name="bank_name"       value=""/>
    <input type="hidden" name="bank_issu"       value=""/>
    <input type="hidden" name="use_pay_method"  value=""/>

    <!--  현금영수증 관련 정보 : Payplus Plugin 에서 설정하는 정보입니다 -->
    <input type="hidden" name="cash_tsdtime"    value=""/>
    <input type="hidden" name="cash_yn"         value=""/>
    <input type="hidden" name="cash_authno"     value=""/>
    <input type="hidden" name="cash_tr_code"    value=""/>
    <input type="hidden" name="cash_id_info"    value=""/>

	<!-- 2012년 8월 18일 정자상거래법 개정 관련 설정 부분 -->
	<!-- 제공 기간 설정 0:일회성 1:기간설정(ex 1:2012010120120131)  -->
    <!--
        2012.08.18 부터 개정 시행되는 '전자상거래 등에서의 소비자보호에 관한 법률'에 따른 코드 변경
        이용기간이 제한되는 컨텐츠 상품이나 정기 과금 상품 등에 한하여 '용역의 제공기간'을
        표기/적용하여야 하며 이와 무관한 실물 배송상품 등의 결제에는 해당되지 않습니다.
        0 : 일반결제
        good_expr의 나머지 적용 방식에 대해서는 KCP에서 제공하는 매뉴얼을 참고해 주세요.
    -->
	<input type="hidden" name="good_expr" value="0">

    <!-- 에스크로 항목 -->

    <!-- 에스크로 사용 여부 : 반드시 Y 로 세팅 -->
    <input type='hidden' name='escw_used' value='Y'>

    <!-- 에스크로 결제처리 모드 : 에스크로: Y, 일반: N, KCP 설정 조건: O -->
    <input type='hidden' name='pay_mod' value='<?=($default['de_escrow_use']?"O":"N");?>'>

    <!-- 배송 소요일 : 예상 배송 소요일을 입력 -->
    <input type='hidden' name='deli_term' value='03'>

    <!-- 장바구니 상품 개수 : 장바구니에 담겨있는 상품의 개수를 입력 -->
    <input type='hidden' name='bask_cntx' value="<? echo (int)$goods_count; ?>" />

    <!-- 장바구니 상품 상세 정보 (자바 스크립트 샘플(create_goodInfo()) 참고) -->
    <input type='hidden' name='good_info' value="<? echo $good_info; ?>" />

<?
    /* = -------------------------------------------------------------------------- = */
    /* =   3. Payplus Plugin 필수 정보 END                                          = */
    /* ============================================================================== */
?>

<?
    /* ============================================================================== */
    /* =   4. 옵션 정보                                                             = */
    /* = -------------------------------------------------------------------------- = */
    /* =   ※ 옵션 - 결제에 필요한 추가 옵션 정보를 입력 및 설정합니다.             = */
    /* = -------------------------------------------------------------------------- = */

    /* PayPlus에서 보이는 신용카드사 삭제 파라미터 입니다
    ※ 해당 카드를 결제창에서 보이지 않게 하여 고객이 해당 카드로 결제할 수 없도록 합니다. (카드사 코드는 매뉴얼을 참고)
    <input type="hidden" name="not_used_card" value="CCPH:CCSS:CCKE:CCHM:CCSH:CCLO:CCLG:CCJB:CCHN:CCCH"/> */

    /* 신용카드 결제시 OK캐쉬백 적립 여부를 묻는 창을 설정하는 파라미터 입니다
         OK캐쉬백 포인트 가맹점의 경우에만 창이 보여집니다
        <input type="hidden" name="save_ocb"        value="Y"/> */

	/* 고정 할부 개월 수 선택
	       value값을 "7" 로 설정했을 경우 => 카드결제시 결제창에 할부 7개월만 선택가능
    <input type="hidden" name="fix_inst"        value="07"/> */

	/*  무이자 옵션
            ※ 설정할부    (가맹점 관리자 페이지에 설정 된 무이자 설정을 따른다)                             - "" 로 설정
            ※ 일반할부    (KCP 이벤트 이외에 설정 된 모든 무이자 설정을 무시한다)                           - "N" 로 설정
            ※ 무이자 할부 (가맹점 관리자 페이지에 설정 된 무이자 이벤트 중 원하는 무이자 설정을 세팅한다)   - "Y" 로 설정
    <input type="hidden" name="kcp_noint"       value=""/> */


	/*  무이자 설정
            ※ 주의 1 : 할부는 결제금액이 50,000 원 이상일 경우에만 가능
            ※ 주의 2 : 무이자 설정값은 무이자 옵션이 Y일 경우에만 결제 창에 적용
            예) 전 카드 2,3,6개월 무이자(국민,비씨,엘지,삼성,신한,현대,롯데,외환) : ALL-02:03:04
            BC 2,3,6개월, 국민 3,6개월, 삼성 6,9개월 무이자 : CCBC-02:03:06,CCKM-03:06,CCSS-03:06:04
    <input type="hidden" name="kcp_noint_quota" value="CCBC-02:03:06,CCKM-03:06,CCSS-03:06:09"/> */

	/* 사용카드 설정 여부 파라미터 입니다.(통합결제창 노출 유무)
	<input type="hidden" name="used_card_YN"        value="Y"/>
	/* 사용카드 설정 파라미터 입니다. (해당 카드만 결제창에 보이게 설정하는 파라미터입니다. used_card_YN 값이 Y일때 적용됩니다.
	/<input type="hidden" name="used_card"        value="CCBC:CCKM:CCSS"/>

	/* 해외카드 구분하는 파라미터 입니다.(해외비자, 해외마스터, 해외JCB로 구분하여 표시)
	<input type="hidden" name="used_card_CCXX"        value="Y"/>

	/*  가상계좌 은행 선택 파라미터
         ※ 해당 은행을 결제창에서 보이게 합니다.(은행코드는 매뉴얼을 참조) */
?>
    <input type="hidden" name="wish_vbank_list" value="05:03:04:07:11:23:26:32:34:81:71"/>
<?


	/*  가상계좌 입금 기한 설정하는 파라미터 - 발급일 + 3일
    <input type="hidden" name="vcnt_expire_term" value="3"/> */


	/*  가상계좌 입금 시간 설정하는 파라미터
         HHMMSS형식으로 입력하시기 바랍니다
         설정을 안하시는경우 기본적으로 23시59분59초가 세팅이 됩니다
         <input type="hidden" name="vcnt_expire_term_time" value="120000"/> */


    /* 포인트 결제시 복합 결제(신용카드+포인트) 여부를 결정할 수 있습니다.- N 일경우 복합결제 사용안함
        <input type="hidden" name="complex_pnt_yn" value="N"/>    */


	/* 문화상품권 결제시 가맹점 고객 아이디 설정을 해야 합니다.(필수 설정)
	    <input type="hidden" name="tk_shop_id" value=""/>    */


	/* 현금영수증 등록 창을 출력 여부를 설정하는 파라미터 입니다
         ※ Y : 현금영수증 등록 창 출력
         ※ N : 현금영수증 등록 창 출력 안함
		 ※ 주의 : 현금영수증 사용 시 KCP 상점관리자 페이지에서 현금영수증 사용 동의를 하셔야 합니다 */
?>
    <input type="hidden" name="disp_tax_yn"     value="Y"/>
<?
    /* 결제창에 가맹점 사이트의 로고를 플러그인 좌측 상단에 출력하는 파라미터 입니다
       업체의 로고가 있는 URL을 정확히 입력하셔야 하며, 최대 150 X 50  미만 크기 지원

	※ 주의 : 로고 용량이 150 X 50 이상일 경우 site_name 값이 표시됩니다. */
?>
    <input type="hidden" name="site_logo"       value="" />
<?
	/* 결제창 영문 표시 파라미터 입니다. 영문을 기본으로 사용하시려면 Y로 세팅하시기 바랍니다
		2010-06월 현재 신용카드와 가상계좌만 지원됩니다
		<input type='hidden' name='eng_flag'      value='Y'> */
?>

<? if($default['de_compound_tax_use']) {
	/* KCP는 과세상품과 비과세상품을 동시에 판매하는 업체들의 결제관리에 대한 편의성을 제공해드리고자,
	   복합과세 전용 사이트코드를 지원해 드리며 총 금액에 대해 복합과세 처리가 가능하도록 제공하고 있습니다

	   복합과세 전용 사이트 코드로 계약하신 가맹점에만 해당이 됩니다

	   상품별이 아니라 금액으로 구분하여 요청하셔야 합니다

	   총결제 금액은 과세금액 + 부과세 + 비과세금액의 합과 같아야 합니다.
	   (good_mny = comm_tax_mny + comm_vat_mny + comm_free_mny) */
 ?>
	   <input type="hidden" name="tax_flag"          value="TG03">     <!-- 변경불가    -->
	   <input type="hidden" name="comm_tax_mny"	     value="<? echo $comm_tax_mny; ?>">         <!-- 과세금액    -->
       <input type="hidden" name="comm_vat_mny"      value="<? echo $comm_vat_mny; ?>">         <!-- 부가세	    -->
	   <input type="hidden" name="comm_free_mny"     value="<? echo $comm_free_mny; ?>">        <!-- 비과세 금액 -->
<?
}
?>

<?
     /* skin_indx 값은 스킨을 변경할 수 있는 파라미터이며 총 7가지가 지원됩니다.
	    변경을 원하시면 1부터 7까지 값을 넣어주시기 바랍니다. */
?>
    <input type='hidden' name='skin_indx'      value='1'>

<?
	/* 상품코드 설정 파라미터 입니다.(상품권을 따로 구분하여 처리할 수 있는 옵션기능입니다.)
	<input type='hidden' name='good_cd'      value=''> */

    /* = -------------------------------------------------------------------------- = */
    /* =   4. 옵션 정보 END                                                         = */
    /* ============================================================================== */
?>

<? // 쿠폰
$s_cp_option = '';
$o_cp_option = '';

if($is_member) {
    // 배송비할인쿠폰
    if($send_cost) { // 배송비가 있을 경우만
        $sql = " select cp_id, cp_subject, cp_amount, cp_minimum
                    from {$g4['yc4_coupon_table']}
                    where cp_type = '2'
                      and cp_use = '1'
                      and mb_id in ( '{$member['mb_id']}', '전체회원' )
                      and cp_start <= '{$g4['time_ymd']}'
                      and cp_end >= '{$g4['time_ymd']}' ";
        $result = sql_query($sql);
        for($k=0; $row=sql_fetch_array($result); $k++) {
            // 최소결제금액이 있다면 체크
            if($row['cp_minimum'] && $tot_sell_amount < $row['cp_minimum']) {
                continue;
            }

            // 이미 사용한 쿠폰인지
            $sql = " select ch_no
                        from {$g4['yc4_coupon_history_table']}
                        where cp_id = '{$row['cp_id']}'
                          and mb_id = '{$member['mb_id']}'
                          and uq_id <> '$uq_id' ";
            $ch = sql_fetch($sql);

            if($ch['ch_no']){
                continue;
            }

            $s_cp_option .= '<option value="'.$row['cp_id'].'+'.$row['cp_amount'].'+'.$row['cp_minimum'].'">'.$row['cp_subject'].'</option>'."\n";
        }
    }

    // 결제할인쿠폰
    $sql = " select cp_id, cp_subject, cp_method, cp_amount, cp_minimum, cp_maximum, cp_trunc
                from {$g4['yc4_coupon_table']}
                where cp_type = '1'
                  and cp_use = '1'
                  and mb_id in ( '{$member['mb_id']}', '전체회원' )
                  and cp_start <= '{$g4['time_ymd']}'
                  and cp_end >= '{$g4['time_ymd']}' ";
    $result = sql_query($sql);

    for($k=0; $row=sql_fetch_array($result); $k++) {
        // 최소결제금액이 있다면 체크
        if($row['cp_minimum'] && $tot_sell_amount < $row['cp_minimum']) {
            continue;
        }

        // 정액할인쿠폰에서 할인금액이 주문금액합(과세)보다 크다면
        if(!$row['cp_method'] && $row['cp_amount'] > $tot_tax_amount) {
            continue;
        }

        // 이미 사용한 쿠폰인지
        $sql = " select ch_no
                    from {$g4['yc4_coupon_history_table']}
                    where cp_id = '{$row['cp_id']}'
                      and mb_id = '{$member['mb_id']}'
                      and uq_id <> '$uq_id' ";
        $ch = sql_fetch($sql);

        if($ch['ch_no']){
            continue;
        }

        $o_cp_option .= '<option value="'.$row['cp_id'].'+'.$row['cp_method'].'+'.$row['cp_amount'].'+'.$row['cp_trunc'].'+'.$row['cp_minimum'].'+'.$row['cp_maximum'].'">'.$row['cp_subject'].'</option>'."\n";
    }
}
?>

<!-- 할인쿠폰 -->
<? if($s_cp_option || $o_cp_option) { ?>
<table width="100%" align="center" cellpadding="0" cellspacing="10" border="0">
<colgroup width="140">
<colgroup width="">
<tr>
    <td bgcolor="#F3F2FF" align="center">할인쿠폰</td>
    <td bgcolor="#FAFAFA" style="padding-left:10px">
        <table cellpadding="3">
        <colgroup width="100">
        <colgroup width="">
       <?php if($s_cp_option) { ?>
        <tr>
            <td>배송비할인</td>
            <td>
                <select name="s_cp_id">
                    <option value="">쿠폰선택</option>
                    <?php echo $s_cp_option; ?>
                </select>
            </td>
        </tr>
        <?php } ?>
        <?php if($o_cp_option) { ?>
        <tr>
            <td>결제금액할인</td>
            <td>
                <select name="o_cp_id">
                    <option value="">쿠폰선택</option>
                    <?php echo $o_cp_option; ?>
                </select>
                <? if($default['de_compound_tax_use']) { ?>
                <br />결제할인은 과세상품에만 적용됩니다.
                <? } ?>
            </td>
        </tr>
        <?php } ?>
        </table>
    </td>
</tr>
</table>
<? } ?>

<!-- 주문하시는 분 -->
<table width="100%" align="center" cellpadding="0" cellspacing="10" border="0">
<colgroup width="140">
<colgroup width="">
<tr>
    <td bgcolor="#F3F2FF" align="center"><img src="<? echo $g4['shop_img_path']; ?>/t_data01.gif"></td>
    <td bgcolor="#FAFAFA" style="padding-left:10px">
        <table cellpadding="3">
        <colgroup width="100">
        <colgroup width="">
        <tr>
            <td>이름</td>
            <td><input type="text" id="od_name" name="od_name" value="<? echo $member['mb_name']; ?>" maxlength="20" class="ed"></td>
        </tr>

        <? if (!$is_member) { // 비회원이면 ?>
        <tr>
            <td>비밀번호</td>
            <td><input type="password" name="od_pwd" class="ed" maxlength="20">
                영,숫자 3~20자 (주문서 조회시 필요)</td>
        </tr>
        <? } ?>

        <tr>
            <td>전화번호</td>
            <td><input type="text" name="od_tel" value="<? echo $member['mb_tel']; ?>" maxlength="20" class="ed"></td>
        </tr>
        <tr>
            <td>핸드폰</td>
            <td><input type="text" name="od_hp" value="<? echo $member['mb_hp']; ?>" maxlength="20" class="ed"></td>
        </tr>
        <tr>
            <td rowspan=2>주 소</td>
            <td>
                <input type="text" name="od_zip1" size="3" maxlength="3" value="<? echo $member['mb_zip1']; ?>" class="ed" readonly>
                -
                <input type="text" name="od_zip2" size="3" maxlength="3" value="<? echo $member['mb_zip2']; ?>" class="ed" readonly>
                <a href="javascript:;" onclick="win_zip('forderform', 'od_zip1', 'od_zip2', 'od_addr1', 'od_addr2');"><img
                    src="<? echo $g4['shop_img_path']?>/btn_zip_find.gif" border="0" align="absmiddle"></a>
            </td>
        </tr>
        <tr>
            <td>
                <input type="text" name="od_addr1" size="35" maxlength="50" value="<? echo $member['mb_addr1']; ?>" class="ed" readonly>
                <input type="text" name="od_addr2" size="20" maxlength="50" value="<? echo $member['mb_addr2']; ?>" class="ed"> (상세주소)
            </td>
        </tr>
        <tr>
            <td>E-mail</td>
            <td><input type="text" name="od_email" size="35" maxlength="100" value="<? echo $member['mb_email']; ?>" class="ed"></td>
        </tr>

        <? if ($default['de_hope_date_use']) { // 배송희망일 사용 ?>
        <tr>
            <td>희망배송일</td>
            <td><select name="od_hope_date">
                <option value="">선택하십시오.
                <?
                for ($i=0; $i<7; $i++) {
                    $sdate = date("Y-m-d", time()+86400*($default['de_hope_date_after']+$i));
                    echo "<option value=\"$sdate\">$sdate (".get_yoil($sdate).")\n";
                }
                ?>
                </select></td>
        </tr>
        <? } ?>
        </table>
    </td>
</tr>
</table>

<!-- 받으시는 분 -->
<table width="100%" align="center" cellpadding="0" cellspacing="10" border="0">
<colgroup width="140">
<colgroup width="">
<tr>
    <td bgcolor="#F3F2FF" align="center"><img src="<? echo $g4['shop_img_path']?>/t_data03.gif"></td>
    <td bgcolor="#FAFAFA" style="padding-left:10px">
        <table cellpadding="3">
        <colgroup width="100">
        <colgroup width="">
        <tr height="30">
            <td colspan="2">
                <input type="checkbox" id="same" name="same" onclick="javascript:gumae2baesong(document.forderform);">
                <label for="same"><b>주문하시는 분과 받으시는 분의 정보가 동일한 경우 체크하세요.</b></label></td></tr>
        <tr>
        <tr>
            <td>이름</td>
            <td><input type="text" name="od_b_name" class="ed" maxlength="20"></td>
        </tr>
        <tr>
            <td>전화번호</td>
            <td><input type="text" name="od_b_tel" class="ed" maxlength="20"></td>
        </tr>
        <tr>
            <td>핸드폰</td>
            <td><input type="text" name="od_b_hp" class="ed" maxlength="20"></td>
        </tr>
        <tr>
            <td rowspan="2">주 소</td>
            <td>
                <input type="text" name="od_b_zip1" size="3" maxlength="3" class="ed" readonly>
                -
                <input type="text" name="od_b_zip2" size="3" maxlength="3" class="ed" readonly>
                <a href="javascript:;" onclick="win_zip('forderform', 'od_b_zip1', 'od_b_zip2', 'od_b_addr1', 'od_b_addr2');"><img
                    src="<? echo $g4['shop_img_path']?>/btn_zip_find.gif" border="0" align="absmiddle"></a>
                </a>
            </td>
        </tr>
        <tr>
            <td>
                <input type="text" name="od_b_addr1" size="35" maxlength="50" class="ed" readonly>
                <input type="text" name="od_b_addr2" size="20" maxlength="50" class="ed"> (상세주소)
            </td>
        </tr>
        <tr>
            <td>전하실말씀</td>
            <td><textarea name="od_memo" rows="4" cols="60" class="ed"></textarea></td>
        </tr>
        </table>
    </td>
</tr>
</table>

<!-- 결제 정보 -->
<table width="100%" align="center" cellpadding="0" cellspacing="10" border="0">
<colgroup width="140">
<colgroup width="">
<tr>
    <td bgcolor="#FFEFFD" align="center"><img src="<? echo $g4['shop_img_path']?>/t_data04.gif"></td>
    <td bgcolor="#FAFAFA" style="padding-left:10px">
        <table cellpadding="3">
        <tr>
            <td height="50">
                <?
                $multi_settle == 0;
                $checked = "";

                $escrow_title = "";
                if ($default['de_escrow_use']) {
                    $escrow_title = "에스크로 ";
                }

                // 무통장입금 사용
                if ($default['de_bank_use']) {
                    $multi_settle++;
                    echo "<input type=\"radio\" id=\"od_settle_bank\" name=\"od_settle_case\" value=\"무통장\" $checked><label for=\"od_settle_bank\">무통장입금</label> &nbsp;&nbsp;&nbsp;";
                    $checked = "";
                }

                // 가상계좌 사용
                if ($default['de_vbank_use']) {
                    $multi_settle++;
                    echo "<input type=\"radio\" id=\"od_settle_vbank\" name=\"od_settle_case\" value=\"가상계좌\" $checked><label for=\"od_settle_vbank\">{$escrow_title} 가상계좌</label> &nbsp;&nbsp;&nbsp;";
                    $checked = "";
                }

                // 계좌이체 사용
                if ($default['de_iche_use']) {
                    $multi_settle++;
                    echo "<input type=\"radio\" id=\"od_settle_iche\" name=\"od_settle_case\" value=\"계좌이체\" $checked><label for=\"od_settle_iche\">{$escrow_title} 계좌이체</label> &nbsp;&nbsp;&nbsp;";
                    $checked = "";
                }

                // 휴대폰 사용
                if ($default['de_hp_use']) {
                    $multi_settle++;
                    echo "<input type=\"radio\" id=\"od_settle_hp\" name=\"od_settle_case\" value=\"휴대폰\" $checked><label for=\"od_settle_hp\">휴대폰</label> &nbsp;&nbsp;&nbsp;";
                    $checked = "";
                }

                // 신용카드 사용
                if ($default['de_card_use']) {
                    $multi_settle++;
                    echo "<input type=\"radio\" id=\"od_settle_card\" name=\"od_settle_case\" value=\"신용카드\" $checked><label for=\"od_settle_card\">신용카드</label> &nbsp;&nbsp;&nbsp;";
                    $checked = "";
                }

                // 회원이면서 포인트사용이면
                $temp_point = 0;
                if ($is_member && $config['cf_use_point'])
                {
                    // 포인트 결제 사용 포인트보다 회원의 포인트가 크다면
                    if ($member['mb_point'] >= $default['de_point_settle'])
                    {
                        $temp_point = $tot_amount * ($default['de_point_per'] / 100); // 포인트 결제 % 적용
                        $temp_point = (int)((int)($temp_point / 100) * 100); // 100점 단위

                        $member_point = (int)((int)($member['mb_point'] / 100) * 100); // 100점 단위
                        if ($temp_point > $member_point)
                            $temp_point = $member_point;

                        echo "<div style=\"margin-top:20px;\">결제포인트 : <input type=\"text\" id=\"od_temp_point\" name=\"od_temp_point\" value=\"0\" size=\"10\" class=\"ed\">점 (100점 단위로 입력하세요.)</div>";
                        echo "<div style=\"margin-top:10px;\">회원님의 보유포인트(".display_point($member['mb_point']).")중 <strong>".display_point($temp_point)."</strong>(주문금액  {$default['de_point_per']}%) 내에서 결제가 가능합니다.</div>";
                        $multi_settle++;
                    }
                }

                if ($multi_settle == 0)
                    echo "<br><span class=\"point\">결제할 방법이 없습니다.<br>운영자에게 알려주시면 감사하겠습니다.</span>";

                if (!$default['de_card_point'])
                    echo "<br><br>· '무통장입금' 이외의 결제 수단으로 결제하시는 경우 포인트를 적립해드리지 않습니다.";
                ?>
            </td>
        </tr>
        </table>

        <?
        if ($default['de_bank_use']) {
            // 은행계좌를 배열로 만든후
            $str = explode("\n", trim($default['de_bank_account']));
            if (count($str) <= 1)
            {
                $bank_account = "<input type=\"hidden\" name=\"od_bank_account\" value=\"$str[0]\">$str[0]\n";
            }
            else
            {
                $bank_account = "\n<select name=\"od_bank_account\">\n";
                $bank_account .= "<option value=\"\">--------------- 선택하십시오 ---------------\n";
                for ($i=0; $i<count($str); $i++)
                {
                    //$str[$i] = str_replace("\r", "", $str[$i]);
                    $str[$i] = trim($str[$i]);
                    $bank_account .= "<option value=\"$str[$i]\">$str[$i] \n";
                }
                $bank_account .= "</select> ";
            }
        ?>
        <div id="settle_bank" style="display:none;">
        <table width="100%">
        <tr>
            <td>계좌번호</td>
            <td><? echo $bank_account?></td>
        </tr>
        <tr>
            <td>입금자명</td>
            <td><input type="text" name="od_deposit_name" class="ed" size="10" maxlength="20"></td>
        </tr>
        </table>
        </div>
        <? } ?>

    </td>
</tr>
</table>

<!-- 결제하기 -->
<table width="100%" align="center" cellpadding="0" cellspacing="10" border="0">
<tr id="display_pay_button" style="display:none">
    <td align="center"><input type="image" id="order_submit" src="<? echo $g4['shop_img_path']; ?>/btn_next2.gif" border="0" alt="결제하기" />&nbsp;<a href='javascript:history.go(-1);'><img src="<? echo $g4['shop_img_path']; ?>/btn_back1.gif" alt="뒤로" border="0"></a></td>
</tr>
<!-- Payplus Plug-in 설치 안내 -->
<tr id="display_setup_message" style="display:none">
    <td align="center">
        <span class="red">결제를 계속 하시려면 상단의 노란색 표시줄을 클릭</span>하시거나<br/>
        <a href="http://pay.kcp.co.kr/plugin/file_vista/PayplusWizard.exe"><span class="bold">[수동설치]</span></a>를 눌러 Payplus Plug-in을 설치하시기 바랍니다.<br/>
        [수동설치]를 눌러 설치하신 경우 <span class="red bold">새로고침(F5)키</span>를 눌러 진행하시기 바랍니다.
    </td>
</tr>
</table>
</form>

<!-- <? if ($default[de_card_use] || $default[de_iche_use]) { echo "결제대행사 : $default[de_card_pg]"; } ?> -->

<? if ($default['de_escrow_use']) { ?>
<script type="text/javascript">
function escrow_foot_check()
{
    var status  = "width=500 height=450 menubar=no,scrollbars=no,resizable=no,status=no";
    var obj     = window.open("", "escrow_foot_pop", status);

    document.escrow_foot.method = "post";
    document.escrow_foot.target = "escrow_foot_pop";
    document.escrow_foot.action = "http://admin.kcp.co.kr/Modules/escrow/kcp_pop.jsp";

    document.escrow_foot.submit();
}
</script>

<form name="escrow_foot" method="post" action="http://admin.kcp.co.kr/Modules/escrow/kcp_pop.jsp">
<input type="hidden" name="site_cd" value="<? echo $default['de_kcp_mid']; ?>">
<table border="0" cellspacing="0" cellpadding="0">
<tr>
    <td align="center"><img src="<? echo $g4['shop_path']; ?>/img/marks_escrow/escrow_foot.gif" width="290" height="92" border="0" usemap="#Map"></td>
</tr>
<tr>
    <td style='line-height:150%;'>
        <br>
        <strong>에스크로(escrow) 제도란?</strong>
        <br>상거래 시에, 판매자와 구매자의 사이에 신뢰할 수 있는 중립적인 제삼자(여기서는 <a href="http://kcp.co.kr" target="_blank">KCP</a>)가 중개하여
        금전 또는 물품을 거래를 하도록 하는 것, 또는 그러한 서비스를 말한다. 거래의 안전성을 확보하기 위해 이용된다.
        (2006.4.1 전자상거래 소비자보호법에 따른 의무 시행)
        <br><br>
        5만원 이상의 현금 거래에만 해당(에스크로 결제를 선택했을 경우에만 해당)되며,
        신용카드로 구매하는 거래, 배송이 필요하지 않은 재화 등을 구매하는 거래(컨텐츠 등),
        5만원 미만의 현금 거래에는 해당되지 않는다.
        <br>
        <br>
    </td>
</tr>
</table>
<map name="Map" id="Map">
<area shape="rect" coords="5,62,74,83" href="javascript:escrow_foot_check()" alt="가입사실확인"  onfocus="this.blur()"/>
</map>
</form>
<? } ?>

<script type="text/javascript">
var old_zipcode = "";

$(function() {
    <? if($is_member) { ?>
    // 쿠폰적용
    $(".coupon-apply").click(function() {
        if($(this).hasClass("coupon-link")) {
            var $tr = $(this).closest("tr");
            var it_id = $tr.find("input[name^=it_id]").val();
            var cp_id = $tr.find("input[name^=cp_id]").val();
            var idx = $(".coupon-apply").index($(this));
            window.open("./cartcoupon.php?coupon="+cp_id+"&it_id="+it_id+"&idx="+idx+"&sw_direct=<? echo $sw_direct; ?>", "couponform", "width=600, height=500, left=100, top=50, scrollbars=yes");
        }
    });

    // 배송비쿠폰
    $("select[name=s_cp_id]").change(function() {
        var send_cost = parseInt($("input[name=od_send_cost]").val());
        var tot_amount = parseInt($("input[name=od_amount]").val());
        var area_send_cost = parseInt($("input[name=od_send_cost_area]").val());
        var coupon_amount = parseInt($("input[name=od_coupon_amount]").val());
        var send_coupon_amount = parseInt($("input[name=od_send_coupon_amount]").val());
        var val = $(this).val();
        var temp_send_cost = order_amount = 0;
        // 상품할인금액합
        var ch_amount = ch_amount_sum();

        if(val != "") {
            var str = val.split("+");
            var cp_id = str[0];
            var cp_amount = parseInt(str[1]);
            var cp_minimum = parseInt(str[2]);

            if(cp_minimum != 0 && cp_minimum > tot_amount) {
                $(this).val("");
                alert("주문금액이 "+number_format(str[2])+"원 이상이면 쿠폰을 사용할 수 있습니다.");
                return false;
            }

            // 배송할인금액이 배송비 보다 크다면
            temp_send_cost = send_cost - cp_amount;
            if(temp_send_cost < 0) {
                temp_send_cost = 0;
                cp_amount = send_cost;
            }

            order_amount = tot_amount - coupon_amount - ch_amount + temp_send_cost + area_send_cost;

            $("input[name=od_send_coupon]").val(cp_id);
            $("input[name=od_send_coupon_amount]").val(cp_amount);
            $("span#send_cost").text(number_format(String(temp_send_cost)));
            $("span#tot_amount b").text(number_format(String(order_amount)));
            $("input[name=good_mny]").val(order_amount);
        } else {
            temp_send_cost = send_cost;
            order_amount = tot_amount - coupon_amount - ch_amount + send_cost + area_send_cost;

            $("input[name=od_send_coupon]").val("");
            $("input[name=od_send_coupon_amount]").val(0);
            $("span#send_cost").text(number_format(String(send_cost)));
            $("span#tot_amount b").text(number_format(String(order_amount)));
            $("input[name=good_mny]").val(order_amount);
        }

        <? if($default['de_compound_tax_use']) { ?>
        // 복합과세처리
        if(temp_send_cost > 0 || area_send_cost > 0) {
            tax_calculate(send_cost, area_send_cost, coupon_amount, cp_amount);
        }
        <? } ?>
    });

    // 결제금액할인쿠폰
    $("select[name=o_cp_id]").change(function() {
        var send_cost = parseInt($("input[name=od_send_cost]").val());
        var tot_amount = parseInt($("input[name=od_amount]").val());
        var area_send_cost = parseInt($("input[name=od_send_cost_area]").val());
        var coupon_amount = parseInt($("input[name=od_coupon_amount]").val());
        var send_coupon_amount = parseInt($("input[name=od_send_coupon_amount]").val());
        var val = $(this).val();
        var dc_amount = order_amount = 0;
        // 상품할인금액 합
        var ch_amount = ch_amount_sum();

        <? if($default['de_compound_tax_use']) { ?>
        // 과세, 면세 금액 합
        var $t_el = $("input[name^=tax_mny]");
        var $v_el = $("input[name^=vat_mny]");
        var $f_el = $("input[name^=free_mny]");
        var comm_tax = comm_vat = comm_free = 0;

        $t_el.each(function(index) {
            var t_val = parseInt($(this).val());
            var v_val = parseInt($v_el.eq(index).val());
            var f_val = parseInt($f_el.eq(index).val());

            comm_tax += t_val;
            comm_vat += v_val;
            comm_free += f_val;
        });
        <? } ?>

        if(val != "") {
            var str = val.split("+");

            var cp_id = str[0];
            var cp_method = parseInt(str[1]);
            var cp_amount = parseInt(str[2]);
            var cp_trunc = parseInt(str[3]);
            var cp_minimum = parseInt(str[4]);
            var cp_maximum = parseInt(str[5]);

            if(cp_minimum != 0 && tot_amount < cp_minimum) {
                $(this).val("");
                alert("주문금액이 "+number_format(str[4])+"원 이상이면 쿠폰을 사용할 수 있습니다.");
                return false;
            }

            if(cp_method == 1) {
                dc_amount = Math.floor(((tot_amount * (cp_amount / 100)) / cp_trunc)) * cp_trunc;
                if(dc_amount > cp_maximum) {
                    dc_amount = cp_maximum;
                }
            } else {
                dc_amount = cp_amount;
            }

            <? if(!$default['de_compound_tax_use']) { ?>
            // 상품할인쿠폰 적용 후 금액이 결제할인금액보다 작다면
            if((tot_amount - ch_amount) < dc_amount) {
                $(this).val("");
                alert("결제금액이 "+number_format(String(dc_amount))+"원 이상이면 쿠폰을 사용할 수 있습니다.");
                return false;
            }
            <? } else { ?>
            // 과세금액합이 결제할인금액보다 작다면
            if((comm_tax + comm_vat) < dc_amount) {
                $(this).val("");
                alert("결제금액이 "+number_format(String(dc_amount))+"원 이상이면 쿠폰을 사용할 수 있습니다.");
                return false;
            }
            <? } ?>

            order_amount = tot_amount - ch_amount + send_cost + area_send_cost - send_coupon_amount - dc_amount;

            $("input[name=od_coupon]").val(cp_id);
            $("input[name=od_coupon_amount]").val(dc_amount);
            $("span#tot_amount b").text(number_format(String(order_amount)));
            $("input[name=good_mny]").val(order_amount);
        } else {
            dc_amount = 0;
            order_amount = tot_amount - ch_amount + send_cost + area_send_cost - send_coupon_amount;

            $("input[name=od_coupon]").val("");
            $("input[name=od_coupon_amount]").val(0);
            $("span#tot_amount b").text(number_format(String(order_amount)));
            $("input[name=good_mny]").val(order_amount);
        }

        <? if($default['de_compound_tax_use']) { ?>
        // 배송비과세계산
        var temp_send_cost = send_cost - send_coupon_amount;
        if(temp_send_cost > 0) {
            var s_tax = Math.round(temp_send_cost / 1.1);
            var s_vat = temp_send_cost - s_tax;

            comm_tax += s_tax;
            comm_vat += s_vat;
        }

        // 추가배송비과세계산
        if(area_send_cost > 0) {
            var as_tax = Math.round(area_send_cost / 1.1);
            var as_vat = area_send_cost - as_tax;

            comm_tax += as_tax;
            comm_vat += as_vat;
        }

        // 할인금액과세계산
        if(dc_amount > 0) {
            var dc_tax = Math.round(dc_amount / 1.1);
            var dc_vat = dc_amount - dc_tax;

            comm_tax -= dc_tax;
            comm_vat -= dc_vat;
        }

        $("input[name=comm_tax_mny]").val(comm_tax);
        $("input[name=comm_vat_mny]").val(comm_vat);
        $("input[name=comm_free_mny]").val(comm_free);
        <? } ?>
    });
    <? } ?>

    $("#order_submit").click(function(e) {
        e.preventDefault();

        var form = document.forderform;
        if(forderform_check(form) == true) {
            if(form.pay_method.value != "무통장") {
                if(jsf__pay( form )) {
                    form.submit();
                }
            } else {
                form.submit();
            }
        } else {
            return false;
        }
    });
});


function forderform_check(f)
{
    errmsg = "";
    errfld = "";
    var deffld = "";

    check_field(f.od_name, "주문하시는 분 이름을 입력하십시오.");
    if (typeof(f.od_pwd) != "undefined")
    {
        clear_field(f.od_pwd);
        if( (f.od_pwd.value.length<3) || (f.od_pwd.value.search(/([^A-Za-z0-9]+)/)!=-1) )
            error_field(f.od_pwd, "회원이 아니신 경우 주문서 조회시 필요한 비밀번호를 3자리 이상 입력해 주십시오.");
    }
    check_field(f.od_tel, "주문하시는 분 전화번호를 입력하십시오.");
    check_field(f.od_addr1, "우편번호 찾기를 이용하여 주문하시는 분 주소를 입력하십시오.");
    check_field(f.od_addr2, " 주문하시는 분의 상세주소를 입력하십시오.");
    check_field(f.od_zip1, "");
    check_field(f.od_zip2, "");

    clear_field(f.od_email);
    if(f.od_email.value=="" || f.od_email.value.search(/(\S+)@(\S+)\.(\S+)/) == -1)
        error_field(f.od_email, "E-mail을 바르게 입력해 주십시오.");

    if (typeof(f.od_hope_date) != "undefined")
    {
        clear_field(f.od_hope_date);
        if (!f.od_hope_date.value)
            error_field(f.od_hope_date, "희망배송일을 선택하여 주십시오.");
    }

    check_field(f.od_b_name, "받으시는 분 이름을 입력하십시오.");
    check_field(f.od_b_tel, "받으시는 분 전화번호를 입력하십시오.");
    check_field(f.od_b_addr1, "우편번호 찾기를 이용하여 받으시는 분 주소를 입력하십시오.");
    check_field(f.od_b_addr2, "받으시는 분의 상세주소를 입력하십시오.");
    check_field(f.od_b_zip1, "");
    check_field(f.od_b_zip2, "");

    var od_settle_bank = document.getElementById("od_settle_bank");
    if (od_settle_bank) {
        if (od_settle_bank.checked) {
            check_field(f.od_bank_account, "계좌번호를 선택하세요.");
            check_field(f.od_deposit_name, "입금자명을 입력하세요.");
        }
    }

    // 배송비를 받지 않거나 더 받는 경우 아래식에 + 또는 - 로 대입
    f.od_send_cost.value = parseInt(f.od_send_cost.value);

    if (errmsg)
    {
        alert(errmsg);
        errfld.focus();
        return false;
    }

    var settle_case = document.getElementsByName("od_settle_case");
    var settle_check = false;
    var settle_method = "";
    for (i=0; i<settle_case.length; i++)
    {
        if (settle_case[i].checked)
        {
            settle_check = true;
            settle_method = settle_case[i].value;
            break;
        }
    }
    if (!settle_check)
    {
        alert("결제방식을 선택하십시오.");
        return false;
    }

    var tot_amount = <? echo (int)$tot_amount; ?>;
    var max_point  = <? echo (int)$temp_point; ?>;

    var temp_point = 0;
    if (typeof(f.od_temp_point) != "undefined") {
        if (f.od_temp_point.value)
        {
            temp_point = parseInt(f.od_temp_point.value);

            if (temp_point < 0) {
                alert("포인트를 0 이상 입력하세요.");
                f.od_temp_point.select();
                return false;
            }

            if (temp_point > tot_amount) {
                alert("주문금액 보다 많이 포인트결제할 수 없습니다.");
                f.od_temp_point.select();
                return false;
            }

            if (temp_point > <? echo (int)$member['mb_point']; ?>) {
                alert("회원님의 포인트보다 많이 결제할 수 없습니다.");
                f.od_temp_point.select();
                return false;
            }

            if (temp_point > max_point) {
                alert(max_point + "점 이상 결제할 수 없습니다.");
                f.od_temp_point.select();
                return false;
            }

            if (parseInt(parseInt(temp_point / 100) * 100) != temp_point) {
                alert("포인트를 100점 단위로 입력하세요.");
                f.od_temp_point.select();
                return false;
            }
        }
    }

    if (document.getElementById("od_settle_iche")) {
        if (document.getElementById("od_settle_iche").checked) {
            if (tot_amount - temp_point < 150) {
                alert("계좌이체는 150원 이상 결제가 가능합니다.");
                return false;
            }
        }
    }

    if (document.getElementById("od_settle_card")) {
        if (document.getElementById("od_settle_card").checked) {
            if (tot_amount - temp_point < 1000) {
                alert("신용카드는 1000원 이상 결제가 가능합니다.");
                return false;
            }
        }
    }

    if (document.getElementById("od_settle_hp")) {
        if (document.getElementById("od_settle_hp").checked) {
            if (tot_amount - temp_point < 350) {
                alert("휴대폰은 350원 이상 결제가 가능합니다.");
                return false;
            }
        }
    }

    // pay_method 설정
    switch(settle_method)
    {
        case "계좌이체":
            f.pay_method.value = "010000000000";
            break;
        case "가상계좌":
            f.pay_method.value = "001000000000";
            break;
        case "휴대폰":
            f.pay_method.value = "000010000000";
            break;
        case "신용카드":
            f.pay_method.value = "100000000000";
            break;
        default:
            f.pay_method.value = "무통장";
            break;
    }

    // kcp 결제정보설정
    f.buyr_name.value = f.od_name.value;
    f.buyr_mail.value = f.od_email.value;
    f.buyr_tel1.value = f.od_tel.value;
    f.buyr_tel2.value = f.od_hp.value;
    f.rcvr_name.value = f.od_b_name.value;
    f.rcvr_tel1.value = f.od_b_tel.value;
    f.rcvr_tel2.value = f.od_b_hp.value;
    f.rcvr_mail.value = f.od_email.value;
    f.rcvr_zipx.value = f.od_b_zip1.value + f.od_b_zip2.value;
    f.rcvr_add1.value = f.od_b_addr1.value;
    f.rcvr_add2.value = f.od_b_addr2.value;

    return true;
}

// 구매자 정보와 동일합니다.
function gumae2baesong(f)
{
    f.od_b_name.value = f.od_name.value;
    f.od_b_tel.value  = f.od_tel.value;
    f.od_b_hp.value   = f.od_hp.value;
    f.od_b_zip1.value = f.od_zip1.value;
    f.od_b_zip2.value = f.od_zip2.value;
    f.od_b_addr1.value = f.od_addr1.value;
    f.od_b_addr2.value = f.od_addr2.value;

    // 추가배송비체크
    area_sendcost_check();
}

function area_sendcost_check()
{
    var zip1 = $.trim($("input[name=od_b_zip1]").val());
    var zip2 = $.trim($("input[name=od_b_zip2]").val());

    if(!zip1 || !zip2) {
        return false;
    }

    var zip = zip1 + zip2;

    if(old_zipcode == "" || old_zipcode != zip) {
        $.post(
            './ordersendcostcheck.php',
            { zip: zip },
            function(data) {
                var amount = parseInt($("input[name=od_amount]").val());
                var sendcost = parseInt($("input[name=od_send_cost]").val());
                var od_coupon = parseInt($("input[name=od_coupon_amount]").val());
                var send_coupon = parseInt($("input[name=od_send_coupon_amount]").val());
                var sendarea = 0;
                // 상품할인금액합
                var ch_amount = ch_amount_sum();

                if(data) {
                    sendarea = parseInt(data);

                    $("input[name=od_send_cost_area]").val(data);

                    var total = amount + sendcost - od_coupon - send_coupon - ch_amount + sendarea;
                    $("span#tot_amount b").text(number_format(String(total)));

                    old_zipcode = zip;

                    // 추가배송비표시
                    var esc_text = "<tr><td colspan=\"6\" height=\"1\" bgcolor=\"#E7E9E9\"></td></tr>";
                    esc_text += "<tr id=\"area_send_cost\">";
                    esc_text += "<td height=\"28\" colspan=\"4\" align=\"right\">추가배송비 : </td>";
                    esc_text += "<td align=\"right\">"+number_format(data)+"</td>";
                    esc_text += "<td>&nbsp;</td>";
                    esc_text += "</tr>";

                    var $el = $("span#send_cost");
                    if($el.length) {
                        $el.closest("tr").after(esc_text);
                    } else {
                        $el = $("span#tot_amount");
                        $el.closest("tr").prev().before(esc_text);
                    }
                } else {
                    sendarea = 0;
                    var total = amount + send_cost - od_coupon - send_coupon - ch_amount;
                    $("span#tot_amount b").text(number_format(String(total)));

                    $("input[name=od_send_cost_area]").val(0);
                    $("#area_send_cost").prev().remove();
                    $("#area_send_cost").remove();
                }

                <? if($default['de_compound_tax_use']) { ?>
                // 복합과세처리
                if(sendcost > 0 || sendarea > 0) {
                    tax_calculate(sendcost, sendarea, od_coupon, send_coupon);
                }
                <? } ?>
            }
        );
    }
}

function ch_amount_sum()
{
    var ch_amount = 0;

    $("input[name^=od_ch_amount]").each(function() {
        var value = $(this).val();
        if(value == "") {
            value = 0;
        }

        ch_amount += parseInt(value);
    });

    return ch_amount;
}

<? if($default['de_compound_tax_use']) { ?>
function tax_calculate(send_cost, area_send_cost, od_coupon, send_coupon)
{
    // 과세, 면세 금액 합
    var $t_el = $("input[name^=tax_mny]");
    var $v_el = $("input[name^=vat_mny]");
    var $f_el = $("input[name^=free_mny]");
    var comm_tax = comm_vat = comm_free = 0;
    var t_val = v_val = f_val = 0;

    $t_el.each(function(index) {
        t_val = parseInt($(this).val());
        v_val = parseInt($v_el.eq(index).val());
        f_val = parseInt($f_el.eq(index).val());

        comm_tax += t_val;
        comm_vat += v_val;
        comm_free += f_val;
    });

    // 배송비과세계산
    var temp_send_cost = send_cost - send_coupon;
    if(temp_send_cost > 0) {
        var s_tax = Math.round(temp_send_cost / 1.1);
        var s_vat = temp_send_cost - s_tax;

        comm_tax += s_tax;
        comm_vat += s_vat;
    }

    // 추가배송비과세계산
    if(area_send_cost > 0) {
        var as_tax = Math.round(area_send_cost / 1.1);
        var as_vat = area_send_cost - as_tax;

        comm_tax += as_tax;
        comm_vat += as_vat;
    }

    // 할인금액과세계산
    if(od_coupon > 0) {
        var dc_tax = Math.round(od_coupon / 1.1);
        var dc_vat = od_coupon - dc_tax;

        comm_tax -= dc_tax;
        comm_vat -= dc_vat;
    }

    $("input[name=comm_tax_mny]").val(comm_tax);
    $("input[name=comm_vat_mny]").val(comm_vat);
    $("input[name=comm_free_mny]").val(comm_free);
}
<? } ?>

$(function() {
    $("#od_settle_bank").bind("click", function() {
        $("[name=od_deposit_name]").val( $("[name=od_b_name]").val() );
        $("#settle_bank").show();
    });

    $("#od_settle_iche,#od_settle_card,#od_settle_vbank").bind("click", function() {
        $("#settle_bank").hide();
    });

    // 지역 추가배송비 체크
    $("input[name=od_b_addr2]").blur(function() {
        area_sendcost_check();
    });
});
</script>

<?
include_once('./_tail.php');
?>