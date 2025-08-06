<?php
// db.php - Database e API per MCL Voghera - VERSIONE DEBUG

// Abilita gli errori per debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configurazione database
$host = 'localhost';
$username = 'root';
$password = 'root';
$dbname = 'mcl_voghera';

// Headers per API
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Avvia la sessione
session_start();

// Log per debug
file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Avvio script\n", FILE_APPEND);

// Connessione al database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Connessione DB OK\n", FILE_APPEND);
} catch(PDOException $e) {
    file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Errore connessione DB: " . $e->getMessage() . "\n", FILE_APPEND);
    
    // Se il database non esiste, crealo
    try {
        $pdo = new PDO("mysql:host=$host", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Crea database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Database creato\n", FILE_APPEND);
        
        // Riconnettiti al nuovo database
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        // Crea tabelle
        createTables($pdo);
        
    } catch(PDOException $e) {
        file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Errore fatale: " . $e->getMessage() . "\n", FILE_APPEND);
        die(json_encode(['error' => 'Errore di connessione: ' . $e->getMessage()]));
    }
}

function createTables($pdo) {
    file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Creazione tabelle\n", FILE_APPEND);
    
    // Crea tabella utenti
    $sql_users = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql_users);
    
    // Crea tabella eventi
    $sql_eventi = "CREATE TABLE IF NOT EXISTS eventi (
        id INT AUTO_INCREMENT PRIMARY KEY,
        titolo VARCHAR(255) NOT NULL,
        descrizione TEXT NOT NULL,
        data_creazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        data_evento DATE NULL,
        prezzo DECIMAL(10,2) NULL
    )";
    
    $pdo->exec($sql_eventi);
    
    // Inserisci utente admin se non esiste
    $admin_password = password_hash('mcl2025', PASSWORD_DEFAULT);
    file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Password hash: " . $admin_password . "\n", FILE_APPEND);
    
    $sql_admin = "INSERT IGNORE INTO users (username, password) VALUES ('admin', ?)";
    $stmt = $pdo->prepare($sql_admin);
    $result = $stmt->execute([$admin_password]);
    
    file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Inserimento admin: " . ($result ? 'OK' : 'FAIL') . "\n", FILE_APPEND);
    
    // Verifica che l'utente admin sia stato creato
    $stmt = $pdo->prepare("SELECT username FROM users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch();
    file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Admin trovato: " . ($admin ? 'SI' : 'NO') . "\n", FILE_APPEND);
    
    // Inserisci evento di esempio
    $stmt = $pdo->query("SELECT COUNT(*) FROM eventi");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        $sql_evento = "INSERT INTO eventi (titolo, descrizione, data_evento, prezzo) 
                      VALUES ('GITA A ROMA', 'Partenza da Voghera Piazza Duomo e Arrivo a Roma dove visiteremo San Pietro e la Cappella Sistina. La gita avrà una durata di 3 giorni con partenza da Voghera il 7/8/2025 e ritorno il 10 in tarda serata. Prezzo di 350€ a persona.', '2025-08-07', 350.00)";
        $pdo->exec($sql_evento);
    }
}

// Verifica che le tabelle esistano
try {
    $pdo->query("SELECT 1 FROM users LIMIT 1");
    $pdo->query("SELECT 1 FROM eventi LIMIT 1");
    file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Tabelle esistono\n", FILE_APPEND);
} catch(PDOException $e) {
    file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Tabelle non esistono, le creo\n", FILE_APPEND);
    createTables($pdo);
}

// Gestione delle azioni
$action = $_GET['action'] ?? '';
file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Azione richiesta: " . $action . "\n", FILE_APPEND);

