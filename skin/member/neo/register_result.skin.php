<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<article>
    <header>
        <h1>회원가입이 완료되었습니다.</h1>
    </header>

    <p>
        <?=$mb[mb_name]?>님의 회원가입을 진심으로 축하합니다.<br>
        회원님의 아이디는 <?=$mb[mb_id]?> 입니다.<br>
        회원님의 패스워드는 아무도 알 수 없는 암호화 코드로 저장되므로 안심하셔도 좋습니다.<br>
        아이디, 패스워드 분실시에는 회원가입시 입력하신 패스워드 분실시 질문, 답변을 이용하여 찾을 수 있습니다.
    </p>

    <? if ($config[cf_use_email_certify]) { ?>
    <p>
        회원 가입 시 입력하신 이메일 <strong><?=$mb[mb_email]?></strong>로 발송된 내용을 확인하신 후 인증하셔야 회원가입이 완료됩니다.
    </p>
    <? } ?>

    <p>
        회원의 탈퇴는 언제든지 가능하며 탈퇴 후 일정기간이 지난 후, 회원님의 모든 소중한 정보는 삭제하고 있습니다.<br>
        감사합니다.
    </p>

    <a href="<?=$g4[url]?>/">메인으로</a>

</article>