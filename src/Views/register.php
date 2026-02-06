<?php
$title = 'Register - PHP JWT RBAC';
require __DIR__ . '/layout/header.php';
?>

<div class="auth-container">
    <div class="card">
        <h1>Create Account</h1>
        <p class="subtitle">Get started with our starter kit</p>

        <div id="error-msg" class="alert alert-error"></div>
        <div id="success-msg" class="alert alert-success"></div>

        <form id="registerForm">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" id="email" required placeholder="name@example.com">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" id="password" required placeholder="••••••••">
            </div>
            <button type="submit" id="submitBtn">Create Account</button>
        </form>

        <div class="links">
            Already have an account? <a href="/login">Sign in</a>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
    document.getElementById('registerForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('submitBtn');
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;

        btn.disabled = true;
        btn.innerText = 'Creating...';
        document.getElementById('error-msg').style.display = 'none';
        document.getElementById('success-msg').style.display = 'none';

        try {
            const response = await apiCall('/auth/register', 'POST', { email, password });
            
            if (response.ok) {
                showSuccess('Account created! Redirecting to login...');
                setTimeout(() => {
                    window.location.href = '/login';
                }, 1500);
            } else {
                const data = await response.json();
                showError(data.error || 'Registration failed');
            }
        } catch (err) {
            showError('Network error occurred');
        } finally {
            btn.disabled = false;
            btn.innerText = 'Create Account';
        }
    });
</script>
<?php
$scripts = ob_get_clean();
require __DIR__ . '/layout/footer.php';
?>
