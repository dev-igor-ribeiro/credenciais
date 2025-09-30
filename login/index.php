<?php if (isset($_GET['erro']) && $_GET['erro'] == 1): ?>
<div style="text-align: center; color: red; font-weight: bold; margin-top: 1rem;">
    Usuário ou senha incorretos!
</div>
<?php endif; ?>
<?php include('../src/login_form.php'); ?>