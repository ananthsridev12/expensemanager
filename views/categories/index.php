<?php
$activeModule = 'categories';
$categories = $categories ?? [];

include __DIR__ . '/../partials/nav.php';
?>
<main class="module-content">
    <header class="module-header">
        <h1>Categories</h1>
        <p>Create income, expense, or transfer categories and organize subcategories.</p>
    </header>

    <section class="module-panel">
        <h2>New category</h2>
        <form method="post" class="module-form">
            <input type="hidden" name="form" value="category">
            <label>
                Category name
                <input type="text" name="name" required>
            </label>
            <label>
                Type
                <select name="type">
                    <option value="income">Income</option>
                    <option value="expense" selected>Expense</option>
                    <option value="transfer">Transfer</option>
                </select>
            </label>
            <button type="submit">Create category</button>
        </form>
    </section>

    <section class="module-panel">
        <h2>New subcategory</h2>
        <?php if (count($categories) === 0): ?>
            <p class="muted">Create a category first to add its subcategories.</p>
        <?php else: ?>
            <form method="post" class="module-form">
                <input type="hidden" name="form" value="subcategory">
                <label>
                    Parent category
                    <select name="category_id" required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?> (<?= $category['type'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    Subcategory name
                    <input type="text" name="name" required>
                </label>
                <button type="submit">Add subcategory</button>
            </form>
        <?php endif; ?>
    </section>

    <section class="module-panel">
        <h2>Category listing</h2>
        <?php if (count($categories) === 0): ?>
            <p class="muted">No categories defined yet.</p>
        <?php else: ?>
            <div class="category-list">
                <?php foreach ($categories as $category): ?>
                    <article class="category-card">
                        <header>
                            <strong><?= htmlspecialchars($category['name']) ?></strong>
                            <span class="pill"><?= ucfirst($category['type']) ?></span>
                        </header>
                        <?php if (count($category['subcategories']) === 0): ?>
                            <p class="muted">No subcategories.</p>
                        <?php else: ?>
                            <ul>
                                <?php foreach ($category['subcategories'] as $sub): ?>
                                    <li><?= htmlspecialchars($sub['name']) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>
