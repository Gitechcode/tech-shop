<?php
require_once '../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    try {
        switch ($action) {
            case 'add':
                $product_id = (int)($input['product_id'] ?? 0);
                $quantity = (int)($input['quantity'] ?? 1);
                
                if ($product_id <= 0 || $quantity <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Invalid product or quantity']);
                    exit;
                }
                
                // Check if product exists and has stock
                $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ? AND status = 'active'");
                $stmt->execute([$product_id]);
                $product = $stmt->fetch();
                
                if (!$product) {
                    echo json_encode(['success' => false, 'message' => 'Product not found']);
                    exit;
                }
                
                if ($product['stock'] < $quantity) {
                    echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
                    exit;
                }
                
                // Add to cart
                if (isLoggedIn()) {
                    // Check if item already exists in cart
                    $stmt = $pdo->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?");
                    $stmt->execute([$_SESSION['user_id'], $product_id]);
                    $existing = $stmt->fetch();
                    
                    if ($existing) {
                        $new_quantity = $existing['quantity'] + $quantity;
                        if ($new_quantity > $product['stock']) {
                            echo json_encode(['success' => false, 'message' => 'Cannot add more items than available stock']);
                            exit;
                        }
                        
                        $stmt = $pdo->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE user_id = ? AND product_id = ?");
                        $stmt->execute([$new_quantity, $_SESSION['user_id'], $product_id]);
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                        $stmt->execute([$_SESSION['user_id'], $product_id, $quantity]);
                    }
                } else {
                    // Guest user - use session
                    $session_id = session_id();
                    
                    $stmt = $pdo->prepare("SELECT quantity FROM cart WHERE session_id = ? AND product_id = ?");
                    $stmt->execute([$session_id, $product_id]);
                    $existing = $stmt->fetch();
                    
                    if ($existing) {
                        $new_quantity = $existing['quantity'] + $quantity;
                        if ($new_quantity > $product['stock']) {
                            echo json_encode(['success' => false, 'message' => 'Cannot add more items than available stock']);
                            exit;
                        }
                        
                        $stmt = $pdo->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE session_id = ? AND product_id = ?");
                        $stmt->execute([$new_quantity, $session_id, $product_id]);
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO cart (session_id, product_id, quantity) VALUES (?, ?, ?)");
                        $stmt->execute([$session_id, $product_id, $quantity]);
                    }
                }
                
                echo json_encode(['success' => true, 'message' => 'Product added to cart']);
                break;
                
            case 'update':
                $product_id = (int)($input['product_id'] ?? 0);
                $quantity = (int)($input['quantity'] ?? 1);
                
                if ($product_id <= 0 || $quantity <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Invalid product or quantity']);
                    exit;
                }
                
                // Check stock
                $stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
                $stmt->execute([$product_id]);
                $product = $stmt->fetch();
                
                if ($quantity > $product['stock']) {
                    echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
                    exit;
                }
                
                if (isLoggedIn()) {
                    $stmt = $pdo->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE user_id = ? AND product_id = ?");
                    $stmt->execute([$quantity, $_SESSION['user_id'], $product_id]);
                } else {
                    $session_id = session_id();
                    $stmt = $pdo->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE session_id = ? AND product_id = ?");
                    $stmt->execute([$quantity, $session_id, $product_id]);
                }
                
                echo json_encode(['success' => true, 'message' => 'Cart updated']);
                break;
                
            case 'remove':
                $product_id = (int)($input['product_id'] ?? 0);
                
                if (isLoggedIn()) {
                    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
                    $stmt->execute([$_SESSION['user_id'], $product_id]);
                } else {
                    $session_id = session_id();
                    $stmt = $pdo->prepare("DELETE FROM cart WHERE session_id = ? AND product_id = ?");
                    $stmt->execute([$session_id, $product_id]);
                }
                
                echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
                break;
                
            case 'clear':
                if (isLoggedIn()) {
                    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                } else {
                    $session_id = session_id();
                    $stmt = $pdo->prepare("DELETE FROM cart WHERE session_id = ?");
                    $stmt->execute([$session_id]);
                }
                
                echo json_encode(['success' => true, 'message' => 'Cart cleared']);
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        error_log("Cart API Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    if ($action === 'count') {
        try {
            $count = getCartCount();
            echo json_encode(['success' => true, 'count' => $count]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'count' => 0]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
