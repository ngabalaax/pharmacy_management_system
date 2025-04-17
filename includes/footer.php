<!-- Authorized System Footer -->
<footer style="
    background: #f8f9fa;
    color: #495057;
    padding: 1rem 0;
    border-top: 1px solid #dee2e6;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 0.9rem;
">
    <div style="
        width: 90%;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    ">
        <div>
            <p style="margin: 0;">&copy; <?php echo date('Y'); ?> Hanan Pharmacy Management System</p>
        </div>
        <div>
            <p style="margin: 0;">
                Logged in as: <strong><?php echo $_SESSION['username'] ?? 'Guest'; ?></strong> |
                <a href="../logout.php" style="
                    color: #e74c3c;
                    text-decoration: none;
                    margin-left: 0.5rem;
                ">Logout</a>
            </p>
        </div>
    </div>
</footer>