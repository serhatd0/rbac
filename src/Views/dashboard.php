<?php $title = 'Dashboard - PHP JWT RBAC';
require __DIR__ . '/layout/header.php'; ?>

<div class="auth-container dashboard-container">
    <div class="card">
        <h1>Dashboard</h1>
        <p class="subtitle">Protected User Area</p>

        <div id="loading">Loading user data...</div>
        
        <div id="content" style="display: none;">
            <div class="profile-info">
                <div class="profile-item">
                    <span>User ID</span>
                    <span id="userId" class="text-color">-</span>
                </div>
                <div class="profile-item">
                    <span>Email</span>
                    <span id="userEmail" class="text-color">-</span>
                </div>
                <div class="profile-item">
                    <span>Joined</span>
                    <span id="userDate" class="text-color">-</span>
                </div>
            </div>

            <div class="form-group" style="margin-top: 2rem;">
                <label>Debug: Access Token</label>
                <input type="text" id="tokenDisplay" readonly style="font-size: 0.8rem; color: #aaa;">
            </div>

            <button id="logoutBtn" class="logout-btn">Sign Out</button>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
    // Check auth immediately
    const { accessToken } = getTokens();
    if (!accessToken) {
        window.location.href = '/login';
    }

    async function loadProfile() {
        try {
            const response = await apiCall('/me', 'GET', null, true);
            
            if (!response) {
                return;
            }

            if (response.ok) {
                const user = await response.json();
                document.getElementById('userId').innerText = user.id;
                document.getElementById('userEmail').innerText = user.email;
                document.getElementById('userDate').innerText = user.created_at;
                document.getElementById('tokenDisplay').value = localStorage.getItem('accessToken').substring(0, 40) + '...';
                
                document.getElementById('loading').style.display = 'none';
                document.getElementById('content').style.display = 'block';
            } else {
                alert('Failed to load profile');
            }
        } catch (err) {
            console.error(err);
        }
    }

    document.getElementById('logoutBtn').addEventListener('click', async () => {
            const { refreshToken } = getTokens();
            if (refreshToken) {
                await apiCall('/auth/logout', 'POST', { refreshToken });
            }
            removeTokens();
            window.location.href = '/login';
    });

    loadProfile();
</script>
<?php $scripts = ob_get_clean();
require __DIR__ . '/layout/footer.php'; ?>
