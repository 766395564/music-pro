<?php
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

// 1. 获取关键词
$keyword = $_GET['word'] ?? $_GET['keyword'] ?? '';

if (empty($keyword)) {
    die(json_encode(["code" => 400, "msg" => "请输入歌名"]));
}

// 2. 调用核心解析源 (确保使用你验证过的这个源)
$apiUrl = "https://api.liuzhijin.cn/music/?type=search&word=" . urlencode($keyword);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
curl_close($ch);

$remoteData = json_decode($response, true);

// 3. 严格匹配你要求的格式 (去除所有多余层级)
if ($remoteData && isset($remoteData['data'][0])) {
    $item = $remoteData['data'][0];
    
    $output = [
        "code"      => 200,
        "title"     => $item['title'] ?: "未知歌名",
        "singer"    => $item['author'] ?: "未知歌手",
        "cover"     => $item['pic'] ?: "",
        "music_url" => $item['url'] ?: "",
        "lyric"     => $item['lrc'] ?: "[00:00.00]暂无歌词"
    ];
} else {
    $output = ["code" => 404, "msg" => "未找到歌曲"];
}

// 4. 重点：清除所有潜在的干扰输出，只打印 JSON
ob_clean(); 
echo json_encode($output, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
exit;
?>
