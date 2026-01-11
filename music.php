<?php
header('Content-Type: application/json; charset=utf-8');

// 1. 接收参数 (严格匹配文档截图中的 GET 请求参数)
$keyword = $_GET['keyword'] ?? '';
$page = (int)($_GET['page'] ?? 1);

// 2. 调用那个“必成”的接口，并补全它需要的 type 和 source
$targetUrl = "https://music-dl.sayqz.com/api?type=aggregateSearch&source=all&keyword=" . urlencode($keyword) . "&page=" . $page;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $targetUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
curl_close($ch);

$raw = json_decode($response, true);

// 3. 严格按照文档 要求的响应格式进行封装
$output = [
    "code" => 200,
    "message" => "success",
    "data" => [
        "keyword" => $keyword,
        "limit" => 10,
        "page" => $page,
        "platforms" => ["all"], // 文档要求必须有参与聚合的平台列表
        "results" => []
    ]
];

// 4. 将提取的数据放入 results 数组
if (isset($raw['data']['results'])) {
    $output['data']['results'] = $raw['data']['results'];
} elseif (isset($raw['results'])) {
    $output['data']['results'] = $raw['results'];
}

// 5. 输出最终 JSON
echo json_encode($output, JSON_UNESCAPED_UNICODE);
