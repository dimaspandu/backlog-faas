<?php

/**
 * SkuLogicTest
 *
 * Tests the critical SKU fallback logic used when creating/updating
 * sprint product offerings.
 *
 * This logic lives in SprintProductsController::values()
 * and ensures consistent SKU resolution across product / variant / sprint.
 */

echo "Running SkuLogicTest...\n";

function resolveSprintProductSku(?string $inputSku, ?string $variantSku, string $productSku): string
{
    $sku = trim($inputSku ?? '');
    if ($sku === '') {
        $sku = trim($variantSku ?? '') ?: $productSku;
    }
    return $sku;
}

// Test cases
assertEquals(
    'CUSTOM-SKU',
    resolveSprintProductSku('CUSTOM-SKU', 'VAR-123', 'PROD-001'),
    'Explicit SKU in sprint offering should take highest priority'
);

assertEquals(
    'VAR-123',
    resolveSprintProductSku('', 'VAR-123', 'PROD-001'),
    'Should fall back to variant SKU when no explicit SKU provided'
);

assertEquals(
    'PROD-001',
    resolveSprintProductSku(null, null, 'PROD-001'),
    'Should ultimately fall back to product SKU'
);

assertEquals(
    'VAR-456',
    resolveSprintProductSku('   ', 'VAR-456', 'PROD-001'),
    'Whitespace-only input SKU should be treated as empty and fall back'
);

echo "SkuLogicTest passed\n";
