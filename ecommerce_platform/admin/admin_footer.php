<?php /* admin/admin_footer.php */ ?>
</div><!-- /container -->

<footer class="text-center py-3" style="background:#f8f9fa;">
    <small>&copy; <?= date('Y') ?> MyShop Admin • Powered by PHP</small>
</footer>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.min.js"></script>

<!-- jQuery (DataTables dépendance) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- DataTables JS -->
<script
    src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script
    src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<!-- Init DataTable -->
<script>
    $(document).ready(function() {
        $('#productsTable').DataTable({
            pageLength: 10,
            lengthChange: false,
            language: {
                search: "Rechercher :",
                paginate: {
                    previous: "Précédent",
                    next: "Suivant"
                },
                info: "_START_ à _END_ sur _TOTAL_ produits"
            }
        });
    });
</script>


</body>

</html>