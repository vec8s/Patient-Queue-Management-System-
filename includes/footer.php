<?php
/**
 * Shared Footer Template (Arabic)
 * Patient Queue Management System
 */

$inPages = str_contains($_SERVER['PHP_SELF'] ?? '', '/pages/');
$basePath = $inPages ? '../' : '';
$pagesPath = $inPages ? '' : 'pages/';
?>
<footer class="mt-auto border-top py-4 text-center" style="background-color: var(--color-page-cream); border-color: var(--color-linen) !important;">
    <div class="container">
        <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2">
                <span class="brand-dot"></span>
                <span class="fw-bold" style="color: var(--color-charcoal-ink);">نظام إدارة طوابير وحجوزات المرضى</span>
            </div>
            <div class="small" style="color: var(--color-stone);">
                جميع الحقوق محفوظة &copy; <?php echo date('Y'); ?> — بيئة رعاية صحية رقمية مريحة وآمنة
            </div>
            <div class="d-flex gap-3 small">
                <a href="<?php echo $basePath; ?>index.php" style="color: var(--color-graphite);">الرئيسية</a>
                <a href="<?php echo $basePath . $pagesPath; ?>queue.php" style="color: var(--color-graphite);">شاشة الانتظار</a>
                <a href="<?php echo $basePath . $pagesPath; ?>login.php" style="color: var(--color-graphite);">الكادر الطبي</a>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Main Application Script -->
<script src="<?php echo $basePath; ?>js/script.js"></script>
</body>
</html>
