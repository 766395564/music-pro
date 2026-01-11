<?php
header('Content-Type: application/json; charset=utf-8');

// 1. 获取 App 传来的参数
$keyword = $_GET['keyword'] ?? '';
$page = (int)($_GET['page'] ?? 1);

// 2. 这里的 $targetUrl 指向你那个“肯定可用”的接口地址
// 并补全它在报错中要求的 type 和 source 参数
$targetUrl = "https://music-dl.sayqz.com/api?type=aggregateSearch&source=all&keyword=" . urlencode($keyword) . "&page=" . $page;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $targetUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response = curl_exec($ch);
curl_close($ch);

// 3. 解析拿到的数据
$raw = json_decode($response, true);

// 4. 关键：重新包装成【巨魔智能体】文档截图里的样子
// 文档要求：code, message, data (含 keyword, limit, page, platforms, results)
$finalData = [
    "code" => 200,
    "message" => "success",
    "data" => [
        "keyword" => $keyword,
        "limit" => 10,
        "page" => $page,
        "platforms" => ["all"], 
        "results" => []
    ]
];

// 如果原接口有数据，就把 results 提取出来放进我们的格式里
if (isset($raw['data']['results'])) {
    $finalData['data']['results'] = $raw['data']['results'];
} elseif (isset($raw['results'])) {
    $finalData['data']['results'] = $raw['results'];
}

// 5. 输出
echo json_encode($finalData, JSON_UNESCAPED_UNICODE);
