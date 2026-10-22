<?php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require "config.php";

$rota = $_GET["rota"] ?? "";
$metodo = $_SERVER["REQUEST_METHOD"];
$id = $_GET["id"] ?? null;
$placa = $_GET["placa"] ?? null;

$corpo = json_decode(file_get_contents("php://input"), true) ?? [];

function responder($status, $dados) {
    http_response_code($status);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

function listarClientes($pdo, $id = null) {
    if ($id) {
        $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
        $stmt->execute([$id]);
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$cliente) responder(404, ["erro" => "Cliente não encontrado"]);
        responder(200, $cliente);
    } else {
        $stmt = $pdo->query("SELECT * FROM clientes");
        responder(200, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}

function criarCliente($pdo, $dados) {
    if (empty($dados['nome']) || empty($dados['email'])) {
        responder(400, ["erro" => "Campos 'nome' e 'email' são obrigatórios"]);
    }
    $stmt = $pdo->prepare("INSERT INTO clientes (nome, email, telefone) VALUES (?, ?, ?)");
    $stmt->execute([$dados['nome'], $dados['email'], $dados['telefone'] ?? null]);
    responder(201, ["mensagem" => "Cliente criado com sucesso", "id" => $pdo->lastInsertId()]);
}

function atualizarCliente($pdo, $id, $dados) {
    if (!$id) responder(400, ["erro" => "ID do cliente é obrigatório"]);
    if (empty($dados['nome']) || empty($dados['email'])) {
        responder(400, ["erro" => "Campos 'nome' e 'email' são obrigatórios"]);
    }
    
    $stmt = $pdo->prepare("UPDATE clientes SET nome = ?, email = ?, telefone = ? WHERE id = ?");
    $stmt->execute([$dados['nome'], $dados['email'], $dados['telefone'] ?? null, $id]);
    
    if ($stmt->rowCount() === 0) responder(404, ["erro" => "Cliente não encontrado ou sem alterações"]);
    responder(200, ["mensagem" => "Cliente atualizado com sucesso"]);
}

function deletarCliente($pdo, $id) {
    if (!$id) responder(400, ["erro" => "ID do cliente é obrigatório"]);
    $stmt = $pdo->prepare("DELETE FROM clientes WHERE id = ?");
    $stmt->execute([$id]);
    if ($stmt->rowCount() === 0) responder(404, ["erro" => "Cliente não encontrado"]);
    responder(200, ["mensagem" => "Cliente deletado com sucesso"]);
}

function listarFuncionarios($pdo, $id = null) {
    if ($id) {
        $stmt = $pdo->prepare("SELECT * FROM funcionarios WHERE id = ?");
        $stmt->execute([$id]);
        $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$funcionario) responder(404, ["erro" => "Funcionário não encontrado"]);
        responder(200, $funcionario);
    } else {
        $stmt = $pdo->query("SELECT * FROM funcionarios");
        responder(200, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}

function criarFuncionario($pdo, $dados) {
    if (empty($dados['nome']) || empty($dados['cargo'])) {
        responder(400, ["erro" => "Campos 'nome' e 'cargo' são obrigatórios"]);
    }
    $stmt = $pdo->prepare("INSERT INTO funcionarios (nome, cargo, salario) VALUES (?, ?, ?)");
    $stmt->execute([$dados['nome'], $dados['cargo'], $dados['salario'] ?? 0]);
    responder(201, ["mensagem" => "Funcionário criado com sucesso", "id" => $pdo->lastInsertId()]);
}

function atualizarFuncionario($pdo, $id, $dados) {
    if (!$id) responder(400, ["erro" => "ID do funcionário é obrigatório"]);
    if (empty($dados['nome']) || empty($dados['cargo'])) {
        responder(400, ["erro" => "Campos 'nome' e 'cargo' são obrigatórios"]);
    }
    
    $stmt = $pdo->prepare("UPDATE funcionarios SET nome = ?, cargo = ?, salario = ? WHERE id = ?");
    $stmt->execute([$dados['nome'], $dados['cargo'], $dados['salario'] ?? 0, $id]);
    
    if ($stmt->rowCount() === 0) responder(404, ["erro" => "Funcionário não encontrado ou sem alterações"]);
    responder(200, ["mensagem" => "Funcionário updated com sucesso"]);
}

function deletarFuncionario($pdo, $id) {
    if (!$id) responder(400, ["erro" => "ID do funcionário é obrigatório"]);
    $stmt = $pdo->prepare("DELETE FROM funcionarios WHERE id = ?");
    $stmt->execute([$id]);
    if ($stmt->rowCount() === 0) responder(404, ["erro" => "Funcionário não encontrado"]);
    responder(200, ["mensagem" => "Funcionário deletado com sucesso"]);
}

function listarVeiculos($pdo, $placa = null) {
    if ($placa) {
        $stmt = $pdo->prepare("SELECT * FROM veiculos WHERE placa = ?");
        $stmt->execute([$placa]);
        $veiculo = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$veiculo) responder(404, ["erro" => "Veículo não encontrado"]);
        responder(200, $veiculo);
    } else {
        $stmt = $pdo->query("SELECT * FROM veiculos");
        responder(200, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}

function criarVeiculo($pdo, $dados) {
    if (empty($dados['placa']) || empty($dados['marca']) || empty($dados['modelo'])) {
        responder(400, ["erro" => "Campos 'placa', 'marca' e 'modelo' são obrigatórios"]);
    }
    $stmt = $pdo->prepare("INSERT INTO veiculos (placa, marca, modelo, ano) VALUES (?, ?, ?, ?)");
    $stmt->execute([$dados['placa'], $dados['marca'], $dados['modelo'], $dados['ano'] ?? null]);
    responder(201, ["mensagem" => "Veículo criado com sucesso"]);
}

function atualizarVeiculo($pdo, $placa, $dados) {
    if (!$placa) responder(400, ["erro" => "Placa do veículo é obrigatória"]);
    if (empty($dados['marca']) || empty($dados['modelo'])) {
        responder(400, ["erro" => "Campos 'marca' e 'modelo' são obrigatórios"]);
    }
    
    $stmt = $pdo->prepare("UPDATE veiculos SET marca = ?, modelo = ?, ano = ? WHERE placa = ?");
    $stmt->execute([$dados['marca'], $dados['modelo'], $dados['ano'] ?? null, $placa]);
    
    if ($stmt->rowCount() === 0) responder(404, ["erro" => "Veículo não encontrado ou sem alterações"]);
    responder(200, ["mensagem" => "Veículo atualizado com sucesso"]);
}

function deletarVeiculo($pdo, $placa) {
    if (!$placa) responder(400, ["erro" => "Placa do veículo é obrigatória"]);
    $stmt = $pdo->prepare("DELETE FROM veiculos WHERE placa = ?");
    $stmt->execute([$placa]);
    if ($stmt->rowCount() === 0) responder(404, ["erro" => "Veículo não encontrado"]);
    responder(200, ["mensagem" => "Veículo deletado com sucesso"]);
}

switch ($rota) {
    case "test":
        responder(200, ["mensagem" => "API funcionando"]);
        break;

    case "clientes":
        switch ($metodo) {
            case "GET":    listarClientes($pdo, $id); break;
            case "POST":   criarCliente($pdo, $corpo); break;
            case "PUT":    atualizarCliente($pdo, $id, $corpo); break;
            case "DELETE": deletarCliente($pdo, $id); break;
            default:       responder(405, ["erro" => "Método não permitido"]); break;
        }
        break;

    case "funcionarios":
        switch ($metodo) {
            case "GET":    listarFuncionarios($pdo, $id); break;
            case "POST":   criarFuncionario($pdo, $corpo); break;
            case "PUT":    atualizarFuncionario($pdo, $id, $corpo); break;
            case "DELETE": deletarFuncionario($pdo, $id); break;
            default:       responder(405, ["erro" => "Método não permitido"]); break;
        }
        break;

    case "veiculos":
        switch ($metodo) {
            case "GET":    listarVeiculos($pdo, $placa); break;
            case "POST":   criarVeiculo($pdo, $corpo); break;
            case "PUT":    atualizarVeiculo($pdo, $placa, $corpo); break;
            case "DELETE": deletarVeiculo($pdo, $placa); break;
            default:       responder(405, ["erro" => "Método não permitido"]); break;
        }
        break;

    default:
        responder(404, ["erro" => "Rota não encontrada"]);
        break;
}
