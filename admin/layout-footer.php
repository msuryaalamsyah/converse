
    </div><!-- end admin-content -->
</main>

<script>
// Confirm delete
function confirmDelete(url, name) {
    if (confirm('Delete "' + name + '"?\nThis action cannot be undone.')) {
        window.location = url;
    }
}
</script>
</body>
</html>
