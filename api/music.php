<?php
header('Content-Type: application/json; charset=utf-8');

// 获取参数
$keyword = $_GET['keyword'] ?? '周杰伦';
$type = $_GET['type'] ?? 'search';
$source = $_GET['source'] ?? 'netease';

// 学习点：它背后的逻辑其实是去请求各大平台的公开API
// 我们在这里以网易云为例进行 100% 模拟
$url = "https://music.163.com/api/search/get/web?s=" . urlencode($keyword) . "&type=1&limit=10";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Referer: https://music.163.com/']);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$results = [];

if (isset($data['result']['songs'])) {
    foreach ($data['result']['songs'] as $song) {
        // 学习点：按照你给的参考图，严格输出字段
        $results[] = [
            'id'       => (string)$song['id'],
            'name'     => $song['name'],
            'artist'   => $song['artists'][0]['name'],
            'album'    => $song['album']['name'],
            'platform' => $source,
            'url'      => "https://music.163.com/song/media/outer/url?id=" . $song['id'] . ".mp3"
        ];
    }
}

// 最终返回格式：完全模仿你给出的成功截图
echo json_encode([
    "code" => 200,
    "message" => "success",
    "data" => [
        "results" => $results
    ]
], JSON_UNESCAPED_UNICODE);
