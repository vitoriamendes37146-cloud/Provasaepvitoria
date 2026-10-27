CREATE DATABASE IF NOT EXISTS saep_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE saep_db;
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS ordens_servico;
DROP TABLE IF EXISTS equipamentos;
DROP TABLE IF EXISTS clientes;
DROP TABLE IF EXISTS usuarios;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE usuarios (
 id INT AUTO_INCREMENT PRIMARY KEY,
 nome VARCHAR(120) NOT NULL,
 usuario VARCHAR(60) NOT NULL UNIQUE,
 senha_hash VARCHAR(255) NOT NULL,
 ativo TINYINT(1) NOT NULL DEFAULT 1,
 criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE clientes (
 id INT AUTO_INCREMENT PRIMARY KEY,
 nome VARCHAR(140) NOT NULL,
 cpf_criptografado TEXT NULL,
 cpf_hash CHAR(64) NULL UNIQUE,
 telefone VARCHAR(30) NULL,
 email VARCHAR(160) NULL,
 criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE equipamentos (
 id INT AUTO_INCREMENT PRIMARY KEY,
 cliente_id INT NOT NULL,
 tipo VARCHAR(80) NOT NULL,
 marca VARCHAR(80) NULL,
 modelo VARCHAR(100) NULL,
 numero_serie VARCHAR(100) NULL UNIQUE,
 descricao VARCHAR(255) NULL,
 criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_equip_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE ordens_servico (
 id INT AUTO_INCREMENT PRIMARY KEY,
 equipamento_id INT NOT NULL,
 defeito VARCHAR(255) NOT NULL,
 tecnico VARCHAR(120) NULL,
 valor DECIMAL(10,2) NOT NULL DEFAULT 0.00,
 status ENUM('Aberta','Em andamento','Aguardando peça','Concluída','Cancelada') NOT NULL DEFAULT 'Aberta',
 data_abertura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_os_equip FOREIGN KEY (equipamento_id) REFERENCES equipamentos(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Usuário admin / senha inicial Admin@123 (bcrypt gerado com PHP password_hash).
INSERT INTO usuarios (nome, usuario, senha_hash, ativo) VALUES
('Vitória Mendes','admin','$2y$12$1snZ1LceXi0KsGH5nWiC/ur0tMLBDmziFNtgbPwMwnUzbyq4dUTEC','1'),
('Atendente Demonstração','atendente','$2y$12$1snZ1LceXi0KsGH5nWiC/ur0tMLBDmziFNtgbPwMwnUzbyq4dUTEC','1'),
('Técnico Demonstração','tecnico','$2y$12$1snZ1LceXi0KsGH5nWiC/ur0tMLBDmziFNtgbPwMwnUzbyq4dUTEC','1');

-- CPF dos dados fictícios demonstrativos é armazenado criptografado pela aplicação;
-- para manter o script SQL independente de uma chave local, estes registros usam CPF nulo.
INSERT INTO clientes (nome, cpf_criptografado, cpf_hash, telefone, email) VALUES
('Mariana Costa',NULL,NULL,'81999990001','mariana@example.com'),
('Pedro Almeida',NULL,NULL,'81999990002','pedro@example.com'),
('Ana Ribeiro',NULL,NULL,'81999990003','ana@example.com');

INSERT INTO equipamentos (cliente_id,tipo,marca,modelo,numero_serie,descricao) VALUES
(1,'Notebook','Dell','Inspiron 15','DEMO-NB-001','Não inicia'),
(2,'Desktop','Lenovo','ThinkCentre','DEMO-PC-002','Lentidão'),
(3,'Impressora','HP','LaserJet Pro','DEMO-IMP-003','Falha de impressão');

INSERT INTO ordens_servico (equipamento_id,defeito,tecnico,valor,status,data_abertura) VALUES
(1,'Equipamento não liga','Técnico Demonstração',180.00,'Aberta','2026-10-20 09:30:00'),
(2,'Sistema operacional lento','Técnico Demonstração',120.00,'Em andamento','2026-10-21 11:15:00'),
(3,'Papel não alimenta corretamente','Técnico Demonstração',95.00,'Aguardando peça','2026-10-22 14:00:00');
