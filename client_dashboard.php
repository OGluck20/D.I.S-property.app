<?php
include 'includes/header.php';
include 'includes/db.php';
require_once 'includes/track_visitor.php';
// Get user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT firstname, lastname, email, phone, created_at, avatar FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<style>
    :root {
    --primary: #2ecc71;
    --primary-dark: #27ae60;
    --secondary: #34495e;
    --accent: #3498db;
    --background: #f9fafb;
    --text: #2c3e50;
    --shadow: rgba(0, 0, 0, 0.1);
    }

    .dashboard-wrapper {
        display: flex;
        min-height: calc(110vh - 130px);
    }

    .dashboard-main {
        flex: 1;
        padding: 30px;
        background: #f8f9fa;
    }

    .card-img-top{
        height: 200px;
        object-fit: cover;
    }

    .greeting-header {
        background: white;
        padding: 2rem;
        border-radius: 10px;
        margin-bottom: 2rem;
        box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    }

    /* Mobile First Styles */
    .dashboard-wrapper {
        flex-direction: column;
    }

    /* Enhanced Sidebar Styles */
    .dashboard-sidebar {
        width: 280px;
        background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%);
        border-right: 1px solid rgba(0, 0, 0, 0.05);
        padding: 25px 20px;
        box-shadow: 4px 0 25px rgba(0, 0, 0, 0.03);
        /* position: sticky; */
        top: 80px;
        height: calc(100vh - 80px);
        overflow-y: auto;
        transition: all 0.3s ease;
    }

    /* User Profile Section in Sidebar */
    .sidebar-profile {
        text-align: center;
        padding: 20px 0;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        margin-bottom: 20px;
    }

    .user-avatar {
        width: 100px;
        height: 100px;
        border-radius: 20px;
        object-fit: cover;
        border: 3px solid #ffffff;
        box-shadow: 0 4px 15px rgba(46, 204, 113, 0.15);
        padding: 3px;
        margin-bottom: 15px;
        transition: transform 0.3s ease;
    }

    .user-avatar:hover {
        transform: scale(1.05);
    }

    /* Navigation Links */
    .sidebar-nav {
        padding: 10px 0;
    }

    .sidebar-nav .nav-link {
        color: #4a5568;
        padding: 12px 20px;
        border-radius: 12px;
        margin: 8px 0;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        font-weight: 500;
        background: transparent;
        border-left: 4px solid transparent;
    }

    .sidebar-nav .nav-link:hover {
        background: rgba(46, 204, 113, 0.05);
        color: var(--primary);
        transform: translateX(5px);
        border-left-color: var(--primary);
    }

    .sidebar-nav .nav-link.active {
        background: var(--primary);
        color: white;
        border-left-color: var(--primary-dark);
        box-shadow: 0 4px 15px rgba(46, 204, 113, 0.2);
    }

    .sidebar-nav .nav-link i {
        width: 24px;
        font-size: 1.1rem;
        margin-right: 12px;
        transition: all 0.3s ease;
    }

    .sidebar-nav .nav-link:hover i {
        transform: translateX(3px);
    }

    .sidebar-nav .nav-link.active i {
        color: white;
    }

    /* Divider */
    .sidebar-divider {
        height: 1px;
        background: linear-gradient(90deg, 
            rgba(0, 0, 0, 0.03) 0%, 
            rgba(0, 0, 0, 0.06) 50%, 
            rgba(0, 0, 0, 0.03) 100%);
        margin: 15px 0;
    }

    /* Mobile Menu Toggle */
    .menu-toggle {
        background: var(--primary);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(46, 204, 113, 0.2);
        transition: all 0.3s ease;
    }

    .menu-toggle:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
    }

    .close-menu {
        position: absolute;
        right: 20px;
        top: 20px;
        background: white;
        color: var(--primary);
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid var(--primary);
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .close-menu:hover {
        background: var(--primary);
        color: white;
        transform: rotate(90deg);
    }

    /* Responsive Styles */
    @media (max-width: 991px) {
        .dashboard-sidebar {
            position: fixed;
            left: -100%;
            top: 0;
            height: 100vh;
            width: 280px;
            z-index: 1000;
            background: white;
            transition: all 0.3s ease;
        }

        .dashboard-sidebar.active {
            left: 0;
            box-shadow: 0 0 50px rgba(0, 0, 0, 0.1);
        }

        .sidebar-profile {
            margin-top: 40px;
        }
    }

    /* Scrollbar Styling */
    .dashboard-sidebar::-webkit-scrollbar {
        width: 6px;
    }

    .dashboard-sidebar::-webkit-scrollbar-track {
        background: transparent;
    }

    .dashboard-sidebar::-webkit-scrollbar-thumb {
        background: rgba(46, 204, 113, 0.2);
        border-radius: 3px;
    }

    .dashboard-sidebar::-webkit-scrollbar-thumb:hover {
        background: rgba(46, 204, 113, 0.4);
    }

    /* Media Queries */
    @media (max-width: 991px) {  /* Changed to 991px breakpoint */
        
        .dashboard-sidebar.active {
            left: 0;
        }
        
        #applicationModal .modal-dialog {
            margin-top: 160px !important;
        }
    }

    @media (min-width: 769px) {
        .dashboard-wrapper {
            flex-direction: row;
        }
    }

    /* Add this to your existing styles */
    .message-card .card {
        border-left: 4px solid var(--primary);
        transition: transform 0.2s ease;
    }

    .message-card .card:hover {
        transform: translateX(5px);
    }

    .content-section {
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    /* Applications Section Styles */
    .applications-wrapper {
        padding: 1rem;
        max-width: 1200px;
        margin: 0 auto;
    }

    .applications-header-card {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        margin-bottom: 2rem;
    }

    .application-stats {
        display: flex;
        gap: 1.5rem;
    }

    .stats-card {
        text-align: center;
        padding: 0.5rem 1rem;
        background: var(--background);
        border-radius: 10px;
        min-width: 100px;
    }

    .stats-number {
        display: block;
        font-size: 1.5rem;
        font-weight: bold;
        line-height: 1;
    }

    .stats-label {
        font-size: 0.875rem;
        color: var(--text);
    }

    .applications-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .application-table {
        margin-bottom: 0;
    }

    .application-table th {
        background: var(--background);
        color: var(--text);
        font-weight: 600;
        padding: 1rem;
    }

    .application-table td {
        padding: 1rem;
        vertical-align: middle;
    }

    .reference-number {
        font-family: monospace;
        font-weight: 600;
    }

    .service-type {
        color: var(--primary);
    }

    .status-badge {
        padding: 0.5rem 1rem;
        font-weight: 500;
    }

    .property-title {
        display: block;
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .amount {
        font-weight: 600;
        color: var(--text);
    }

    .view-details {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }

    @media (max-width: 768px) {
        .application-stats {
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .stats-card {
            width: 100%;
        }
        
        .application-table {
            font-size: 0.875rem;
        }
    }

    /* Messages Section Enhanced Styling */
    .messages-container {
        max-width: 100%;
        margin: 0 auto;
        padding: 20px;
        background: #f8f9fa;
        min-height: calc(100vh - 200px);
    }

    .messages-wrapper {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        margin-bottom: 30px;
    }

    .messages-header {
        background: linear-gradient(135deg, #2ecc71, #27ae60);
        padding: 2rem;
        position: relative;
        margin-bottom: 20px;
    }

    .messages-header h3 {
        color: white;
        margin: 0;
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .messages-header::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 0;
        right: 0;
        height: 20px;
        background: white;
        border-radius: 50% 50% 0 0;
    }

    .message-card {
        padding: 0.75rem 1.25rem;
        transition: all 0.3s ease;
    }

    .message-card .card {
        border: none;
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        border-left: 4px solid #2ecc71;
    }

    .message-card .card:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .message-card .sender-avatar {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        background: linear-gradient(135deg, #2ecc71, #27ae60);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.2rem;
        margin-right: 15px;
        box-shadow: 0 4px 15px rgba(46, 204, 113, 0.2);
    }

    .message-card .card-subtitle {
        font-size: 1rem;
        color: #2c3e50;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .message-card .text-muted {
        font-size: 0.85rem;
        color: #6b7280 !important;
    }

    .message-card .application-info {
        background: rgba(46, 204, 113, 0.08);
        padding: 8px 15px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .message-card .application-info:hover {
        background: rgba(46, 204, 113, 0.12);
        transform: translateX(5px);
    }

    .message-card .card-text {
        color: #4b5563;
        line-height: 1.6;
        margin-top: 15px;
        font-size: 0.95rem;
        padding: 0 15px 15px 60px;
    }

    .spinner-container {
        min-height: 300px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: white;
        border-radius: 16px;
        margin-top: 20px;
    }

    .spinner-border {
        width: 3.5rem;
        height: 3.5rem;
        border-width: 0.25rem;
        color: #2ecc71;
    }

    .alert {
        border: none;
        border-radius: 12px;
        padding: 1.5rem;
        margin: 1rem;
        display: flex;
        align-items: center;
        gap: 15px;
        background: white;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .alert-danger {
        border-left: 4px solid #ef4444;
    }

    .alert-info {
        border-left: 4px solid #3b82f6;
    }

    /* Responsive Styles */
    @media (max-width: 768px) {
        .messages-container {
            padding: 10px;
        }

        .message-card {
            padding: 0.5rem;
        }

        .message-card .card-body {
            padding: 1rem;
        }

        .message-card .d-flex {
            flex-direction: column;
        }

        .message-card .application-info {
            margin-top: 1rem;
            width: 100%;
        }

        .message-card .card-text {
            padding: 0.75rem 0 0 0;
        }

        .messages-header h3 {
            font-size: 1.2rem;
        }

        .message-card .sender-avatar {
            width: 35px;
            height: 35px;
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }
    }

    @media (max-width: 576px) {
        .messages-container {
            padding: 5px;
        }

        .message-card .card-subtitle {
            font-size: 0.9rem;
        }

        .message-card .text-muted {
            font-size: 0.8rem;
        }

        .message-card .application-info {
            font-size: 0.8rem;
            padding: 6px 10px;
        }

        .message-card .card-text {
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .spinner-container {
            min-height: 200px;
        }

        .spinner-border {
            width: 2.5rem;
            height: 2.5rem;
        }
    }

    /* Animation */
    .content-section {
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Add inside your existing style tag */
    .purchases-wrapper {
        padding: 20px;
    }

    .purchases-header {
        background: linear-gradient(135deg, var(--primary) 0%, #34d399 100%);
        padding: 2rem;
        border-radius: 15px;
        margin-bottom: 2rem;
        color: white;
    }

    .purchase-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }

    .purchase-card:hover {
        transform: translateY(-5px);
    }

    .purchase-image-wrapper {
        height: 200px;
        overflow: hidden;
    }

    .purchase-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .purchase-details {
        padding: 1.5rem;
    }

    .purchase-info {
        display: flex;
        gap: 1rem;
        margin: 1rem 0;
        color: #666;
        font-size: 0.9rem;
    }

    .purchase-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 1rem;
    }

    .purchase-meta .price {
        color: var(--primary);
        font-weight: bold;
    }

    .purchase-meta .date {
        color: #666;
        font-size: 0.9rem;
    }
    .info-item {
        display: flex;
        align-items: center;
        gap: 5px;
        color: #6b7280;
        font-size: 0.9rem;
    }

    .property-price {
        font-size: 1.25rem;
        color: #2ecc71;
        font-weight: 600;
        margin-bottom: 15px;
    }

    .property-location {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #6b7280;
        font-size: 0.9rem;
        margin-bottom: 15px;
    }

    .property-actions {
        display: flex;
        gap: 10px;
    }

    .property-image-wrapper {
    position: relative;
    width: 100%;
    height: 200px;
    background: #f8f9fa;
    overflow: hidden;
    border-top-left-radius: 16px;
    border-top-right-radius: 16px;
    }

    .property-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .property-image.error {
        object-fit: contain;
        padding: 20px;
    }

    /* Loading state */
    .property-image-wrapper::before {
        content: 'Loading...';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: #6c757d;
        font-size: 0.9rem;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .property-image-wrapper.loading::before {
        opacity: 1;
    }

    .btn-view-details {
        background: #2ecc71;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .btn-view-details:hover {
        background: #27ae60;
        transform: translateY(-2px);
    }

    .no-properties {
        background: white;
        padding: 2rem;
        border-radius: 16px;
        text-align: center;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
    }

    .no-properties i {
        font-size: 3rem;
        color: #2ecc71;
        margin-bottom: 1rem;
    }

    /* Responsive Styles */
    @media (max-width: 768px) {
        .purchases-wrapper {
            padding: 10px;
        }

        .purchases-header {
            padding: 1.5rem;
        }

        .purchases-header h3 {
            font-size: 1.2rem;
        }

        .property-card {
            margin-bottom: 20px;
        }

        .property-image {
            height: 180px;
        }

        .property-details {
            padding: 1rem;
        }

        .property-title {
            font-size: 1.1rem;
        }

        .property-info {
            flex-wrap: wrap;
            gap: 10px;
        }
    }

    /* Animation */
    .property-card {
        animation: fadeInUp 0.5s ease;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Add to your existing CSS */
    .stats-card-home {
        background: linear-gradient(145deg, #ffffff, #f8f9fa);
        border-radius: 24px;
        padding: 2rem;
        box-shadow: 20px 20px 60px #d9d9d9,
                   -20px -20px 60px #ffffff;
        display: flex;
        align-items: center;
        gap: 1.5rem;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(255, 255, 255, 0.8);
        position: relative;
        overflow: hidden;
    }

    .stats-card-home::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(
            135deg,
            rgba(46, 204, 113, 0.05) 0%,
            rgba(52, 152, 219, 0.05) 100%
        );
        opacity: 0;
        transition: opacity 0.4s ease;
    }

    .stats-card-home:hover {
        transform: translateY(-8px);
        box-shadow: 25px 25px 75px #d9d9d9,
                   -25px -25px 75px #ffffff;
    }

    .stats-card-home:hover::before {
        opacity: 1;
    }

    .stats-icon {
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, var(--primary), #34d399);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 20px rgba(46, 204, 113, 0.25);
        transition: all 0.4s ease;
        position: relative;
        z-index: 1;
    }

    .stats-icon i {
        font-size: 1.8rem;
        color: white;
        transition: all 0.4s ease;
    }

    .stats-card-home:hover .stats-icon {
        transform: scale(1.1) rotate(5deg);
    }

    .stats-card-home:hover .stats-icon i {
        transform: scale(1.1);
    }

    .stats-info {
        flex: 1;
        position: relative;
        z-index: 1;
    }

    .stats-info h3 {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--text);
        margin: 0;
        line-height: 1.2;
        background: linear-gradient(135deg, var(--primary), #34d399);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        transition: all 0.4s ease;
    }

    .stats-info p {
        margin: 0.5rem 0 0;
        color: #64748b;
        font-size: 1rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.4s ease;
    }

    /* Add responsive styles */
    @media (max-width: 768px) {
        .stats-card-home {
            padding: 1.5rem;
            gap: 1rem;
        }

        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
        }

        .stats-icon i {
            font-size: 1.5rem;
        }

        .stats-info h3 {
            font-size: 2rem;
        }

        .stats-info p {
            font-size: 0.875rem;
        }
    }

    /* Add to your existing CSS */
    .property-image-wrapper {
        position: relative;
        width: 100%;
        height: 200px;
        background: #f8f9fa;
        overflow: hidden;
        border-top-left-radius: 16px;
        border-top-right-radius: 16px;
    }

    .property-image-wrapper.loading::before {
        content: 'Loading...';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: #6c757d;
        font-size: 0.9rem;
        z-index: 1;
    }

    .property-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: all 0.3s ease;
        position: relative;
        z-index: 2;
    }

    .property-image.error {
        object-fit: contain;
        padding: 20px;
        opacity: 0.7;
    }

    .property-image.error::after {
        content: 'Image not available';
        position: absolute;
        bottom: 10px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.8rem;
    }

    /* Add to your existing styles */
    .message-actions {
        display: flex;
        gap: 0.5rem;
    }

    .message-card .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    .message-card .read-btn,
    .message-card .waybill-btn {
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .message-card:hover .read-btn,
    .message-card:hover .waybill-btn {
        opacity: 1;
    }

    .message-status {
        position: absolute;
        top: 1rem;
        right: 1rem;
    }

    .message-card.unread .message-status i {
        color: var(--primary);
    }

    .sender-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--primary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }

    /* Add to your existing styles in client_dashboard.php */
    .message-card {
        margin-bottom: 1rem;
    }

    .message-card.unread {
    background-color: rgba(46, 204, 113, 0.05);
    border-left: 4px solid #2ecc71;
    }

.unread-count {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    border-radius: 0.5rem;
    background-color: #2ecc71;
    color: white;
}

    .message-card.unread .card {
        background: rgba(46, 204, 113, 0.05);
        border-left: 4px solid var(--primary);
    }

    .message-meta {
        display: flex;
        align-items: center;
    }

    .unread-indicator i {
        font-size: 0.5rem;
    }

    .message-actions {
        display: flex;
        gap: 0.5rem;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .message-card:hover .message-actions {
        opacity: 1;
    }

    .read-btn, .waybill-btn {
        padding: 0.25rem 0.75rem;
        font-size: 0.875rem;
    }

    .message-card .card-body {
        padding: 1.5rem;
    }

    .message-card .card-text {
        color: #4a5568;
        margin-bottom: 0;
        line-height: 1.5;
    }

    @media (max-width: 768px) {
        .message-actions {
            opacity: 1;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .read-btn, .waybill-btn {
            width: 100%;
        }
    }
</style>
<style>
    /* Profile Section Styles */
    .profile-wrapper {
        padding: 1rem;
        max-width: 1200px;
        margin: 0 auto;
    }

    .profile-header-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    }

    .profile-cover {
        height: 150px;
        background: linear-gradient(135deg, var(--primary), var(--accent));
    }

    .profile-info {
        padding: 1.5rem;
        position: relative;
        margin-top: -50px;
    }

    .profile-avatar {
        width: 100px;
        height: 100px;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: var(--primary);
        border: 4px solid white;
        box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        margin-bottom: 1rem;
    }

    .profile-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        height: 100%;
    }

    .profile-card .card-header {
        background: var(--background);
        padding: 1rem 1.5rem;
        border-bottom: 1px solid rgba(0,0,0,0.1);
    }

    .profile-card .card-header h3 {
        margin: 0;
        font-size: 1.2rem;
        color: var(--primary);
    }

    .profile-card .card-body {
        padding: 1.5rem;
    }

    .input-group-text {
        background: var(--background);
        border: 1px solid #eee;
        color: var(--primary);
    }

    .form-control {
        border: 1px solid #eee;
        padding: 0.75rem;
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(46, 204, 113, 0.25);
    }

    .btn-primary {
        background: var(--primary);
        border: none;
        padding: 0.75rem 1.5rem;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
    }

    @media (max-width: 768px) {
        .profile-info {
            text-align: center;
        }
        
        .profile-avatar {
            margin: -50px auto 1rem;
        }
        
        .profile-card {
            margin-bottom: 1rem;
        }
    }

    .spinner-border{
        margin-top: 30vh;
    }

    /* Property Card Styles */
.property-card {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    overflow: hidden;
    margin-bottom: 25px;
}

.property-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
}

.property-image-wrapper {
    position: relative;
    padding-top: 66.67%; /* 3:2 Aspect Ratio */
    overflow: hidden;
}

.property-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.property-card:hover .property-image {
    transform: scale(1.05);
}

.property-details {
    padding: 1.5rem;
    background: linear-gradient(to bottom, #fff, #f8f9fa);
}

.property-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 1rem;
    line-height: 1.4;
}

.property-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.price {
    font-size: 1.3rem;
    font-weight: 700;
    color: #2ecc71;
}

.property-info span {
    display: flex;
    align-items: center;
    color: #6c757d;
    font-size: 0.95rem;
}

.property-meta {
    margin-bottom: 1rem;
}

.purchase-date {
    font-size: 0.9rem;
    color: #8395a7;
    display: flex;
    align-items: center;
}

.purchase-date::before {
    content: '\f017';
    font-family: 'Font Awesome 5 Free';
    margin-right: 0.5rem;
    color: #a4b0be;
}

.property-actions {
    margin-top: 1.5rem;
}

.apply-service-btn {
    background: linear-gradient(135deg, #4CAF50, #45a049);
    border: none;
    padding: 0.8rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
}

.apply-service-btn:hover {
    background: linear-gradient(135deg, #45a049, #4CAF50);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(76, 175, 80, 0.2);
}

.apply-service-btn i {
    transition: transform 0.3s ease;
}

.apply-service-btn:hover i {
    transform: translateX(3px);
}

/* Responsive Grid Adjustments */
@media (max-width: 992px) {
    .col-lg-4 {
        padding: 0 10px;
    }
    
    .property-details {
        padding: 1.25rem;
    }
}

@media (max-width: 768px) {
    .property-card {
        margin-bottom: 20px;
    }
    
    .property-title {
        font-size: 1.1rem;
    }
    
    .price {
        font-size: 1.1rem;
    }
}

/* Animation Classes */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.row.g-4 > [class*='col-'] {
    animation: fadeInUp 0.6s ease forwards;
    opacity: 0;
}

.row.g-4 > [class*='col-']:nth-child(2) {
    animation-delay: 0.2s;
}

.row.g-4 > [class*='col-']:nth-child(3) {
    animation-delay: 0.4s;
}
</style>

<!-- Update CSS link -->
<link href="/css/styles.css" rel="stylesheet">
<link href="/css/client_dashboard.css" rel="stylesheet">
<!-- Update JS references -->

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<button class="menu-toggle d-lg-none fixed-top" style="left: 60%; top: 100px; z-index: 2000;">
    <i class="fas fa-bars me-2"></i> Menu
</button>

<div class="dashboard-wrapper">
    <!-- Sidebar -->
    <div class="dashboard-sidebar">
        <button class="close-menu d-lg-none">
            <i class="fas fa-times"></i>
        </button>
        <div class="text-center mb-4">
            <?php
            $avatar_file = !empty($user['avatar']) ? $user['avatar'] : 'default-avatar.jpg';
            $avatar_path = "uploads/avatars/{$avatar_file}";
            ?>
            <img src="<?php echo $avatar_path; ?>" 
                 class="user-avatar" 
                 alt="<?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?>"
                 onerror="this.src='uploads/avatars/default-avatar.jpg'">
            <h4 class="mt-2"><?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></h4>
        </div>
        
        <!-- Update the sidebar nav section -->
        <nav class="sidebar-nav">
            <a href="#" class="nav-link" data-content="home-content">
                <i class="fas fa-home me-2"></i> Home
            </a>
            <a href="#" class="nav-link" data-content="purchases-content">
                <i class="fas fa-shopping-bag me-2"></i> Purchases
            </a>
            <a href="#" class="nav-link" data-content="applications-content">
                <i class="fas fa-file-alt me-2"></i> Applications
            </a>
            <div class="sidebar-divider"></div>
            <a href="#" class="nav-link" data-content="profile-content">
                <i class="fas fa-user me-2"></i> Profile
            </a>
            <a href="index.php" class="nav-link">
                <i class="fas fa-arrow-left me-2"></i> Return Home
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="dashboard-main">

        <!-- Home content section -->
        <div id="home-content" class="content-section">
            <div class="greeting-header">
                <h1>Welcome back, <?php echo htmlspecialchars($user['firstname']); ?>!</h1>
                <p class="lead text-muted">Member since <?php 
                    echo date('F Y', strtotime($user['created_at']));
                ?></p>
            </div>

            <div class="row g-4">
                <!-- Quick Stats Cards -->
                <div class="col-md-6">
                    <div class="stats-card-home">
                        <div class="stats-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <div class="stats-info">
                            <h3>
                                <?php
                                $stmt = $conn->prepare("SELECT COUNT(*) FROM properties WHERE purchaser_id = ? AND status = 'sold'");
                                $stmt->execute([$_SESSION['user_id']]);
                                echo $stmt->fetchColumn();
                                ?>
                            </h3>
                            <p>Properties Owned</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="stats-card-home">
                        <div class="stats-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="stats-info">
                            <h3>
                                <?php
                                $stmt = $conn->prepare("SELECT COUNT(*) FROM service_applications WHERE user_id = ?");
                                $stmt->execute([$_SESSION['user_id']]);
                                echo $stmt->fetchColumn();
                                ?>
                            </h3>
                            <p>Active Applications</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add this inside your dashboard-main div -->
        <div id="purchases-content" class="content-section" style="display: none;">
            <div class="purchases-wrapper">
                <div class="purchases-header">
                    <h3><i class="fas fa-shopping-cart me-2"></i>My Purchases</h3>
                    <p>View all your property purchases and investments</p>
                </div>
                <div class="purchases-container">
                    <!-- Purchases will be loaded here dynamically -->
                </div>
            </div>
        </div>

        <!-- Profile content section -->
        <div id="profile-content" class="content-section" style="display: none;">
            <div class="profile-wrapper">
                <div class="row">
                    <!-- Profile Header -->
                    <div class="col-12 mb-4">
                        <div class="profile-header-card">
                            <div class="profile-cover"></div>
                            <div class="profile-info">
                                <div class="profile-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="profile-details">
                                    <h2><?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></h2>
                                    <p class="text-muted">Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Content -->
                    <div class="col-md-6 mb-4">
                        <div class="profile-card">
                            <div class="card-header">
                                <h3><i class="fas fa-user-edit me-2"></i>Account Information</h3>
                            </div>
                            <div class="card-body">
                                <form id="updateProfileForm">
                                    <div class="form-group mb-3">
                                        <label for="firstname">First Name</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                            <input type="text" class="form-control" id="firstname" name="firstname" 
                                                value="<?php echo htmlspecialchars($user['firstname']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="lastname">Last Name</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                            <input type="text" class="form-control" id="lastname" name="lastname" 
                                                value="<?php echo htmlspecialchars($user['lastname']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="phone">Phone Number</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                            <input type="tel" class="form-control" id="phone" name="phone" 
                                                pattern="[0-9]{11}" title="Please enter 11 digits phone number"
                                                value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="email">Email Address</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                            <input type="email" class="form-control" id="email" name="email" 
                                                value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-save me-2"></i>Save Changes
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="profile-card">
                            <div class="card-header">
                                <h3><i class="fas fa-lock me-2"></i>Security</h3>
                            </div>
                            <div class="card-body">
                                <form id="changePasswordForm">
                                    <div class="form-group mb-3">
                                        <label for="current_password">Current Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                                            <input type="password" class="form-control" id="current_password" 
                                                name="current_password" required>
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="new_password">New Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                            <input type="password" class="form-control" id="new_password" 
                                                name="new_password" required>
                                        </div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="confirm_password">Confirm New Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                            <input type="password" class="form-control" id="confirm_password" 
                                                name="confirm_password" required>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-key me-2"></i>Change Password
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Applications content section -->
        <div id="applications-content" class="content-section" style="display: none;">
            <div class="applications-wrapper">
                <div class="row">
                    <!-- Applications Header -->
                    <div class="col-12 mb-4">
                        <div class="applications-header-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3><i class="fas fa-file-alt me-2"></i>Your Applications</h3>
                                    <p class="text-muted mb-0">Track and manage your service applications</p>
                                </div>
                                <div class="application-stats">
                                    <?php
                                    // Get application statistics
                                    $statsStmt = $conn->prepare("
                                        SELECT 
                                            COUNT(*) as total,
                                            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                                            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
                                        FROM service_applications 
                                        WHERE user_id = ?
                                    ");
                                    $statsStmt->execute([$_SESSION['user_id']]);
                                    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
                                    ?>
                                    <div class="stats-card">
                                        <span class="stats-number"><?php echo $stats['total']; ?></span>
                                        <span class="stats-label">Total</span>
                                    </div>
                                    <div class="stats-card text-success">
                                        <span class="stats-number"><?php echo $stats['completed']; ?></span>
                                        <span class="stats-label">Completed</span>
                                    </div>
                                    <div class="stats-card text-warning">
                                        <span class="stats-number"><?php echo $stats['pending']; ?></span>
                                        <span class="stats-label">Pending</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Applications Table -->
                    <div class="col-12">
                        <div class="applications-card">
                            <div class="table-responsive">
                                <table class="table table-hover application-table">
                                    <thead>
                                        <tr>
                                            <th>Reference</th>
                                            <th>Service Type</th>
                                            <th>Property</th>
                                            <th>Amount</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $stmt = $conn->prepare("
                                            SELECT sa.*, p.title as property_title 
                                            FROM service_applications sa
                                            LEFT JOIN properties p ON sa.property_id = p.id
                                            WHERE sa.user_id = ? 
                                            ORDER BY sa.created_at DESC
                                        ");
                                        $stmt->execute([$_SESSION['user_id']]);
                                        $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                        foreach ($applications as $app):
                                            $statusClass = $app['status'] == 'completed' ? 'success' : 
                                                        ($app['status'] == 'pending' ? 'warning' : 'danger');
                                        ?>
                                        <tr>
                                            <td>
                                                <span class="reference-number">
                                                    <?php echo htmlspecialchars($app['reference']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="service-type">
                                                    <i class="fas fa-file-signature me-1"></i>
                                                    <?php echo htmlspecialchars($app['service_type']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="property-title">
                                                    <?php echo htmlspecialchars($app['property_title'] ?? 'N/A'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="amount">
                                                    ₦<?php echo number_format($app['amount'], 2); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="date">
                                                    <?php echo date('M d, Y', strtotime($app['created_at'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $statusClass; ?> status-badge">
                                                    <?php echo ucfirst($app['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Updated Application Modal -->
<div class="modal fade" id="applicationModal" tabindex="-1" aria-labelledby="applicationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="applicationModalLabel">Apply for Service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" 
                        onclick="window.location.href='#purchases-content'; window.location.reload();"></button>
            </div>
            <form id="applicationForm">
                <div class="modal-body">
                    <input type="hidden" id="propertyId" name="property_id">
                    <input type="hidden" id="userEmail" value="<?php echo htmlspecialchars($user['email']); ?>">
                    
                    <!-- Recipient Information -->
                    <div class="mb-3">
                        <label class="form-label">Recipient's Name</label>
                        <input type="text" class="form-control" id="recipientName" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Recipient's Phone Number</label>
                        <input type="tel" class="form-control" id="recipientPhone" 
                               pattern="[0-9]{11}" 
                               title="Please enter a valid phone number" required>
                        <small class="text-muted">Format: 08012345678</small>
                    </div>

                    <!-- Service Selection -->
                    <div class="mb-3">
                        <label class="form-label">Select Service</label>
                        <select class="form-select" id="serviceType" required>
                            <option value="">Choose a service...</option>
                            <option value="C of O">Certificate of Occupancy (C of O)</option>
                            <option value="Survey">Land Survey</option>
                            <option value="BOQ">Bill of Quantities (BOQ)</option>
                        </select>
                    </div>

                    <!-- Display Area -->
                    <div class="calculation-results" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">Plot Size</label>
                            <input type="text" class="form-control" id="plotSizeDisplay" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Service Fee</label>
                            <input type="text" class="form-control" id="serviceFee" readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" 
                            onclick="window.location.href='#purchases-content'; window.location.reload();">Cancel</button>
                    <button type="button" class="btn btn-primary" id="proceedToPay" disabled>Proceed to Pay</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add this before closing </body> tag -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://js.paystack.co/v1/inline.js"></script>
<script src="https://js.paystack.co/v1/inline.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/js/main.js"></script>
<script src="assets/js/clients_dashboard.js"></script>
<?php include 'includes/whatsapp_float.php'; ?>
<?php include 'includes/footer.php'; ?>