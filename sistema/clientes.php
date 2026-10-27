<?php
require __DIR__ . '/config.php'; require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check(); $nome=trim($_POST['nome']??''); $telefone=trim($_POST['telefone']??''); $email=trim($_POST['email']??''); $cpf=preg_replace('/\D+/', '', $_POST['cpf']??'');
    if ($nome === '') flash('Informe o nome do cliente.');
    elseif ($cpf !== '' && strlen($cpf)!==11) flash('CPF deve conter 11 dígitos.');
    else {
        try {
            $stmt=db()->prepare('INSERT INTO clientes (nome, cpf_criptografado, cpf_hash, telefone, email) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$nome, $cpf!==''?cpf_encrypt($cpf):null, $cpf!==''?cpf_hash($cpf):null, $telefone, $email]);
            flash('Cliente cadastrado com sucesso.');
        } catch (PDOException $e) { flash($e->getCode()==='23000'?'CPF já cadastrado.':'Não foi possível cadastrar o cliente.'); }
    }
    header('Location: clientes.php'); exit;
}
$q=trim($_GET['q']??'');
$sql='SELECT id,nome,telefone,email,cpf_criptografado FROM clientes'; $params=[];
if ($q!=='') { $sql.=' WHERE nome LIKE ? OR telefone LIKE ? OR email LIKE ? OR cpf_hash = ?'; $params=['%'.$q.'%','%'.$q.'%','%'.$q.'%',cpf_hash($q)]; }
$sql.=' ORDER BY nome';
$stmt=db()->prepare($sql); $stmt->execute($params); $rows=$stmt->fetchAll();
page_header('Gerenciamento de clientes');
echo '<section class="panel"><h2>Novo cliente</h2><form method="post" class="form-grid"><input type="hidden" name="csrf" value="'.h(csrf_token()).'"><label>Nome *<input name="nome" required></label><label>CPF<input name="cpf" inputmode="numeric" placeholder="Somente números"></label><label>Telefone<input name="telefone"></label><label>E-mail<input name="email" type="email"></label><button>Cadastrar cliente</button></form></section>';
echo '<section class="panel"><h2>Buscar clientes</h2><form method="get" class="search"><input name="q" value="'.h($q).'" placeholder="Nome, telefone, e-mail ou CPF exato"><button>Buscar</button><a class="button secondary" href="clientes.php">Limpar</a></form><div class="table-wrap"><table><thead><tr><th>Nome</th><th>CPF</th><th>Telefone</th><th>E-mail</th></tr></thead><tbody>';
foreach($rows as $r) echo '<tr><td>'.h($r['nome']).'</td><td>'.h($r['cpf_criptografado']? 'Protegido no banco':'—').'</td><td>'.h($r['telefone']).'</td><td>'.h($r['email']).'</td></tr>';
if (!$rows) echo '<tr><td colspan="4">Nenhum cliente encontrado.</td></tr>';
echo '</tbody></table></div></section>';
page_footer();