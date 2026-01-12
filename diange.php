<?php
// 强制屏蔽所有错误输出，确保不干扰 JSON
error_reporting(0);
ob_start(); 

header('Content-Type: application/json; charset=utf-8');

// 1. 获取参数
$word = $_GET['word'] ?: $_GET['keyword'] ?: '';

if (empty($word)) {
    ob_end_clean();
    die(json_encode(["code" => 400, "msg" => "请输入歌名"]));
}

// 2. 核心请求
$apiUrl = "https://api.liuzhijin.cn/music/?type=search&word=" . urlencode($word);
$response = file_get_contents($apiUrl);
$data = json_decode($response, true);

// 3. 严格适配点歌格式
if ($data && isset($data['data'][0])) {
    $song = $data['data'][0];
    $res = [
        "code" => 200,
        "title" => $song['title'],
        "singer" => $song['author'],
        "cover" => $song['pic'],
        "music_url" => $song['url'],
        "lyric" => $song['lrc']
    ];
} else {
    $res = ["code" => 404, "msg" => "未找到歌曲"];
}

// 4. 关键：清除缓冲区，只输出点歌 JSON
ob_end_clean();
echo json_encode($res, JSON_UNESCAPED_UNICODE);
exit;
?>
