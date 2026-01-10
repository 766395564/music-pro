<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");

// 1. 严格按照作者截图 12 的要求，获取 keyword 和 page 参数
$kw = $_GET['keyword'] ?? '周杰伦';
$page = intval($_GET['page'] ?? 1);

// 2. 调用网易云 API 获取真实数据
$search_url = "http://music.163.com/api/search/get/web?csrf_token=v&type=1&offset=".(($page-1)*10)."&limit=10&s=" . urlencode($kw);
$search_res = file_get_contents($search_url);
$search_data = json_decode($search_res, true);

$results = [];
if (!empty($search_data['result']['songs'])) {
    foreach ($search_data['result']['songs'] as $song) {
        // 每一个字段都严格对应截图 11 中的“必须”标注
        $results[] = [
            "id" => (string)$song['id'],
            "name" => (string)$song['name'],
            "artist" => (string)$song['artists'][0]['name'],
            "album" => (string)$song['album']['name'],
            "platform" => "netease", // 对应截图 11 的 platform 必填项
            "url" => "https://music.163.com/song/media/outer/url?id=" . $song['id'] . ".mp3",
            "pic" => (string)$song['album']['picUrl'],
            "lrc" => "" 
        ];
    }
}

// 3. 严格构造外层 data 结构，包含作者红线标注的所有字段
$output = [
    "code" => 200,
    "message" => "success",
    "data" => [
        "keyword" => $kw,
        "limit" => 10,
        "page" => $page,        // 对应截图 11 的 page 必填项
        "platforms" => ["netease"], // 对应截图 11 的 platforms 必填项
        "results" => $results
    ]
];

// 输出干净的 JSON，确保没有多余的斜杠
echo json_encode($output, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
