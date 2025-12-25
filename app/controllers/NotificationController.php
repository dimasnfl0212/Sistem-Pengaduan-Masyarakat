<?php
// File: C:\xampp\htdocs\lapor-system\app\controllers\NotificationController.php

class NotificationController {
    private $notification;
    private $session;
    
    public function __construct() {
        require_once __DIR__ . '/../core/Database.php';
        require_once __DIR__ . '/../core/Session.php';
        require_once __DIR__ . '/../models/Notification.php';
        
        $this->session = new Session();
        
        $database = new Database();
        $db = $database->connect();
        
        $this->notification = new Notification($db);
    }
    
    public function getNotifications($limit = 10, $unreadOnly = false) {
        $userId = $this->session->get('user_id');
        
        if (!$userId) {
            return [];
        }
        
        return $this->notification->getUserNotifications($userId, $limit, $unreadOnly);
    }
    
    public function getUnreadCount() {
        $userId = $this->session->get('user_id');
        
        if (!$userId) {
            return 0;
        }
        
        return $this->notification->countUnread($userId);
    }
    
    public function markAsRead($id) {
        return $this->notification->markAsRead($id);
    }
    
    public function markAllAsRead() {
        $userId = $this->session->get('user_id');
        return $this->notification->markAllAsRead($userId);
    }
    
    public function sendReportStatusNotification($userId, $reportId, $oldStatus, $newStatus) {
        return $this->notification->createStatusChangeNotification($userId, $reportId, $oldStatus, $newStatus);
    }
    
    public function sendNewReportNotification($reportId, $title) {
        // Get all admin users
        require_once __DIR__ . '/../models/User.php';
        $database = new Database();
        $db = $database->connect();
        $userModel = new User($db);
        
        // This method needs to be added to User model
        // $adminIds = $userModel->getAdminIds();
        
        // For now, let's assume we have at least one admin
        $adminIds = [1]; // Default admin ID
        
        return $this->notification->createNewReportNotification($adminIds, $reportId, $title);
    }
    
    public function renderDropdown() {
        $notifications = $this->getNotifications(5, false);
        $unreadCount = $this->getUnreadCount();
        
        ob_start();
        ?>
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle position-relative" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown">
                <i class="bi bi-bell"></i>
                <?php if($unreadCount > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    <?= $unreadCount ?>
                    <span class="visually-hidden">unread notifications</span>
                </span>
                <?php endif; ?>
            </a>
            <div class="dropdown-menu dropdown-menu-end shadow" style="width: 300px;">
                <div class="dropdown-header d-flex justify-content-between align-items-center">
                    <strong>Notifikasi</strong>
                    <?php if($unreadCount > 0): ?>
                    <form method="POST" action="mark-all-read.php" style="display: inline;">
                        <button type="submit" class="btn btn-sm btn-outline-primary">Tandai semua dibaca</button>
                    </form>
                    <?php endif; ?>
                </div>
                <div class="dropdown-divider"></div>
                
                <?php if(empty($notifications)): ?>
                    <div class="text-center py-3">
                        <i class="bi bi-bell-slash display-6 text-muted"></i>
                        <p class="text-muted mb-0">Tidak ada notifikasi</p>
                    </div>
                <?php else: ?>
                    <div style="max-height: 300px; overflow-y: auto;">
                        <?php foreach($notifications as $notif): ?>
                        <a href="#" class="dropdown-item <?= $notif['is_read'] ? '' : 'bg-light' ?>" onclick="markAsRead(<?= $notif['id'] ?>)">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-2">
                                    <?php
                                    $icon = [
                                        'info' => 'bi-info-circle text-info',
                                        'success' => 'bi-check-circle text-success',
                                        'warning' => 'bi-exclamation-triangle text-warning',
                                        'danger' => 'bi-exclamation-circle text-danger'
                                    ][$notif['type']];
                                    ?>
                                    <i class="bi <?= $icon ?>"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <strong><?= htmlspecialchars($notif['title']) ?></strong>
                                    <p class="mb-0 text-muted small"><?= htmlspecialchars(substr($notif['message'], 0, 60)) ?>...</p>
                                    <small class="text-muted"><?= $this->timeAgo($notif['created_at']) ?></small>
                                </div>
                                <?php if(!$notif['is_read']): ?>
                                <div class="flex-shrink-0">
                                    <span class="badge bg-primary">Baru</span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </a>
                        <div class="dropdown-divider"></div>
                        <?php endforeach; ?>
                    </div>
                    <div class="dropdown-footer text-center py-2">
                        <a href="notifications.php" class="text-decoration-none">Lihat semua notifikasi</a>
                    </div>
                <?php endif; ?>
            </div>
        </li>
        
        <script>
        function markAsRead(id) {
            fetch('mark-read.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        location.reload();
                    }
                });
        }
        </script>
        <?php
        return ob_get_clean();
    }
    
    private function timeAgo($datetime) {
        $time = strtotime($datetime);
        $now = time();
        $diff = $now - $time;
        
        if ($diff < 60) {
            return 'Baru saja';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . ' menit yang lalu';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . ' jam yang lalu';
        } elseif ($diff < 604800) {
            return floor($diff / 86400) . ' hari yang lalu';
        } else {
            return date('d/m/Y', $time);
        }
    }
}
?>