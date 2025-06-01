<?php
function ftp_connect_logged() {
    if (!isset($_SESSION['ftp_host'], $_SESSION['ftp_user'], $_SESSION['ftp_pass'])) {
        return false;
    }
    $conn_id = ftp_connect($_SESSION['ftp_host'], 21, 10);
    if (!$conn_id) return false;
    if (!@ftp_login($conn_id, $_SESSION['ftp_user'], $_SESSION['ftp_pass'])) {
        ftp_close($conn_id);
        return false;
    }
    ftp_pasv($conn_id, true);
    return $conn_id;
}

function ftp_list_dir($conn_id, $dir) {
    $list = ftp_rawlist($conn_id, $dir);
    $items = [];
    foreach ($list as $item) {
        $chunks = preg_split("/\s+/", $item, 9);
        if (count($chunks) < 9) continue;
        $name = $chunks[8];
        if ($name == '.' || $name == '..') continue;
        $is_dir = ($chunks[0][0] === 'd');
        $items[] = [
            'name' => $name,
            'is_dir' => $is_dir,
            'raw' => $item
        ];
    }
    return $items;
}
?>
