<?php
require_once '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}


$success = '';
$error = '';

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Message deleted successfully!";
    } catch(PDOException $e) {
        $error = "Failed to delete message.";
    }
}

// Handle status update
if (isset($_GET['mark']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);
    $status = in_array($_GET['mark'], ['unread', 'read', 'replied']) ? $_GET['mark'] : 'unread';
    try {
        $stmt = $pdo->prepare("UPDATE messages SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        $success = "Status updated to " . ucfirst($status) . "!";
    } catch(PDOException $e) {
        $error = "Failed to update status.";
    }
}

// Handle bulk actions
if (isset($_POST['bulk_action']) && isset($_POST['selected'])) {
    $selected = array_map('intval', $_POST['selected']);
    $action = $_POST['bulk_action'];
    
    if (!empty($selected)) {
        $placeholders = implode(',', array_fill(0, count($selected), '?'));
        
        try {
            if ($action === 'delete') {
                $stmt = $pdo->prepare("DELETE FROM messages WHERE id IN ($placeholders)");
                $stmt->execute($selected);
                $success = count($selected) . " messages deleted!";
            } elseif (in_array($action, ['unread', 'read', 'replied'])) {
                $stmt = $pdo->prepare("UPDATE messages SET status = ? WHERE id IN ($placeholders)");
                array_unshift($selected, $action);
                $stmt->execute($selected);
                $success = count($selected) - 1 . " messages updated!";
            }
        } catch(PDOException $e) {
            $error = "Bulk action failed.";
        }
    }
}

// Search and filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$sql = "SELECT * FROM messages WHERE 1=1";
$params = [];

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR email LIKE ? OR message LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term]);
}

if (!empty($status_filter) && in_array($status_filter, ['unread', 'read', 'replied'])) {
    $sql .= " AND status = ?";
    $params[] = $status_filter;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$messages = $stmt->fetchAll();

// Stats
$total = $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();
$unread = $pdo->query("SELECT COUNT(*) FROM messages WHERE status = 'unread'")->fetchColumn();
$read = $pdo->query("SELECT COUNT(*) FROM messages WHERE status = 'read'")->fetchColumn();
$replied = $pdo->query("SELECT COUNT(*) FROM messages WHERE status = 'replied'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0f0f0f;
            color: #e0e0e0;
            line-height: 1.6;
        }

        /* Layout */
        .container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: #1a1a1a;
            border-right: 1px solid #2a2a2a;
            padding: 2rem 1.5rem;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #2a2a2a;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }

        .logo h2 {
            font-size: 1.3rem;
            color: #fff;
            font-weight: 600;
        }

        .nav-menu {
            list-style: none;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.9rem 1rem;
            border-radius: 8px;
            color: #a0a0a0;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background: #252525;
            color: #fff;
        }

        .nav-link.active {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.2) 0%, rgba(118, 75, 162, 0.2) 100%);
            color: #667eea;
            border: 1px solid rgba(102, 126, 234, 0.3);
        }

        .nav-link i {
            width: 20px;
            text-align: center;
        }

        .badge {
            margin-left: auto;
            background: #e74c3c;
            color: white;
            font-size: 0.75rem;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-weight: 600;
        }

        /* User Dropdown - FIXED VERSION */
.user-section {
    margin-top: auto;
    padding-top: 2rem;
    border-top: 1px solid #2a2a2a;
}

.user-menu {
    position: relative;
}

.user-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 0.8rem;
    background: #252525;
    border: 1px solid #2a2a2a;
    border-radius: 8px;
    color: #fff;
    cursor: pointer;
    transition: all 0.3s ease;
}

.user-btn:hover {
    background: #2a2a2a;
}

.user-avatar {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    font-weight: 600;
}

.user-name {
    flex: 1;
    text-align: left;
    font-size: 0.9rem;
}

.dropdown-icon {
    transition: transform 0.3s ease;
}

.user-menu.active .dropdown-icon {
    transform: rotate(180deg);
}

/* DROPDOWN MENU - FIXED TO DROP DOWNWARD */
.dropdown {
    position: absolute;
    top: 100%; /* Changed from bottom: 100% to top: 100% */
    left: 0;
    right: 0;
    margin-top: 0.5rem; /* Changed from margin-bottom to margin-top */
    background: #252525;
    border: 1px solid #2a2a2a;
    border-radius: 8px;
    overflow: hidden;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px); /* Changed from translateY(10px) to -10px */
    transition: all 0.3s ease;
    z-index: 1001; /* Added z-index to ensure it's above other elements */
}

.user-menu.active .dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0.8rem 1rem;
    color: #a0a0a0;
    text-decoration: none;
    transition: all 0.3s ease;
}

.dropdown a:hover {
    background: #2a2a2a;
    color: #fff;
}

