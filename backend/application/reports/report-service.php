<?php
/*
 * Report aggregation service.
 */

require_once __DIR__ . '/../../infrastructure/database/report-repository.php';

/**
 * Normalize a database count before it reaches the JSON response.
 */
function normalizeReportInteger($value)
{
    if (is_int($value)) {
        return $value;
    }

    if (is_string($value) && preg_match('/^-?[0-9]+$/', trim($value))) {
        $integer = filter_var(trim($value), FILTER_VALIDATE_INT);

        return $integer === false ? 0 : $integer;
    }

    if (is_float($value) && is_finite($value)) {
        return (int) $value;
    }

    return 0;
}

/**
 * Normalize a database amount before it reaches the JSON response.
 */
function normalizeReportAmount($value)
{
    if (is_string($value)) {
        $value = trim($value);
    }

    if (!is_int($value) && !is_float($value) && !is_string($value)) {
        return 0.0;
    }

    if (!is_numeric($value)) {
        return 0.0;
    }

    $amount = (float) $value;

    return is_finite($amount) ? round($amount, 2) : 0.0;
}

/**
 * Preserve safe scalar text from a report row without allowing arrays or
 * objects to reach json_encode().
 */
function normalizeReportText($value, $fallback = '')
{
    if (is_string($value)) {
        return $value;
    }

    if (is_int($value) || is_float($value)) {
        return (string) $value;
    }

    return $fallback;
}

/**
 * Preserve nullable database text fields such as Party_Name.
 */
function normalizeNullableReportText($value)
{
    if ($value === null) {
        return null;
    }

    return normalizeReportText($value, null);
}

/**
 * Keep report collections safe when a repository row is unexpectedly absent
 * or malformed. The repository normally returns a list of associative rows.
 */
function normalizeReportRows($rows, $normalizer)
{
    if (!is_array($rows)) {
        return [];
    }

    $normalizedRows = [];
    foreach ($rows as $row) {
        if (is_array($row)) {
            $normalizedRows[] = $normalizer($row);
        }
    }

    return $normalizedRows;
}

/**
 * Build the dashboard report statistics from repository data.
 *
 * @return array
 */
function getReportStatistics(PDO $pdo)
{
    $totalOrders = normalizeReportInteger(getTotalOrderCount($pdo));
    $ordersByType = findOrdersByType($pdo);

    $sellOrders = 0;
    $purchaseOrders = 0;
    $totalSales = 0;
    $totalPurchases = 0;

    if (is_array($ordersByType)) {
        foreach ($ordersByType as $row) {
            if (!is_array($row)) {
                continue;
            }

            $orderType = $row['Order_Type'] ?? null;
            $orderCount = normalizeReportInteger($row['count'] ?? 0);
            $orderAmount = normalizeReportAmount($row['total_amount'] ?? 0);

            if ($orderType === 'Sell') {
                $sellOrders = $orderCount;
                $totalSales = $orderAmount;
            } elseif ($orderType === 'Purchase') {
                $purchaseOrders = $orderCount;
                $totalPurchases = $orderAmount;
            }
        }
    }

    $totalProducts = normalizeReportInteger(getTotalProductCount($pdo));
    $lowStockCount = normalizeReportInteger(getLowStockProductCount($pdo));

    $productsByCategory = normalizeReportRows(
        findProductsByCategory($pdo),
        function (array $row) {
            return [
                'Category' => normalizeReportText($row['Category'] ?? null),
                'count' => normalizeReportInteger($row['count'] ?? 0),
                'total_stock' => normalizeReportInteger($row['total_stock'] ?? 0)
            ];
        }
    );

    $recentOrders = normalizeReportRows(
        findRecentOrders($pdo),
        function (array $row) {
            return [
                'Order_ID' => normalizeReportText($row['Order_ID'] ?? null),
                'Order_Type' => normalizeReportText($row['Order_Type'] ?? null),
                'Party_Name' => normalizeNullableReportText($row['Party_Name'] ?? null),
                'Staff_Name' => normalizeNullableReportText($row['Staff_Name'] ?? null),
                'Total_Amount' => normalizeReportAmount($row['Total_Amount'] ?? 0),
                'Order_Date' => normalizeNullableReportText($row['Order_Date'] ?? null)
            ];
        }
    );

    $topProducts = normalizeReportRows(
        findTopProducts($pdo),
        function (array $row) {
            return [
                'Name' => normalizeReportText($row['Name'] ?? null),
                'total_sold' => normalizeReportInteger($row['total_sold'] ?? 0),
                'revenue' => normalizeReportAmount($row['revenue'] ?? 0)
            ];
        }
    );

    $monthlyTrend = normalizeReportRows(
        findMonthlySalesTrend($pdo),
        function (array $row) {
            return [
                'month' => normalizeReportText($row['month'] ?? null),
                'order_count' => normalizeReportInteger($row['order_count'] ?? 0),
                'sales' => normalizeReportAmount($row['sales'] ?? 0),
                'purchases' => normalizeReportAmount($row['purchases'] ?? 0)
            ];
        }
    );

    return [
        'summary' => [
            'total_orders' => $totalOrders,
            'sell_orders' => $sellOrders,
            'purchase_orders' => $purchaseOrders,
            'total_sales' => $totalSales,
            'total_purchases' => $totalPurchases,
            'net_revenue' => $totalSales - $totalPurchases,
            'total_products' => $totalProducts,
            'low_stock_count' => $lowStockCount
        ],
        'products_by_category' => $productsByCategory,
        'recent_orders' => $recentOrders,
        'top_products' => $topProducts,
        'monthly_trend' => $monthlyTrend
    ];
}
