<?php
if (!defined('_GNUBOARD_')) exit;

@ini_set('memory_limit', '512M');

function it_img_thumb($filename, $filepath, $thumb_width, $thumb_height, $is_create=false)
{
    return thumbnail($filename, $filepath, $filepath, $thumb_width, $thumb_height, $is_create);
}

//function thumbnail($bo_table, $file, $width, $height, $is_create=false)
function thumbnail($filename, $source_path, $target_path, $thumb_width, $thumb_height, $is_create)
{
    global $g4;

    $thumb_filename = preg_replace("/\.[^\.]+$/i", "", $filename); // 확장자제거

    if (!is_dir($target_path)) {
        @mkdir($target_path, 0707);
        @chmod($target_path, 0707);
    }

    $thumb_file = "$target_path/{$thumb_filename}_{$thumb_width}x{$thumb_height}.png";
    $thumb_time = @filemtime($thumb_file);
    $source_file = "$source_path/$filename";
    $source_time = @filemtime($source_file);
    if (file_exists($thumb_file)) {
        if ($is_create == false && $source_time < $thumb_time) {
            return str_replace($target_path.'/', '', $thumb_file);
        }
    }

    $size = @getimagesize($source_file);
    // 이미지 파일이 없거나 아님
    if (!$size[0]) {
        if (!$thumb_height) $thumb_height = $thumb_width;
        $thumb_file = "$target_path/noimg_{$thumb_width}x{$thumb_height}.png";
        if (!file_exists($thumb_file)) {
            $target = imagecreate($thumb_width, $thumb_height);
            imagecolorallocate($target, 250, 250, 250);
            imagecopy($target, $target, 0, 0, 0, 0, $thumb_width, $thumb_height);
            imagepng($target, $thumb_file, 0);
            @chmod($thumb_file, 0606); // 추후 삭제를 위하여 파일모드 변경
        }
        return str_replace($target_path.'/', '', $thumb_file);
    }

    $is_imagecopyresampled = false;
    $is_large = false;

    $src = null;
    if ($size[2] == 1) {
        $src = imagecreatefromgif($source_file);
    } else if ($size[2] == 2) {
        $src = imagecreatefromjpeg($source_file);
    } else if ($size[2] == 3) {
        $src = imagecreatefrompng($source_file);
    }

    if ($thumb_width) {
        if ($thumb_height) {
            $rate = $thumb_width / $size[0];
            $tmp_height = (int)($size[1] * $rate);
            if ($tmp_height < $thumb_height) {
                $dst = imagecreatetruecolor($thumb_width, $thumb_height);
                $bgcolor = imagecolorallocate($dst, 250, 250, 250); // 배경색 여기야!!!
                imagefill($dst, 0, 0, $bgcolor);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $thumb_width, $tmp_height, $size[0], $size[1]);
            } else {
                $dst = imagecreatetruecolor($thumb_width, $thumb_height);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $thumb_width, $thumb_height, $size[0], $size[1]);
            }
        } else {
            $rate = $thumb_width / $size[0];
            $tmp_height = (int)($size[1] * $rate);
            $dst = imagecreatetruecolor($thumb_width, $tmp_height);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $thumb_width, $tmp_height, $size[0], $size[1]);
        }
    }

    imagepng($dst, $thumb_file, 0); // 0 (no compression) ~ 9
    chmod($thumb_file, 0606); // 추후 삭제를 위하여 파일모드 변경
    return str_replace($target_path.'/', '', $thumb_file);
}
?>