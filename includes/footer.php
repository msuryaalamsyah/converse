
<footer class="footer">
    <div class="footer-top">
        <div class="footer-brand">
            <div class="footer-logo">CONVERSE</div>
            <p>Rooted in a High-Contrast / Bold aesthetic that balances minimalist structure with aggressive, urban layouts.</p>
        </div>
        <div class="footer-col">
            <h4>GET HELP</h4>
            <ul>
                <li><a href="#">Contact Us</a></li>
                <li><a href="#">Order Status</a></li>
                <li><a href="#">Shipping &amp; Delivery</a></li>
                <li><a href="#">Returns</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>ABOUT</h4>
            <ul>
                <li><a href="#">Our Story</a></li>
                <li><a href="#">Size Guide</a></li>
                <li><a href="#">Sustainability</a></li>
                <li><a href="#">Privacy Policy</a></li>
            </ul>
        </div>
        <div class="footer-col footer-newsletter">
            <h4>STAY IN THE LOOP</h4>
            <p>Sign up for updates on new drops, collabs, and exclusive offers.</p>
            <form class="newsletter-form" onsubmit="return false">
                <input type="email" placeholder="ENTER EMAIL ADDRESS">
                <button type="submit">SIGN UP</button>
            </form>
        </div>
    </div>
    <div class="footer-bottom">
        <span>&copy; <?= date('Y') ?> Converse Inc. All Rights Reserved.</span>
        <a href="<?= SITE_URL ?>/admin/login.php" style="color:#555;font-size:11px">Admin</a>
    </div>
</footer>

<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>