switch($action) {
    
    case 'login':
        file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Processo di login\n", FILE_APPEND);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $raw_input = file_get_contents('php://input');
            file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Raw input: " . $raw_input . "\n", FILE_APPEND);
            
            $input = json_decode($raw_input, true);
            file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Input decodificato: " . print_r($input, true) . "\n", FILE_APPEND);
            
            $username = $input['username'] ?? '';
            $password = $input['password'] ?? '';
            
            file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Username: '$username', Password: '$password'\n", FILE_APPEND);
            
            if (empty($username) || empty($password)) {
                $response = [
                    'success' => false,
                    'message' => 'Username e password sono obbligatori'
                ];
                file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Campi vuoti\n", FILE_APPEND);
                echo json_encode($response);
                exit;
            }
            
            try {
                $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = ?");
                $stmt->execute([$username]);
                $user = $stmt->fetch();
                
                file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Utente dal DB: " . print_r($user, true) . "\n", FILE_APPEND);
                
                if ($user) {
                    $password_check = password_verify($password, $user['password']);
                    file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Password check: " . ($password_check ? 'OK' : 'FAIL') . "\n", FILE_APPEND);
                    
                    if ($password_check) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        
                        $response = [
                            'success' => true,
                            'message' => 'Login effettuato con successo',
                            'user' => ['id' => $user['id'], 'username' => $user['username']]
                        ];
                        file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Login OK\n", FILE_APPEND);
                    } else {
                        $response = [
                            'success' => false,
                            'message' => 'Password non corretta'
                        ];
                        file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Password sbagliata\n", FILE_APPEND);
                    }
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'Username non trovato'
                    ];
                    file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Utente non trovato\n", FILE_APPEND);
                }
                
                echo json_encode($response);
                
            } catch(PDOException $e) {
                file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Errore DB login: " . $e->getMessage() . "\n", FILE_APPEND);
                echo json_encode([
                    'success' => false,
                    'message' => 'Errore del database: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Metodo non consentito'
            ]);
        }
        exit;
        
    case 'check_auth':
        $logged_in = isset($_SESSION['user_id']) && isset($_SESSION['username']);
        file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Check auth: " . ($logged_in ? 'LOGGED IN' : 'NOT LOGGED') . "\n", FILE_APPEND);
        
        echo json_encode([
            'logged_in' => $logged_in,
            'user' => $logged_in ? ['username' => $_SESSION['username']] : null
        ]);
        exit;
        
    case 'logout':
        session_destroy();
        session_start();
        file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Logout OK\n", FILE_APPEND);
        echo json_encode(['success' => true, 'message' => 'Logout effettuato']);
        exit;
        
    case 'get_eventi':
        try {
            $stmt = $pdo->query("SELECT * FROM eventi ORDER BY data_creazione DESC");
            $eventi = $stmt->fetchAll();
            echo json_encode($eventi);
        } catch(PDOException $e) {
            echo json_encode([]);
        }
        exit;
        
    case 'add_evento':
        // Verifica che l'utente sia loggato
        if (!isset($_SESSION['user_id'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Devi essere loggato per aggiungere eventi'
            ]);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $raw_input = file_get_contents('php://input');
            file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Add evento input: " . $raw_input . "\n", FILE_APPEND);
            
            $input = json_decode($raw_input, true);
            
            $titolo = $input['titolo'] ?? '';
            $descrizione = $input['descrizione'] ?? '';
            $data_evento = !empty($input['data_evento']) ? $input['data_evento'] : null;
            $prezzo = !empty($input['prezzo']) ? floatval($input['prezzo']) : null;
            
            file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Dati evento: titolo='$titolo', descrizione='$descrizione', data='$data_evento', prezzo='$prezzo'\n", FILE_APPEND);
            
            if (empty($titolo) || empty($descrizione)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Titolo e descrizione sono obbligatori'
                ]);
                exit;
            }
            
            try {
                $sql = "INSERT INTO eventi (titolo, descrizione, data_evento, prezzo) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $result = $stmt->execute([$titolo, $descrizione, $data_evento, $prezzo]);
                
                if ($result) {
                    file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Evento aggiunto con successo\n", FILE_APPEND);
                    echo json_encode([
                        'success' => true,
                        'message' => 'Evento aggiunto con successo'
                    ]);
                } else {
                    file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Errore nell'aggiungere evento\n", FILE_APPEND);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Errore nell\'aggiungere l\'evento'
                    ]);
                }
            } catch(PDOException $e) {
                file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Errore DB add evento: " . $e->getMessage() . "\n", FILE_APPEND);
                echo json_encode([
                    'success' => false,
                    'message' => 'Errore del database: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Metodo non consentito'
            ]);
        }
        exit;
        
    case 'delete_evento':
        // Verifica che l'utente sia loggato
        if (!isset($_SESSION['user_id'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Devi essere loggato per eliminare eventi'
            ]);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $raw_input = file_get_contents('php://input');
            file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Delete evento input: " . $raw_input . "\n", FILE_APPEND);
            
            $input = json_decode($raw_input, true);
            
            $id = $input['id'] ?? '';
            
            file_put_contents('debug.log', date('Y-m-d H:i:s') . " - ID evento da eliminare: '$id'\n", FILE_APPEND);
            
            if (empty($id)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'ID evento mancante'
                ]);
                exit;
            }
            
            try {
                $sql = "DELETE FROM eventi WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $result = $stmt->execute([$id]);
                
                if ($result && $stmt->rowCount() > 0) {
                    file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Evento eliminato con successo\n", FILE_APPEND);
                    echo json_encode([
                        'success' => true,
                        'message' => 'Evento eliminato con successo'
                    ]);
                } else {
                    file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Evento non trovato o già eliminato\n", FILE_APPEND);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Evento non trovato'
                    ]);
                }
            } catch(PDOException $e) {
                file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Errore DB delete evento: " . $e->getMessage() . "\n", FILE_APPEND);
                echo json_encode([
                    'success' => false,
                    'message' => 'Errore del database: ' . $e->getMessage()
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Metodo non consentito'
            ]);
        }
        exit;
        
    case 'test_db':
        // Endpoint per testare il database
        try {
            $stmt = $pdo->query("SELECT * FROM users");
            $users = $stmt->fetchAll();
            echo json_encode([
                'success' => true,
                'users' => $users,
                'message' => 'Database OK'
            ]);
        } catch(PDOException $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Errore database: ' . $e->getMessage()
            ]);
        }
        exit;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Azione non riconosciuta: ' . $action
        ]);
        exit;
}
?>