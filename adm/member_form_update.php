<?
$sub_menu = "200100";
include_once('./_common.php');

if ($w == 'u')
    check_demo();

auth_check($auth[$sub_menu], 'w');

check_token();

if ($member['mb_password'] != sql_password($_POST['admin_password'])) {
    alert('패스워드가 다릅니다.');
}

$mb_id = mysql_real_escape_string(trim($_POST['mb_id']));

// 핸드폰번호 체크
$mb_hp = $_POST['mb_hp'];
if($mb_hp) {
    $mb_hp = preg_replace("/[^0-9]/", "", $mb_hp);
    $hp_len = strlen($mb_hp);
    if($hp_len == 10) {
        $mb_hp = preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "\\1-\\2-\\3", $mb_hp);
    } else if($hp_len == 11) {
        $mb_hp = preg_replace("/([0-9]{3})([0-9]{4})([0-9]{4})/", "\\1-\\2-\\3", $mb_hp);
    } else {
        alert('핸드폰번호를 올바르게 입력해 주십시오.');
    }

    // 중복체크
    $sql = " select count(*) as cnt from {$g4['member_table']} where mb_hp = '$mb_hp' and mb_id <> '$mb_id' ";

    $row = sql_fetch($sql);
    if($row['cnt'])
        alert('다른 회원이 사용 중인 핸드폰번호입니다.');
}

$sql_common = " mb_name = '{$_POST['mb_name']}',
                         mb_nick = '{$_POST['mb_nick']}',
                         mb_email = '{$_POST['mb_email']}',
                         mb_homepage = '{$_POST['mb_homepage']}',
                         mb_tel = '{$_POST['mb_tel']}',
                         mb_hp = '$mb_hp',
                         mb_zip1 = '{$_POST['mb_zip1']}',
                         mb_zip2 = '{$_POST['mb_zip2']}',
                         mb_addr1 = '{$_POST['mb_addr1']}',
                         mb_addr2 = '{$_POST['mb_addr2']}',
                         mb_birth = '{$_POST['mb_birth']}',
                         mb_sex = '{$_POST['mb_sex']}',
                         mb_signature = '{$_POST['mb_signature']}',
                         mb_leave_date = '{$_POST['mb_leave_date']}',
                         mb_intercept_date='{$_POST['mb_intercept_date']}',
                         mb_memo = '{$_POST['mb_memo']}',
                         mb_mailling = '{$_POST['mb_mailling']}',
                         mb_sms = '{$_POST['mb_sms']}',
                         mb_open = '{$_POST['mb_open']}',
                         mb_profile = '{$_POST['mb_profile']}',
                         mb_level = '{$_POST['mb_level']}',
                         mb_1 = '{$_POST['mb_1']}',
                         mb_2 = '{$_POST['mb_2']}',
                         mb_3 = '{$_POST['mb_3']}',
                         mb_4 = '{$_POST['mb_4']}',
                         mb_5 = '{$_POST['mb_5']}',
                         mb_6 = '{$_POST['mb_6']}',
                         mb_7 = '{$_POST['mb_7']}',
                         mb_8 = '{$_POST['mb_8']}',
                         mb_9 = '{$_POST['mb_9']}',
                         mb_10 = '{$_POST['mb_10']}' ";

if ($w == '')
{
    $mb = get_member($mb_id);
    if ($mb['mb_id'])
        alert('이미 존재하는 회원입니다.\\nＩＤ : '.$mb['mb_id'].'\\n이름 : '.$mb['mb_name'].'\\n별명 : '.$mb['mb_nick'].'\\n메일 : '.$mb['mb_email']);

    if ($mb['mb_nick'] == $mb_nick)
        alert('이미 존재하는 별명입니다.\\nＩＤ : '.$mb['mb_id'].'\\n이름 : '.$mb['mb_name'].'\\n별명 : '.$mb['mb_nick'].'\\n메일 : '.$mb['mb_email']);

    if ($mb['mb_email'] == $mb_email)
        alert('이미 존재하는 E-mail 입니다.\\nＩＤ : '.$mb['mb_id'].'\\n이름 : '.$mb['mb_name'].'\\n별명 : '.$mb['mb_nick'].'\\n메일 : '.$mb['mb_email']);

    sql_query(" insert into {$g4['member_table']} set mb_id = '{$mb_id}', mb_password = '".sql_password($mb_password)."', mb_datetime = '".G4_TIME_YMDHIS."', mb_ip = '{$_SERVER['REMOTE_ADDR']}', mb_email_certify = '".G4_TIME_YMDHIS."', {$sql_common} ");
}
else if ($w == 'u')
{
    $mb = get_member($mb_id);
    if (!$mb['mb_id'])
        alert('존재하지 않는 회원자료입니다.');

    if ($is_admin != 'super' && $mb['mb_level'] >= $member['mb_level'])
        alert('자신보다 권한이 높거나 같은 회원은 수정할 수 없습니다.');

    if ($_POST['mb_id'] == $member['mb_id'] && $_POST['mb_level'] != $mb['mb_level'])
        alert($mb['mb_id'].' : 로그인 중인 관리자 레벨은 수정 할 수 없습니다.');

    $mb_dir = substr($mb_id,0,2);

    // 회원 아이콘 삭제
    if ($del_mb_icon)
        @unlink(G4_DATA_PATH.'/member/'.$mb_dir.'/'.$mb_id.'.gif');

    // 아이콘 업로드
    if (is_uploaded_file($_FILES['mb_icon']['tmp_name'])) {
        if (!preg_match("/(\.gif)$/i", $_FILES['mb_icon']['name'])) {
            alert($_FILES['mb_icon']['name'] . '은(는) gif 파일이 아닙니다.');
        }

        if (preg_match("/(\.gif)$/i", $_FILES['mb_icon']['name'])) {
            @mkdir(G4_DATA_PATH.'/member/'.$mb_dir, 0707);
            @chmod(G4_DATA_PATH.'/member/'.$mb_dir, 0707);

            $dest_path = G4_DATA_PATH.'/member/'.$mb_dir.'/'.$mb_id.'.gif';

            move_uploaded_file($_FILES['mb_icon']['tmp_name'], $dest_path);
            chmod($dest_path, 0606);

            if (file_exists($dest_path)) {
                $size = getimagesize($dest_path);
                // 아이콘의 폭 또는 높이가 설정값 보다 크다면 이미 업로드 된 아이콘 삭제
                if ($size[0] > $config['cf_member_icon_width'] || $size[1] > $config['cf_member_icon_height']) {
                    @unlink($dest_path);
                }
            }
        }
    }

    if ($mb_password)
        $sql_password = " , mb_password = '".sql_password($mb_password)."' ";
    else
        $sql_password = "";

    if ($passive_certify)
        $sql_certify = " , mb_email_certify = '".G4_TIME_YMDHIS."' ";
    else
        $sql_certify = "";

    $sql = " update {$g4['member_table']}
                set {$sql_common}
                     {$sql_password}
                     {$sql_certify}
                where mb_id = '{$mb_id}' ";
    sql_query($sql);
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');

goto_url('./member_form.php?'.$qstr.'&amp;w=u&amp;mb_id='.$mb_id, false);
?>