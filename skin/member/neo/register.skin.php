<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<form name="fregister" method="POST" onsubmit="return fregister_submit(this);" autocomplete="off">

<section>
    <h2>회원가입약관</h2>
    <textarea readonly><?=get_text($config[cf_stipulation])?></textarea>
    <input type="radio" id="agree11" name="agree" value="1"><label for="agree11">동의합니다.</label>
    <input type="radio" id="agree10" name="agree" value="0"><label for="agree10">동의하지 않습니다.</label>
</section>


<section>
    <h2>개인정보취급방침</h2>
    <textarea readonly><?=get_text($config[cf_privacy])?></textarea>
    <input type="radio" id="agree21" name="agree2" value="1"><label for="agree21">동의합니다.</label>
    <input type="radio" id="agree20" name="agree2" value="0"><label for="agree20">동의하지 않습니다.</label>
</section>

<div class="btn_confirm">
    <input type="submit" value="회원가입">
</div>

</form>


<script type="text/javascript">
function fregister_submit(f) 
{
    var agree1 = document.getElementsByName("agree");
    if (!agree1[0].checked) {
        alert("회원가입약관의 내용에 동의하셔야 회원가입 하실 수 있습니다.");
        agree1[0].focus();
        return false;
    }

    var agree2 = document.getElementsByName("agree2");
    if (!agree2[0].checked) {
        alert("개인정보취급방침의 내용에 동의하셔야 회원가입 하실 수 있습니다.");
        agree2[0].focus();
        return false;
    }

    f.action = "./register_form.php";
    return true;
}

if (typeof(document.fregister.mb_name) != "undefined")
    document.fregister.mb_name.focus();
</script>