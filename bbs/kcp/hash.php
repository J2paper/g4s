<?
include_once('./_common.php');
include_once('./kcpcert_common.php');

$ordr_idxx = "1234";
$site_cd = "S6186";
$year = "00";
$month = "00";
$day = "00";
$sex_code = "00";
$local_code = "00";

$ct_cert = new C_CT_CLI;
$ct_cert->mf_clear();

$hash_data = $site_cd.$ordr_idxx.$user_name.$year.$month.$day.$sex_code.$local_code; // site_cd 와 ordr_idxx 는 필수 값입니다.

$up_hash = $ct_cert->make_hash_data( $home_dir, $hash_data );

echo $up_hash;
?>