<?php
require __DIR__ . '/_bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['name'] ?? 'Admin'));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        flash('error', 'Email and password are required.');
        header('Location: setup.php');
        exit;
    }

    try {
        $userId = $authService->createAdmin($name, $email, $password);
        flash('success', 'Admin user created with ID ' . $userId . '. You can login now.');
        header('Location: login.php');
        exit;
    } catch (Throwable $e) {
        flash('error', $e->getMessage());
        header('Location: setup.php');
        exit;
    }
}

$flash = pull_flash();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin Setup</title>
  <style>
    body { font-family: Arial, sans-serif; max-width: 500px; margin: 40px auto; }
    input { width: 100%; margin: 8px 0; padding: 8px; }
    .msg { padding: 10px; margin: 10px 0; }
    .success { background: #e6ffed; }
    .error { background: #ffeaea; }
  </style>
</head>
<body>
  <h1>Create Admin User</h1>
  <p>Use once, then delete <code>setup.php</code> for security.</p>

  <?php if ($flash): ?>
    <div class="msg <?php echo h($flash['type']); ?>"><?php echo h($flash['message']); ?></div>
  <?php endif; ?>

  <form method="post">
    <label>Name</label>
    <input name="name" placeholder="Admin" />
    <label>Email</label>
    <input name="email" type="email" required />
    <label>Password</label>
    <input name="password" type="password" required />
    <button type="submit">Create Admin</button>
  </form>
</body>
</html>
