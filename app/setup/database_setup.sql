-- app/setup/database_setup.sql

-- -----------------------------------------------------------
-- CRIAÇÃO DE TABELAS
-- -----------------------------------------------------------

-- 1. Tabela de Centros de Custo (C.C.)
CREATE TABLE ccustos (
    id_cc INT AUTO_INCREMENT PRIMARY KEY,
    nome_cc VARCHAR(100) NOT NULL,
    sigla_cc VARCHAR(10) NOT NULL UNIQUE,
    status ENUM('Ativo', 'Inativo') DEFAULT 'Ativo'
);

-- 2. Tabela de Usuários (Acesso ao Sistema)
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    funcao VARCHAR(100),
    id_cc_principal INT,
    nivel_acesso ENUM('Admin', 'RH', 'Gestor', 'Colaborador') NOT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_cc_principal) REFERENCES ccustos(id_cc) -- Chave estrangeira para o CC principal
);

-- 3. Tabela N:N para Centros de Custo Liberados (Permissões de Acesso)
CREATE TABLE usuario_cc_liberado (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_cc INT NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_cc) REFERENCES ccustos(id_cc),
    UNIQUE KEY (id_usuario, id_cc) -- Garante que um usuário não tenha o mesmo CC liberado duas vezes
);

-- 4. Tabela Colaboradores
CREATE TABLE colaboradores (
    matricula INT PRIMARY KEY, -- Usando matrícula como PK
    nome VARCHAR(150) NOT NULL,
    funcao VARCHAR(100) NOT NULL,
    data_admissao DATE NOT NULL,
    tipo_contrato VARCHAR(50),
    status ENUM('Ativo', 'Transferido', 'Desligado', 'Férias') DEFAULT 'Ativo',
    id_cc_atual INT NOT NULL,
    FOREIGN KEY (id_cc_atual) REFERENCES ccustos(id_cc)
);

-- 5. Tabela Movimentações (Histórico)
CREATE TABLE movimentacoes (
    id_mov INT AUTO_INCREMENT PRIMARY KEY,
    matricula_colaborador INT NOT NULL,
    id_cc_origem INT,
    id_cc_destino INT NOT NULL,
    tipo_movimentacao ENUM('Transferência', 'Admissão', 'Desligamento', 'Status Alterado') NOT NULL,
    data_movimentacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    motivo TEXT,
    status_aprovacao ENUM('Aprovada', 'Pendente', 'Rejeitada') DEFAULT 'Aprovada',
    id_usuario_registro INT NOT NULL,
    FOREIGN KEY (matricula_colaborador) REFERENCES colaboradores(matricula),
    FOREIGN KEY (id_cc_origem) REFERENCES ccustos(id_cc),
    FOREIGN KEY (id_cc_destino) REFERENCES ccustos(id_cc),
    FOREIGN KEY (id_usuario_registro) REFERENCES usuarios(id_usuario)
);

-- 6. Tabela de Logs (Auditoria)
CREATE TABLE logs_sistema (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    acao TEXT NOT NULL,
    data_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_origem VARCHAR(45),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);


-- -----------------------------------------------------------
-- INSERÇÃO DE DADOS INICIAIS (Centros de Custo Mestre)
-- -----------------------------------------------------------

-- INSERÇÃO DOS CENTROS DE CUSTO FORNECIDOS (Sigla/Código e Nome/Descrição)
INSERT INTO ccustos (sigla_cc, nome_cc, status) VALUES
('2011', 'Vedação', 'Ativo'),
('2012', 'Cabeceiras', 'Ativo'),
('2069', 'Alto Forno', 'Ativo'),
('2071', 'Coqueria', 'Ativo'),
('2075', 'Aciaria', 'Ativo'),
('4379', 'Regeneradores', 'Ativo');

-- INSERÇÃO DO C.C. ADMINISTRATIVO (Para vinculação do Admin do Sistema)
INSERT INTO ccustos (sigla_cc, nome_cc, status) VALUES 
('ADM', 'Administração Geral do Sistema', 'Ativo');