.dropdown a.logout {
    color: #e74c3c;
}

.dropdown a.logout:hover {
    background: rgba(231, 76, 60, 0.1);
}

        /* Main Content */
        .main {
            flex: 1;
            margin-left: 250px;
            padding: 2rem;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .header h1 {
            font-size: 1.8rem;
            color: #fff;
            font-weight: 600;
        }

        .view-site {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0.7rem 1.2rem;
            background: #252525;
            border: 1px solid #2a2a2a;
            border-radius: 8px;
            color: #a0a0a0;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .view-site:hover {
            background: #2a2a2a;
            color: #fff;
        }

        /* Alerts */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-success {
            background: rgba(46, 204, 113, 0.1);
            border: 1px solid rgba(46, 204, 113, 0.3);
            color: #2ecc71;
        }

        .alert-error {
            background: rgba(231, 76, 60, 0.1);
            border: 1px solid rgba(231, 76, 60, 0.3);
            color: #e74c3c;
        }

        /* Stats */
        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 12px;
            padding: 1.5rem;
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .stat-icon.blue { background: rgba(102, 126, 234, 0.2); color: #667eea; }
        .stat-icon.red { background: rgba(231, 76, 60, 0.2); color: #e74c3c; }
        .stat-icon.green { background: rgba(46, 204, 113, 0.2); color: #2ecc71; }
        .stat-icon.purple { background: rgba(155, 89, 182, 0.2); color: #9b59b6; }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 0.3rem;
        }

        .stat-label {
            color: #888;
            font-size: 0.9rem;
        }

        /* Filters */
        .filters {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .search-box {
            position: relative;
            flex: 1;
            min-width: 300px;
        }

        .search-box input {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 2.5rem;
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 8px;
            color: #e0e0e0;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            outline: none;
            border-color: #667eea;
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .filter-select {
            padding: 0.8rem 1rem;
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 8px;
            color: #e0e0e0;
            cursor: pointer;
            min-width: 150px;
        }

        .btn-clear {
            padding: 0.8rem 1.2rem;
            background: #252525;
            border: 1px solid #2a2a2a;
            border-radius: 8px;
            color: #a0a0a0;
            text-decoration: none;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-clear:hover {
            background: #2a2a2a;
            color: #fff;
        }

        /* Table */
        .table-wrap {
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 12px;
            overflow: hidden;
        }

        .bulk-bar {
            padding: 1rem 1.5rem;
            background: #252525;
            border-bottom: 1px solid #2a2a2a;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .bulk-select {
            padding: 0.6rem 1rem;
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 6px;
            color: #e0e0e0;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            padding: 1rem 1.5rem;
            text-align: left;
            font-weight: 600;
            color: #888;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: #252525;
            border-bottom: 1px solid #2a2a2a;
        }

        td {
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid #2a2a2a;
            vertical-align: top;
        }

        tr:hover {
            background: #252525;
        }

        tr.unread {
            background: rgba(102, 126, 234, 0.05);
        }

        tr.unread:hover {
            background: rgba(102, 126, 234, 0.1);
        }

        /* Checkbox */
        .checkbox {
            width: 18px;
            height: 18px;
            border: 2px solid #3a3a3a;
            border-radius: 4px;
            cursor: pointer;
            accent-color: #667eea;
        }

        /* Message Content */
        .sender {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sender-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1rem;
        }

        .sender-info h4 {
            color: #fff;
            font-weight: 600;
            margin-bottom: 0.2rem;
            font-size: 0.95rem;
        }

        .sender-info a {
            color: #667eea;
            text-decoration: none;
            font-size: 0.85rem;
        }

        .sender-info a:hover {
            text-decoration: underline;
        }

        .message-text {
            max-width: 300px;
            color: #888;
            font-size: 0.9rem;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            cursor: pointer;
        }

        .message-text:hover {
            color: #a0a0a0;
        }

        .date {
            color: #888;
            font-size: 0.9rem;
        }

        .date small {
            color: #666;
            display: block;
            margin-top: 0.2rem;
        }

        /* Status */
        .status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
        }

        .status.unread {
            background: rgba(231, 76, 60, 0.15);
            color: #e74c3c;
        }
        .status.unread::before { background: #e74c3c; }

        .status.read {
            background: rgba(46, 204, 113, 0.15);
            color: #2ecc71;
        }
        .status.read::before { background: #2ecc71; }

        .status.replied {
            background: rgba(52, 152, 219, 0.15);
            color: #3498db;
        }
        .status.replied::before { background: #3498db; }

        /* Actions */
        .actions {
            display: flex;
            gap: 0.5rem;
        }

        .action-btn {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            color: #fff;
            font-size: 0.85rem;
            text-decoration: none;
        }

        .action-btn.view { background: #3498db; }
        .action-btn.view:hover { background: #2980b9; }

        .action-btn.mark { background: #2ecc71; }
        .action-btn.mark:hover { background: #27ae60; }

        .action-btn.unmark { background: #f39c12; }
        .action-btn.unmark:hover { background: #e67e22; }

        .action-btn.reply { background: #9b59b6; }
        .action-btn.reply:hover { background: #8e44ad; }

        .action-btn.delete { background: #e74c3c; }
        .action-btn.delete:hover { background: #c0392b; }

        /* Empty */
        .empty {
            text-align: center;
            padding: 4rem 2rem;
            color: #666;
        }

        .empty i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #2a2a2a;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .modal.active {
            display: flex;
        }

        .modal-box {
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 16px;
            width: 100%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-head {
            padding: 1.5rem;
            border-bottom: 1px solid #2a2a2a;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-head h3 {
            color: #fff;
            font-size: 1.2rem;
        }

        .modal-close {
            background: none;
            border: none;
            color: #666;
            font-size: 1.5rem;
            cursor: pointer;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .modal-close:hover {
            background: #252525;
            color: #fff;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-msg {
            color: #a0a0a0;
            line-height: 1.8;
            font-size: 1rem;
            white-space: pre-wrap;
            margin-bottom: 1.5rem;
        }

        .modal-meta {
            padding-top: 1.5rem;
            border-top: 1px solid #2a2a2a;
            color: #888;
            font-size: 0.9rem;
        }

        .modal-meta strong {
            color: #fff;
        }

        .modal-foot {
            padding: 1rem 1.5rem;
            border-top: 1px solid #2a2a2a;
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }

        .btn {
            padding: 0.7rem 1.2rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-secondary {
            background: #252525;
            color: #a0a0a0;
            border: 1px solid #2a2a2a;
        }

        .btn-secondary:hover {
            background: #2a2a2a;
            color: #fff;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .main {
                margin-left: 0;
            }
            .stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h2>Admin</h2>
            </div>

            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link active">
                        <i class="fas fa-envelope"></i>
                        <span>Messages</span>
                        <?php if ($unread > 0): ?>
                            <span class="badge"><?php echo $unread; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>

            <!-- User Dropdown -->
            <div class="user-section">
                <div class="user-menu" id="userMenu">
                    <button class="user-btn" onclick="toggleDropdown()">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($_SESSION['admin_username'] ?? 'A', 0, 1)); ?>
                        </div>
                        <span class="user-name"><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></span>
                        <i class="fas fa-chevron-down dropdown-icon"></i>
                    </button>
                    <div class="dropdown">
                        <a href="logout.php" class="logout">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main -->
        <main class="main">
            <!-- Header -->
            <div class="header">
                <h1>Messages</h1>
                <a href="../index.php" target="_blank" class="view-site">
                    <i class="fas fa-external-link-alt"></i>
                    View Site
                </a>
            </div>

            <!-- Alerts -->
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Stats -->
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value"><?php echo $total; ?></div>
                            <div class="stat-label">Total Messages</div>
                        </div>
                        <div class="stat-icon blue">
                            <i class="fas fa-envelope"></i>
                        </div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value"><?php echo $unread; ?></div>
                            <div class="stat-label">Unread</div>
                        </div>
                        <div class="stat-icon red">
                            <i class="fas fa-bell"></i>
                        </div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value"><?php echo $read; ?></div>
                            <div class="stat-label">Read</div>
                        </div>
                        <div class="stat-icon green">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div>
                            <div class="stat-value"><?php echo $replied; ?></div>
                            <div class="stat-label">Replied</div>
                        </div>
                        <div class="stat-icon purple">
                            <i class="fas fa-reply"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <form method="GET" class="filters">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Search messages..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <select name="status" class="filter-select" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="unread" <?php echo $status_filter == 'unread' ? 'selected' : ''; ?>>Unread</option>
                    <option value="read" <?php echo $status_filter == 'read' ? 'selected' : ''; ?>>Read</option>
                    <option value="replied" <?php echo $status_filter == 'replied' ? 'selected' : ''; ?>>Replied</option>
                </select>
                <?php if ($search || $status_filter): ?>
                    <a href="dashboard.php" class="btn-clear">
                        <i class="fas fa-times"></i> Clear
                    </a>
                <?php endif; ?>
            </form>

            <!-- Table -->
            <form method="POST" id="bulkForm">
                <div class="table-wrap">
                    <div class="bulk-bar">
                        <input type="checkbox" class="checkbox" id="selectAll" onchange="toggleAll(this)">
                        <select name="bulk_action" class="bulk-select" onchange="if(this.value && confirm('Are you sure?')) document.getElementById('bulkForm').submit();">
                            <option value="">Bulk Actions</option>
                            <option value="read">Mark as Read</option>
                            <option value="unread">Mark as Unread</option>
                            <option value="replied">Mark as Replied</option>
                            <option value="delete">Delete Selected</option>
                        </select>
                    </div>
                    
                    <table>
                        <thead>
                            <tr>
                                <th width="40"></th>
                                <th>Sender</th>
                                <th>Message</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th width="150">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($messages)): ?>
                                <tr>
                                    <td colspan="6" class="empty">
                                        <i class="fas fa-inbox"></i>
                                        <h3>No messages found</h3>
                                        <p>Messages from your contact form will appear here</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($messages as $msg): ?>
                                    <tr class="<?php echo $msg['status'] == 'unread' ? 'unread' : ''; ?>">
                                        <td>
                                            <input type="checkbox" name="selected[]" value="<?php echo $msg['id']; ?>" class="checkbox row-checkbox">
                                        </td>
                                        <td>
                                            <div class="sender">
                                                <div class="sender-avatar">
                                                    <?php echo strtoupper(substr($msg['name'], 0, 1)); ?>
                                                </div>
                                                <div class="sender-info">
                                                    <h4><?php echo htmlspecialchars($msg['name']); ?></h4>
                                                    <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>">
                                                        <?php echo htmlspecialchars($msg['email']); ?>
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="message-text" onclick="openModal(<?php echo $msg['id']; ?>)">
                                                <?php echo htmlspecialchars($msg['message']); ?>
                                            </div>
                                            <div id="msg-<?php echo $msg['id']; ?>" style="display:none;">
                                                <div class="modal-msg"><?php echo htmlspecialchars($msg['message']); ?></div>
                                                <div class="modal-meta">
                                                    <strong>From:</strong> <?php echo htmlspecialchars($msg['name']); ?> (<?php echo htmlspecialchars($msg['email']); ?>)<br>
                                                    <strong>Received:</strong> <?php echo date('F d, Y \a\t h:i A', strtotime($msg['created_at'])); ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="date">
                                            <?php echo date('M d, Y', strtotime($msg['created_at'])); ?>
                                            <small><?php echo date('h:i A', strtotime($msg['created_at'])); ?></small>
                                        </td>
                                        <td>
                                            <span class="status <?php echo $msg['status']; ?>">
                                                <?php echo ucfirst($msg['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="actions">
                                                <button type="button" class="action-btn view" onclick="openModal(<?php echo $msg['id']; ?>)" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                
                                                <?php if ($msg['status'] == 'unread'): ?>
                                                    <a href="?mark=read&id=<?php echo $msg['id']; ?>" class="action-btn mark" title="Mark Read">
                                                        <i class="fas fa-check"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="?mark=unread&id=<?php echo $msg['id']; ?>" class="action-btn unmark" title="Mark Unread">
                                                        <i class="fas fa-envelope"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <a href="mailto:<?php echo $msg['email']; ?>?subject=Re: Your Message&body=Hi <?php echo urlencode($msg['name']); ?>,%0D%0A%0D%0A" 
                                                   class="action-btn reply" title="Reply">
                                                    <i class="fas fa-reply"></i>
                                                </a>
                                                
                                                <a href="?delete=<?php echo $msg['id']; ?>" 
                                                   class="action-btn delete" 
                                                   onclick="return confirm('Delete this message?')"
                                                   title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </form>
        </main>
    </div>

    <!-- Modal -->
    <div id="messageModal" class="modal" onclick="if(event.target === this) closeModal()">
        <div class="modal-box">
            <div class="modal-head">
                <h3><i class="fas fa-envelope-open"></i> Message Details</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body" id="modalBody"></div>
            <div class="modal-foot">
                <button class="btn btn-secondary" onclick="closeModal()">Close</button>
                <a id="modalReply" href="#" class="btn btn-primary">
                    <i class="fas fa-reply"></i> Reply
                </a>
            </div>
        </div>
    </div>

    <script>
        // Dropdown toggle
        function toggleDropdown() {
            document.getElementById('userMenu').classList.toggle('active');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const menu = document.getElementById('userMenu');
            if (!menu.contains(e.target)) {
                menu.classList.remove('active');
            }
        });

        // Select all
        function toggleAll(source) {
            document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = source.checked);
        }

        // Modal
        function openModal(id) {
            const content = document.getElementById('msg-' + id).innerHTML;
            const row = document.querySelector(`tr:has(#msg-${id})`);
            const email = row.querySelector('.sender-info a').href.replace('mailto:', '');
            
            document.getElementById('modalBody').innerHTML = content;
            document.getElementById('modalReply').href = `mailto:${email}?subject=Re: Your Message`;
            document.getElementById('messageModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('messageModal').classList.remove('active');
        }

        // Close on Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeModal();
        });
    </script>
</body>
</html>