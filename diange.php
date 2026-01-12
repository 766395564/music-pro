<?php
/**
 * 智能体专用点歌接口 (永不失效个人版)
 * 严格适配文档规范：title, singer, cover, music_url, lyric
 */

// 1. 强制纯净输出，屏蔽环境警告
error_reporting(0);
header('Content-Type: application/json; charset=utf-8');

// 2. 获取搜索参数 (同时支持 word 或 keyword 传参)
$keyword = $_GET['word'] ?? $_GET['keyword'] ?? '';

// 3. 初始检查：如果没有输入内容
if (empty($keyword)) {
    echo json_encode([
        "code" => 400,
        "msg"  => "请输入需要搜索的歌名"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 4. 调用解析源 (Liuzhijin 聚合源，包含多平台数据)
$apiUrl = "https://api.liuzhijin.cn/music/?type=search&word=" . urlencode($keyword);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 设置10秒超时，防止智能体等待过久
$response = curl_exec($ch);
curl_close($ch);

$remoteData = json_decode($response, true);

// 5. 格式化转换 (严格匹配你提供的文档第2张图的响应格式)
if ($remoteData && isset($remoteData['data'][0])) {
    // 默认取搜索结果的第一条，因为点歌通常只需要最匹配的一首
    $item = $remoteData['data'][0];
    
    $output = [
        "code"      => 200,
        "title"     => $item['title'] ?: "未知歌名",
        "singer"    => $item['author'] ?: "未知歌手",
        "cover"     => $item['pic'] ?: "", // 对应文档 cover (歌曲封面)
        "music_url" => $item['url'] ?: "", // 对应文档 music_url (播放地址)
        "lyric"     => $item['lrc'] ?: "[00:00.00]暂无歌词" // 对应文档 lyric (歌词文本)
    ];
} else {
    // 未找到歌曲时的标准返回
    $output = [
        "code" => 404,
        "msg"  => "未找到相关歌曲"
    ];
}

// 6. JSON_UNESCAPED_UNICODE 确保中文不被转码
echo json_encode($output, JSON_UNESCAPED_UNICODE);
?>
