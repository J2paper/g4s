<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if((int)get_cookie('ck_passwordlost_count') > 2)
    alert_close('아이디/패스워드 찾기를 기준회수이상 시도하였습니다.');
?>

<div id="find_info" class="new_win">
    <h1>아이디/패스워드 찾기</h1>

    <form name="fpasswordlost" method="post" action="<?=$action_url?>" onsubmit="return fpasswordlost_submit(this);" autocomplete="off">
    <input type="hidden" name="kcpcert_no" value="">
    <input type="hidden" name="kcpcert_time" value="">
    <fieldset id="find_info_fs">
        <p>
            회원가입 시 등록하신  이름과 핸드폰번호를 입력해 주세요.
        </p>
        <label for="mb_name">이름<strong class="sound_only">필수</strong></label>
        <input type="text" id="mb_name" name="mb_name" class="fs_input hangul nospace required" required size="30">
        <label for="mb_hp">핸드폰번호<strong class="sound_only">필수</strong></label>
        <input type="text" id="mb_hp" name="mb_hp" class="fs_input required" maxlength="20" required size="30">
        <button type="button" id="win_kcpcert">휴대폰인증</button>
        <noscript>휴대폰인증을 위해서는 자바스크립트 사용이 가능해야합니다.</noscript>
    </fieldset>
    <?=captcha_html(); ?>
    <div class="btn_win">
        <input type="submit" class="btn_submit" value="확인">
        <a href="javascript:window.close();" class="btn_cancel">창닫기</a>
    </div>
    </form>
</div>

<? // 휴대폰인증 form
include_once(G4_BBS_PATH.'/kcp/kcpcert_form.php');
?>

<script>
$(function() {
    $('#win_kcpcert').click(function() {
        var name = document.fpasswordlost.mb_name.value;
        auth_type_check(name);
        return false;
    });
});

function fpasswordlost_submit(f)
{
    // 휴대폰인증 검사
    if(f.kcpcert_time.value == "") {
        alert("휴대폰 본인인증을 해주세요.");
        return false;
    }

    <? echo chk_captcha_js(); ?>

    return true;
}

self.focus();
document.fpasswordlost.mb_name.focus();

$(function() {
    var sw = screen.width;
    var sh = screen.height;
    var cw = document.body.clientWidth;
    var ch = document.body.clientHeight;
    var top  = sh / 2 - ch / 2 - 100;
    var left = sw / 2 - cw / 2;
    moveTo(left, top);
});
</script>