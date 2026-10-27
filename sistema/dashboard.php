<?php
require __DIR__ . '/config.php'; require_login();
page_header('Painel principal');
echo '<p>Bem-vinda, <strong>'.h($_SESSION['user_name'] ?? 'usuário').'</strong>.</p><section class="cards"><a class="card" href="clientes.php"><h2>Clientes</h2><p>Cadastrar, consultar e pesquisar clientes.</p></a><a class="card" href="equipamentos.php"><h2>Equipamentos</h2><p>Vincular equipamentos a clientes.</p></a><a class="card" href="ordens.php"><h2>Ordens de serviço</h2><p>Acompanhar reparos e status.</p></a></section>';
page_footer();