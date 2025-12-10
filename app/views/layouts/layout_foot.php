</main>

    <!-- AUTO FOOTER -->
    <?php include FOOTER_FILE; ?>

    <!-- GLOBAL JS -->
    <script src="<?= SCROLL_PROGRESS_JS ?>"></script>

    <!-- GLOBAL TOAST + DOWNLOAD UTILITIES -->
    <script src="<?= TOAST_JS ?>"></script>

    <!-- PAGE-LEVEL CUSTOM JS -->
    <?php if (!empty($custom_js)): ?>
        <?php foreach ($custom_js as $js): ?>
            <script src="<?= htmlspecialchars($js) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

</body>
</html>
