<?php
header('Content-Type: application/json');
$keyword = $_GET['keyword'] ?? '周杰伦';
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// 使用网易云公开搜索接口
$url = "https://music.163.com/api/search/get/web?s=" . urlencode($keyword) . "&type=1&offset=" . $offset . "&limit=" . $limit;

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
        $results[] = [
            'songName' => $song['name'],
            'artist' => $song['artists'][0]['name'],
            'album' => $song['album']['name'],
            'songUrl' => "http://music.163.com/song/media/outer/url?id=" . $song['id'] . ".mp3"
        ];
    }
}

echo json_encode([
    "code" => 200,
    "message" => "success",
    "data" => [
        "keyword" => $keyword,
        "results" => $results
    ]
], JSON_UNESCAPED_UNICODE);
