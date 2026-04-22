</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>

<script>
function toggleMenu(){
    document.getElementById('sidebar').classList.toggle('active');
}

$(document).ready(function(){
    const csrfToken = '<?= e(csrf_token()) ?>';

    // Injeta token CSRF automaticamente em todos os formulários POST
    document.querySelectorAll('form[method="POST"], form[method="post"]').forEach((form) => {
        const existing = form.querySelector('input[name="csrf_token"]');
        if (!existing) {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'csrf_token';
            hidden.value = csrfToken;
            form.appendChild(hidden);
        }
    });

    // Envia token CSRF em requisições AJAX via jQuery
    $.ajaxSetup({
        headers: {
            'X-CSRF-Token': csrfToken
        }
    });

    $('.select2').select2({
        width: '100%'
    });
});
</script>

</body>
</html>