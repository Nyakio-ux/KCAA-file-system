<?php

// Helper functions
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

function formatDate($dateString) {
    return date('M j, Y H:i', strtotime($dateString));
}

function getStatusBadge($status) {
    switch (strtolower($status)) {
        case 'approved': return 'green';
        case 'rejected': return 'red';
        case 'pending review': return 'yellow';
        case 'under review': return 'blue';
        case 'pending approval': return 'indigo';
        case 'revision required': return 'purple';
        default: return 'gray';
    }
}
?>