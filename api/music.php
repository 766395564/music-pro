<?php
header('Content-Type: application/json; charset=utf-8');

// 1. 获取关键词并处理乱码
$keyword = $_GET['keyword'] ?? '周杰伦';
if (!preg_match('//u', $keyword)) {
    $keyword = mb_convert_encoding($keyword, 'UTF-8', 'GBK');
}

// 2. 这里的地址是网易云的搜索接口
$url = "https://music.163.com/api/search/get/web?s=" . urlencode($keyword) . "&type=1&limit=10";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Referer: https://music.163.com/']);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$results = [];

// 3. 整理结果给 App 使用
if (isset($data['result']['songs'])) {
    foreach ($data['result']['songs'] as $song) {
        $results[] = [
            'songName' => $song['name'],
            'artist'   => $song['artists'][0]['name'],
            'album'    => $song['album']['name'],
            'songUrl'  => "http://music.163.com/song/media/outer/url?id=" . $song['id'] . ".mp3"
        ];
    }
}

// 4. 输出最终结果
echo json_encode([
    "code" => 200,
    "message" => "success",
    "data" => [
        "keyword" => $keyword,
        "results" => $results
    ]
], JSON_UNESCAPED_UNICODE);

