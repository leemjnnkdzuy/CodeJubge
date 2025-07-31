<?php
function formatRating($rating) {
    if (!isset($rating) || $rating == -1) {
        return 'Chưa có xếp hạng';
    }
    return $rating;
}

function getRatingLevel($rating) {
    if (!isset($rating) || $rating == -1) {
        return 'Chưa xếp hạng';
    }
    
    if ($rating < 1400) {
        return 'Người mới';
    } elseif ($rating < 1600) {
        return 'Học viên';
    } elseif ($rating < 1900) {
        return 'Chuyên gia';
    } elseif ($rating < 2100) {
        return 'Cao thủ';
    } else {
        return 'Huyền thoại';
    }
}

function getRatingColor($rating) {
    if (!isset($rating) || $rating == -1) {
        return '#6c757d';
    }
    
    if ($rating < 1400) {
        return '#28a745';
    } elseif ($rating < 1600) {
        return '#17a2b8';
    } elseif ($rating < 1900) {
        return '#007bff';
    } elseif ($rating < 2100) {
        return '#6f42c1';
    } else {
        return '#dc3545';
    }
}

function getRatingProgress($rating) {
    if (!isset($rating) || $rating == -1) {
        return 0;
    }
    
    if ($rating < 1400) {
        return ($rating - 1200) / 200 * 100;
    } elseif ($rating < 1600) {
        return ($rating - 1400) / 200 * 100;
    } elseif ($rating < 1900) {
        return ($rating - 1600) / 300 * 100;
    } elseif ($rating < 2100) {
        return ($rating - 1900) / 200 * 100;
    } else {
        return 100;
    }
}

?>
