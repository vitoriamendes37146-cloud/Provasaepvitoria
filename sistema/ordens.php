<?php
require __DIR__ . '/config.php'; require_login();
$statuses=['Aberta','Em andamento','Aguardando peça','Concluída','Cancelada'];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_check(); $acao=$_POST['acao']??'';
    if ($acao==='criar') {
        $equip=(int)($_POST['equipamento_id']??0); $defeito=trim($_POST['defeito']??''); $tecnico=trim($_POST['tecnico']??''); $valor=str_replace(',','.',trim($_POST['valor']??'0'));
        if (!$equip || $defeito==='') flash('Selecione o equipamento e informe o defeito relatado.');
        elseif (!is_numeric($valor) || (float)$valor<0) flash('Valor inválido.');
        else { $s=db()->prepare('INSERT INTO ordens_servico (equipamento_id,defeito,tecnico,valor,status) VALUES (?,?,?,?,?)'); $s->execute([$equip,$defeito,$tecnico,(float)$valor,'Aberta']); flash('Ordem de serviço criada.'); }
    } elseif ($acao==='status') {
        $id=(int)($_POST['id']??0); $status=$_POST['status']??'';
        if(in_array($status,$statuses,true)){ $s=db()->prepare('UPDATE ordens_servico SET status=? WHERE id=?'); $s->execute([$status,$id]); flash('Status atualizado.'); }
    }
    header('Location: ordens.php'); exit;
}
$equipamentos=db()->query('SELECT e.id,e.tipo,e.marca,e.modelo,c.nome cliente FROM equipamentos e JOIN clientes c ON c.id=e.cliente_id ORDER BY c.nome')->fetchAll();
$rows=db()->query('SELECT o.*,e.tipo,e.marca,e.modelo,c.nome cliente FROM ordens_servico o JOIN equipamentos e ON e.id=o.equipamento_id JOIN clientes c ON c.id=e.cliente_id ORDER BY o.data_abertura DESC, o.id DESC')->fetchAll();
page_header('Ordens de serviço');
echo '<section class="panel"><h2>Abrir ordem de serviço</h2><form method="post" class="form-grid"><input type="hidden" name="csrf" value="'.h(csrf_token()).'"><input type="hidden" name="acao" value="criar"><label>Equipamento *<select name="equipamento_id" required><option value="">Selecione...</option>';
foreach($equipamentos as $e) echo '<option value="'.(int)$e['id'].'">'.h($e['cliente'].' — '.$e['tipo'].' '.$e['marca'].' '.$e['modelo']).'</option>';
echo '</select></label><label>Defeito relatado *<input name="defeito" required></label><label>Técnico responsável<input name="tecnico"></label><label>Valor estimado (R$)<input name="valor" type="number" min="0" step="0.01" value="0.00"></label><button>Abrir ordem</button></form></section><section class="panel"><h2>Ordens registradas</h2><div class="table-wrap"><table><thead><tr><th>Abertura</th><th>Cliente</th><th>Equipamento</th><th>Defeito</th><th>Técnico</th><th>Valor</th><th>Status</th></tr></thead><tbody>';
foreach($rows as $r) { echo '<tr><td>'.h(date('d/m/Y H:i',strtotime($r['data_abertura']))).'</td><td>'.h($r['cliente']).'</td><td>'.h(trim($r['tipo'].' '.$r['marca'].' '.$r['modelo'])).'</td><td>'.h($r['defeito']).'</td><td>'.h($r['tecnico']).'</td><td>R$ '.number_format((float)$r['valor'],2,',','.').'</td><td><form method="post" class="inline"><input type="hidden" name="csrf" value="'.h(csrf_token()).'"><input type="hidden" name="acao" value="status"><input type="hidden" name="id" value="'.(int)$r['id'].'"><select name="status">';
foreach($statuses as $st) echo '<option'.($r['status']===$st?' selected':'').'>'.h($st).'</option>';
echo '</select><button class="small">Salvar</button></form></td></tr>'; }
if(!$rows) echo '<tr><td colspan="7">Nenhuma ordem de serviço cadastrada.</td></tr>';
echo '</tbody></table></div></section>'; page_footer();