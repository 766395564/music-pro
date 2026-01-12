<?php
// 1. 彻底关闭报错，确保 App 只收到纯净 JSON
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

// 2. 获取参数
$keyword = $_GET['keyword'] ?? '';
$page = $_GET['page'] ?? 1;

// 3. 空值处理
if (empty($keyword)) {
    echo json_encode(["code" => 200, "message" => "success", "data" => ["results" => []]]);
    exit;
}

// 4. 请求备用源 (Liuzhijin 聚合接口)
$apiUrl = "https://api.liuzhijin.cn/music/?type=search&word=" . urlencode($keyword);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
curl_close($ch);

// 5. 解析并精准翻译字段 (严格匹配你 music.php 的输出标准)
$remoteData = json_decode($response, true);
$finalResults = [];

if ($remoteData && isset($remoteData['data'])) {
    foreach ($remoteData['data'] as $item) {
        // 这里的字段名必须和你的 music.php 一模一样，App 才能识别
        $finalResults[] = [
            "song_name"   => $item['title'],
            "artist_name" => $item['author'],
            "album_name"  => "聚合备用源",
            "platform"    => $item['type'],
            "music_url"   => $item['url'],
            "pic_url"     => $item['pic'],
            "lyric_url"   => $item['lrc']
        ];
    }
}

// 6. 封装成 App 认识的完整结构
$output = [
    "code" => 200,
    "message" => "success",
    "data" => [
        "keyword" => $keyword,
        "limit"   => 10,
        "page"    => (int)$page,
        "platforms" => ["all"],
        "results" => $finalResults
    ]
];

echo json_encode($output, JSON_UNESCAPED_UNICODE);
?>
