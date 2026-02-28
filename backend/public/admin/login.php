<?php
require __DIR__ . '/_bootstrap.php';

if (admin_user()) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $user = $authService->login($email, $password);
    if ($user === null) {
        flash('error', 'Invalid credentials.');
        header('Location: login.php');
        exit;
    }

    $_SESSION['admin_user'] = $user;
    header('Location: index.php');
    exit;
}

$flash = pull_flash();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin Login</title>
  <style>
    body { font-family: Arial, sans-serif; max-width: 420px; margin: 60px auto; }
    input { width: 100%; margin: 8px 0; padding: 8px; }
    .msg { padding: 10px; margin: 10px 0; }
    .error { background: #ffeaea; }
  </style>
</head>
<body>
  <h1>Expense Manager Admin</h1>

  <?php if ($flash): ?>
    <div class="msg <?php echo h($flash['type']); ?>"><?php echo h($flash['message']); ?></div>
  <?php endif; ?>

  <form method="post">
    <label>Email</label>
    <input name="email" type="email" required />
    <label>Password</label>
    <input name="password" type="password" required />
    <button type="submit">Login</button>
  </form>

  <p>If first time, open <a href="setup.php">setup.php</a>.</p>
</body>
</html>
