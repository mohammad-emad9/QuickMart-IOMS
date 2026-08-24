<?php
/**
 * QuickMart IOMS - Product Service
 * Product application operations
 */

require_once __DIR__ . '/../../infrastructure/database/product-repository.php';
require_once __DIR__ . '/../shared/transaction-retry.php';

/**
 * Retrieve one product through the product repository.
 *
 * @return array|false
 */
function getProductById(PDO $pdo, $productId)
{
    return findProductById($pdo, $productId);
}

/**
 * Retrieve the filtered product list through the product repository.
 *
 * @return array
 */
function getProductList(PDO $pdo, $category, $status, $search)
{
    return findProductList($pdo, $category, $status, $search);
}

/**
 * Create one product with its initial stock status.
 *
 * @return array
 */
function createProduct(PDO $pdo, $name, $category, $quantity, $price, $threshold)
{
    $maxAttempts = 3;

    for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
        try {
            return createProductAttempt($pdo, $name, $category, $quantity, $price, $threshold);
        } catch (Throwable $e) {
            if ($attempt === $maxAttempts || !isRetryableTransactionFailure($e)) {
                throw $e;
            }

            backoffBeforeTransactionRetry($attempt);
        }
    }
}

/**
 * Execute one complete product creation transaction.
 *
 * @return array
 */
function createProductAttempt(PDO $pdo, $name, $category, $quantity, $price, $threshold)
{
    $pdo->beginTransaction();

    try {
        $productId = generateProductId($pdo);

        $status = STATUS_NORMAL;
        if ($quantity <= 0) {
            $status = STATUS_OUT_OF_STOCK;
        } elseif ($quantity <= $threshold) {
            $status = STATUS_LOW_STOCK;
        }

        insertProduct($pdo, $productId, $name, $category, $quantity, $price, $status, $threshold);

        $pdo->commit();

        return [
            'product_id' => $productId,
            'name' => $name,
            'category' => $category,
            'quantity' => $quantity,
            'price' => $price,
            'status' => $status,
            'threshold' => $threshold
        ];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }
}

/**
 * Update one product and return the final persisted product.
 *
 * @return array|false|null
 */
function updateProduct(PDO $pdo, $productId, array $updates)
{
    $currentProduct = findProductById($pdo, $productId);

    if (!$currentProduct) {
        return null;
    }

    if (empty($updates)) {
        return false;
    }

    $finalQuantity = array_key_exists('quantity', $updates)
        ? $updates['quantity']
        : $currentProduct['Quantity'];
    $finalThreshold = array_key_exists('threshold', $updates)
        ? $updates['threshold']
        : $currentProduct['Threshold'];

    $status = STATUS_NORMAL;
    if ($finalQuantity <= 0) {
        $status = STATUS_OUT_OF_STOCK;
    } elseif ($finalQuantity <= $finalThreshold) {
        $status = STATUS_LOW_STOCK;
    }

    updateProductFields($pdo, $productId, $updates);
    persistProductStatus($pdo, $productId, $status);

    return findProductById($pdo, $productId);
}

/**
 * Delete a product when it has no order references.
 *
 * @return array
 */
function deleteProduct(PDO $pdo, $productId)
{
    $product = findProductForDeletion($pdo, $productId);

    if (!$product) {
        return ['status' => 'not_found'];
    }

    $orderCount = countProductOrderReferences($pdo, $productId);

    if ($orderCount > 0) {
        return [
            'status' => 'referenced',
            'order_count' => $orderCount
        ];
    }

    deleteProductRecord($pdo, $productId);

    return [
        'status' => 'deleted',
        'product_id' => $product['Product_ID'],
        'name' => $product['Name']
    ];
}
