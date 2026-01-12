<?php
// 1. 暴力清除所有干扰，确保只输出 JSON
error_reporting(0);
ob_start();
header('Content-Type: application/json; charset=utf-8');

// 2. 获取参数
$word = $_GET['word'] ?? $_GET['keyword'] ?? '';

// 3. 如果没有关键词，输出提示并结束
if (empty($word)) {
    ob_end_clean();
    echo json_encode(["code" => 400, "msg" => "请输入歌名"]);
    exit;
}

// 4. 调用源接口
$apiUrl = "https://api.liuzhijin.cn/music/?type=search&word=" . urlencode($word);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

// 5. 格式化输出 (严格匹配制作要求)
if ($data && isset($data['data'][0])) {
    $song = $data['data'][0];
    $output = [
        "code"      => 200,
        "title"     => $song['title'] ?: "未知歌名",
        "singer"    => $song['author'] ?: "未知歌手",
        "cover"     => $song['pic'] ?: "",
        "music_url" => $song['url'] ?: "",
        "lyric"     => $song['lrc'] ?: "[00:00.00]暂无歌词"
    ];
} else {
    $output = ["code" => 404, "msg" => "未找到歌曲"];
}

// 6. 输出最终结果
ob_end_clean();
echo json_encode($output, JSON_UNESCAPED_UNICODE);
exit;
?>
