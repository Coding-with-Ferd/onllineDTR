<?php

if (!isset($pagination) || !is_array($pagination)) {
    return;
}

$defaults = [
    'current_page' => 1,
    'total_items' => 0,
    'per_page' => 8,
    'page_param' => 'page',
    'base_url' => $_SERVER['PHP_SELF'],
    'query_params' => [],
    'max_links' => 7,
];

$config = array_merge($defaults, $pagination);
$config['current_page'] = max(1, intval($config['current_page']));
$config['per_page'] = max(1, intval($config['per_page']));

$totalPages = (int) max(1, ceil($config['total_items'] / $config['per_page']));
$currentPage = min($config['current_page'], $totalPages);

$rawParams = is_array($config['query_params']) ? $config['query_params'] : [];
if (isset($rawParams[$config['page_param']])) {
    unset($rawParams[$config['page_param']]);
}

$baseUrl = $config['base_url'];
$parsed = parse_url($baseUrl);
$path = $parsed['path'] ?? $baseUrl;
$query = [];
if (!empty($parsed['query'])) {
    parse_str($parsed['query'], $query);
}
$query = array_merge($query, $rawParams);
$queryString = http_build_query($query);
$baseLink = $path . ($queryString !== '' ? '?' . $queryString : '');
$linkSeparator = strpos($baseLink, '?') === false ? '?' : '&';

function build_pagination_url($baseLink, $linkSeparator, $pageParam, $pageNumber) {
    return $baseLink . $linkSeparator . urlencode($pageParam) . '=' . urlencode($pageNumber);
}

$startPage = max(1, $currentPage - (int) floor($config['max_links'] / 2));
$endPage = min($totalPages, $startPage + $config['max_links'] - 1);
if ($endPage - $startPage + 1 < $config['max_links']) {
    $startPage = max(1, $endPage - $config['max_links'] + 1);
}

?>

<?php if ($totalPages > 1): ?>
    <nav class="pagination" aria-label="Pagination">
        <a href="<?php echo htmlspecialchars(build_pagination_url($baseLink, $linkSeparator, $config['page_param'], $currentPage - 1)); ?>"
           class="<?php echo $currentPage === 1 ? 'disabled' : ''; ?>"
           aria-disabled="<?php echo $currentPage === 1 ? 'true' : 'false'; ?>">
            Prev
        </a>

        <?php if ($startPage > 1): ?>
            <a href="<?php echo htmlspecialchars(build_pagination_url($baseLink, $linkSeparator, $config['page_param'], 1)); ?>">1</a>
            <?php if ($startPage > 2): ?>
                <span class="ellipsis">&hellip;</span>
            <?php endif; ?>
        <?php endif; ?>

        <?php for ($page = $startPage; $page <= $endPage; $page++): ?>
            <?php if ($page === $currentPage): ?>
                <a href="<?php echo htmlspecialchars(build_pagination_url($baseLink, $linkSeparator, $config['page_param'], $page)); ?>" class="active"><?php echo $page; ?></a>
            <?php else: ?>
                <a href="<?php echo htmlspecialchars(build_pagination_url($baseLink, $linkSeparator, $config['page_param'], $page)); ?>"><?php echo $page; ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($endPage < $totalPages): ?>
            <?php if ($endPage < $totalPages - 1): ?>
                <span class="ellipsis">&hellip;</span>
            <?php endif; ?>
            <a href="<?php echo htmlspecialchars(build_pagination_url($baseLink, $linkSeparator, $config['page_param'], $totalPages)); ?>"><?php echo $totalPages; ?></a>
        <?php endif; ?>

        <a href="<?php echo htmlspecialchars(build_pagination_url($baseLink, $linkSeparator, $config['page_param'], min($totalPages, $currentPage + 1))); ?>"
           class="<?php echo $currentPage === $totalPages ? 'disabled' : ''; ?>"
           aria-disabled="<?php echo $currentPage === $totalPages ? 'true' : 'false'; ?>">
            Next
        </a>
    </nav>
<?php endif; ?>
