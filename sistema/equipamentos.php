<?php
require __DIR__ . '/config.php'; require_login();
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_check(); $cliente=(int)($_POST['cliente_id']??0); $tipo=trim($_POST['tipo']??''); $marca=trim($_POST['marca']??''); $modelo=trim($_POST['modelo']??''); $serie=trim($_POST['numero_serie']??''); $descricao=trim($_POST['descricao']??'');
    if (!$cliente || $tipo==='') flash('Selecione um cliente e informe o tipo de equipamento.');
    else { try { $s=db()->prepare('INSERT INTO equipamentos (cliente_id,tipo,marca,modelo,numero_serie,descricao) VALUES (?,?,?,?,?,?)'); $s->execute([$cliente,$tipo,$marca,$modelo,$serie,$descricao]); flash('Equipamento cadastrado.'); } catch(PDOException $e) { flash($e->getCode()==='23000'?'Número de série já cadastrado.':'Erro ao cadastrar equipamento.'); } }
    header('Location: equipamentos.php'); exit;
}
$clientes=db()->query('SELECT id,nome FROM clientes ORDER BY nome')->fetchAll();
$rows=db()->query('SELECT e.*,c.nome cliente FROM equipamentos e JOIN clientes c ON c.id=e.cliente_id ORDER BY e.id DESC')->fetchAll();
page_header('Gerenciamento de equipamentos');
echo '<section class="panel"><h2>Novo equipamento</h2><form method="post" class="form-grid"><input type="hidden" name="csrf" value="'.h(csrf_token()).'"><label>Cliente *<select name="cliente_id" required><option value="">Selecione...</option>';
foreach($clientes as $c) echo '<option value="'.(int)$c['id'].'">'.h($c['nome']).'</option>';
echo '</select></label><label>Tipo *<input name="tipo" required placeholder="Notebook, desktop..."></label><label>Marca<input name="marca"></label><label>Modelo<input name="modelo"></label><label>Número de série<input name="numero_serie"></label><label>Descrição<input name="descricao"></label><button>Cadastrar equipamento</button></form></section><section class="panel"><h2>Equipamentos cadastrados</h2><div class="table-wrap"><table><thead><tr><th>Cliente</th><th>Tipo</th><th>Marca / modelo</th><th>Série</th><th>Descrição</th></tr></thead><tbody>';
foreach($rows as $r) echo '<tr><td>'.h($r['cliente']).'</td><td>'.h($r['tipo']).'</td><td>'.h(trim($r['marca'].' '.$r['modelo'])).'</td><td>'.h($r['numero_serie']).'</td><td>'.h($r['descricao']).'</td></tr>';
if(!$rows) echo '<tr><td colspan="5">Nenhum equipamento cadastrado.</td></tr>';
echo '</tbody></table></div></section>'; page_footer();