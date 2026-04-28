<?php

/**
 * Product Management View
 * View and manage all products
 *
 * @var string $title
 * @var string $message
 * @var string $error
 * @var array $products Array with product data: name_prd, farm_name_ven, category_name, is_active_prd, id_prd
 */
?>

<section class="card">
  <div class="mb-6 flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-center">
    <h1><?= h($title ?? 'Product Management') ?></h1>
    <a href="<?= url('/admin') ?>" class="link-primary">Back to Dashboard</a>
  </div>

  <?php if (!empty($message)): ?>
    <div class="alert-success mb-4" data-flash>
      <?= h($message) ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div class="alert-error mb-4" data-flash>
      <?= h($error) ?>
    </div>
  <?php endif; ?>

  <?php if (empty($products)): ?>
    <div class="card mt-4">
      <p>No products found.</p>
    </div>
  <?php else: ?>
    <div class="card mt-6">
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead>
            <tr class="border-b">
              <th class="p-3 text-left font-semibold">Product Name</th>
              <th class="p-3 text-left font-semibold">Vendor</th>
              <th class="p-3 text-left font-semibold">Category</th>
              <th class="p-3 text-left font-semibold">Status</th>
              <th class="p-3 text-left font-semibold">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($products as $product): ?>
              <tr class="border-t hover:bg-gray-50">
                <td class="p-3">
                  <strong><?= h($product['name_prd']) ?></strong>
                </td>
                <td class="p-3 text-gray-700"><?= h($product['farm_name_ven'] ?? 'N/A') ?></td>
                <td class="p-3 text-gray-700"><?= h($product['category_name'] ?? 'N/A') ?></td>
                <td class="p-3">
                  <span class="inline-block rounded px-2.5 py-1 text-fluid-sm text-white <?= !empty($product['is_active_prd']) ? 'bg-brand-primary' : 'bg-gray-400' ?>">
                    <?= !empty($product['is_active_prd']) ? 'Active' : 'Inactive' ?>
                  </span>
                </td>
                <td class="p-3">
                  <a href="<?= url('/admin/products/upload-photo?product_id=' . (int)$product['id_prd']) ?>" class="text-brand-primary hover:text-brand-primary-hover hover:underline">Upload Photo</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>
</section>
