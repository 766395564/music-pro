<?php
// 1. 屏蔽干扰，清理缓冲区
error_reporting(0);
ob_start();
header('Content-Type: application/json; charset=utf-8');

// 2. 获取参数
$word = $_GET['word'] ?? $_GET['keyword'] ?? '';
if (empty($word)) {
    ob_end_clean();
    die(json_encode(["code" => 400, "msg" => "请输入歌名"]));
}

// 3. 【核心修改】使用网易云官方搜索接口 (这是专门用来搜歌的，不是解析的)
// 这里的 logic 是：直接问网易云要数据，不经过任何第三方中转
$searchUrl = "http://music.163.com/api/search/get/web?s=" . urlencode($word) . "&type=1&offset=0&total=true&limit=1";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $searchUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_REFERER, "http://music.163.com/");
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36");
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

// 4. 提取数据并拼接官方播放链接
if ($data && isset($data['result']['songs'][0])) {
    $song = $data['result']['songs'][0];
    $songId = $song['id'];
    
    // 官方播放链接拼接规则
    $musicUrl = "http://music.163.com/song/media/outer/url?id=" . $songId . ".mp3";
    
    $output = [
        "code"      => 200,
        "title"     => $song['name'],
        "singer"    => $song['artists'][0]['name'],
        "cover"     => $song['album']['picUrl'] ?? "",
        "music_url" => $musicUrl, // 这是一个真实的MP3链接
        "lyric"     => "[00:00.00]请欣赏: " . $song['name'] // 搜索接口不带歌词，暂时用这个代替
    ];
} else {
    $output = ["code" => 404, "msg" => "未找到歌曲"];
}

// 5. 输出纯净JSON
ob_end_clean();
echo json_encode($output, JSON_UNESCAPED_UNICODE);
exit;
?>
