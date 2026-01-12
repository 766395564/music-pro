<?php
// 文件名：api/dg.php
// 核心策略：利用 backup.php 同款稳定源 -> 转换为巨魔智能体专用格式

// 1. 净化环境
error_reporting(0);
ob_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

// 2. 获取参数
$word = $_GET['word'] ?? $_GET['keyword'] ?? '';
if (empty($word)) {
    ob_end_clean();
    echo json_encode(["code" => 400, "msg" => "请输入歌名"]);
    exit;
}

// 3. 请求源接口 (这里换成了和你 backup.php 一模一样的地址！)
$apiUrl = "https://api.liuzhijin.cn/music/?type=search&word=" . urlencode($word);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

// 4. 格式转换 (关键：源接口返回的是 data[0]，我们要把它拎出来)
if ($data && isset($data['data'][0])) {
    
    $song = $data['data'][0];

    // 重新打包成 App 要的格式
    $output = [
        "code"      => 200,
        "title"     => $song['title'] ?? "未知歌名",
        "singer"    => $song['author'] ?? "未知歌手", // 源接口叫 author
        "cover"     => $song['pic'] ?? "",            // 源接口叫 pic
        "music_url" => $song['url'] ?? "",            // 源接口叫 url
        "lyric"     => $song['lrc'] ?? "[00:00.00]暂无歌词"
    ];

} else {
    $output = ["code" => 404, "msg" => "未找到歌曲"];
}

// 5. 输出
ob_end_clean();
echo json_encode($output, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit;
?>
