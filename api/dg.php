<?php
// 文件名：api/dg.php
// 部署位置：必须在 api 文件夹内！
// 核心：使用 backup.php 同款稳定源 -> 转换为巨魔智能体专用格式

// 1. 净化环境，防止杂音
error_reporting(0);
ob_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

// 2. 获取参数
// App 会把关键词拼在 URL 后面 (?word=周杰伦)
$word = $_GET['word'] ?? $_GET['keyword'] ?? '';

// 3. 校验参数
if (empty($word)) {
    ob_end_clean();
    echo json_encode(["code" => 400, "msg" => "请输入歌名"]);
    exit;
}

// 4. 请求源接口 (使用你确认好用的 liuzhijin 聚合源)
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

// 5. 格式转换 (关键步骤)
// liuzhijin 返回的是 data 列表，我们要取第 0 个，并改名为 App 能懂的字段
if ($data && isset($data['data'][0])) {
    
    $song = $data['data'][0];

    // 重新打包成 App 要的格式 (扁平化)
    $output = [
        "code"      => 200,
        "title"     => $song['title'] ?? "未知歌名",
        "singer"    => $song['author'] ?? "未知歌手", // 源接口叫 author，App要 singer
        "cover"     => $song['pic'] ?? "",            // 源接口叫 pic，App要 cover
        "music_url" => $song['url'] ?? "",            // 源接口叫 url，App要 music_url
        "lyric"     => $song['lrc'] ?? "[00:00.00]暂无歌词"
    ];

} else {
    // 没搜到的情况
    $output = ["code" => 404, "msg" => "未找到歌曲"];
}

// 6. 输出结果
ob_end_clean();
echo json_encode($output, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit;
?>
