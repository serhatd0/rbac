<?php $title = 'Login - PHP JWT RBAC'; require __DIR__ . '/layout/header.php'; ?>

<div class="auth-container">
    <div class="card">
        <h1>Welcome Back</h1>
        <p class="subtitle">Sign in to your account</p>

        <div id="error-msg" class="alert alert-error"></div>

        <form id="loginForm">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" id="email" required placeholder="name@example.com">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" id="password" required placeholder="••••••••">
            </div>
            <button type="submit" id="submitBtn">Sign In</button>
        </form>

        <div class="links">
            Don't have an account? <a href="/register">Sign up</a>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
    document.getElementById('loginForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('submitBtn');
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        btn.disabled = true;
        btn.innerText = 'Signing in...';
        document.getElementById('error-msg').style.display = 'none';

        try {
            const response = await apiCall('/auth/login', 'POST', { email, password });
            const data = await response.json();

            if (response.ok) {
                setTokens(data.accessToken, data.refreshToken);
                window.location.href = '/dashboard';
            } else {
                showError(data.error || 'Login failed');
            }
        } catch (err) {
            showError('Network error occurred');
        } finally {
            btn.disabled = false;
            btn.innerText = 'Sign In';
        }
    });
</script>
<?php $scripts = ob_get_clean(); require __DIR__ . '/layout/footer.php'; ?>
