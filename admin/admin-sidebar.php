<aside class="admin-sidebar" id="adminSidebar">
    <nav class="sidebar-nav">
        <ul>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
                <a href="index.php">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'books.php' ? 'active' : ''; ?>">
                <a href="books.php">
                    <i class="fas fa-book"></i>
                    <span>Manage Books</span>
                </a>
            </li>

            <li>
                <a href="../index.php" target="_blank">
                    <i class="fas fa-globe"></i>
                    <span>View Site</span>
                </a>
            </li>

            <li>
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>
