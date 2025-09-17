<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ============================================================================
// PHP Setups
// ============================================================================

date_default_timezone_set('Asia/Kuala_Lumpur');
session_start();

// ============================================================================
// General Page Functions
// ============================================================================

// Is GET request?
function is_get() {
    return $_SERVER['REQUEST_METHOD'] == 'GET';
}

// Is POST request?
function is_post() {
    return $_SERVER['REQUEST_METHOD'] == 'POST';
}

// Obtain GET parameter
function get($key, $value = null) {
    $value = $_GET[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Obtain POST parameter
function post($key, $value = null) {
    $value = $_POST[$key] ?? $value;
    return is_array($value) ? array_map('trim', $value) : trim($value);
}

// Obtain REQUEST (GET and POST) parameter
//function req($key, $value = null) {
//    $value = $_REQUEST[$key] ?? $value;
//    return is_array($value) ? array_map('trim', $value) : trim($value);
//}

function req($key, $value = null) {
    $value = $_REQUEST[$key] ?? $value;
    // Ensure $value is not null before applying trim()
    if (is_array($value)) {
        return array_map(function($item) {
            return is_string($item) ? trim($item) : $item;
        }, $value);
    }
    return is_string($value) ? trim($value) : $value;
}


// Redirect to URL
function redirect($url = null) {
    $url ??= $_SERVER['REQUEST_URI'];
    header("Location: $url");
    exit();
}

// Set or get temporary session variable
function temp($key, $value = null) {
    if ($value !== null) {
        $_SESSION["temp_$key"] = $value;
    }
    else {
        $value = $_SESSION["temp_$key"] ?? null;
        unset($_SESSION["temp_$key"]);
        return $value;
    }
}

// Obtain uploaded file --> cast to object
function get_file($key) {
    $f = $_FILES[$key] ?? null;
    
    if ($f && $f['error'] == 0) {
        return (object)$f;
    }

    return null;
}

// Crop, resize and save photo
function save_photo($f, $folder, $width = 200, $height = 200) {
    $photo = uniqid() . '.jpg';
    
    require_once __DIR__ . '/../lib/SimpleImage.php';
    $img = new SimpleImage();
    $img->fromFile($f->tmp_name)
        ->thumbnail($width, $height)
        ->toFile("$folder/$photo", 'image/jpeg');

    return $photo;
}

// Is money?
function is_money($value) {
    return preg_match('/^\-?\d+(\.\d{1,2})?$/', $value);
}

// Is email?
function is_email($value) {
    return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
}

// Return local root path
function root($path = '') {
    return "$_SERVER[DOCUMENT_ROOT]/$path";
}

// Return base url (host + port)
function base($path = '') {
    return "http://$_SERVER[SERVER_NAME]:$_SERVER[SERVER_PORT]/$path";
}

// ============================================================================
// HTML Helpers
// ============================================================================

// Placeholder for TODO
function TODO() {
    echo '<span>TODO</span>';
}

// Encode HTML special characters
function encode($value) {
    return htmlentities($value);
}

// Generate <input type='hidden'>
function html_hidden($key, $attr = '') {
    $value ??= encode($GLOBALS[$key] ?? '');
    echo "<input type='hidden' id='$key' name='$key' value='$value' $attr>";
}

function html_hidden2($key, $attr = '', $value = null) {
    if ($value === null) {
        $value = encode($GLOBALS[$key] ?? '');
    } else {
        $value = encode($value);
    }
    echo "<input type='hidden' id='$key' name='$key' value='$value' $attr>";
}

// Generate <input type='text'>
function html_text($key, $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='text' id='$key' name='$key' value='$value' $attr>";
}

function html_text2($key, $attr = '', $value = null) {
    if ($value === null) {
        $value = encode($GLOBALS[$key] ?? '');
    } else {
        $value = encode($value);
    }
    echo "<input type='text' id='$key' name='$key' value='$value' $attr>";
}

// Generate <input type='password'>
function html_password($key, $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='password' id='$key' name='$key' value='$value' $attr>";
}

function html_password2($key, $attr = '', $value = null) {
    if ($value === null) {
        $value = encode($GLOBALS[$key] ?? '');
    } else {
        $value = encode($value);
    }
    echo "<input type='password' id='$key' name='$key' value='$value' $attr>";
}

// Generate <input type='number'>
function html_number($key, $min = '', $max = '', $step = '', $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='number' id='$key' name='$key' value='$value'
                 min='$min' max='$max' step='$step' $attr>";
}

function html_number2($key, $min = '', $max = '', $step = '', $attr = '', $value = null) {
    if ($value === null) {
        $value = encode($GLOBALS[$key] ?? '');
    } else {
        $value = encode($value);
    }
    echo "<input type='number' id='$key' name='$key' value='$value'
                 min='$min' max='$max' step='$step' $attr>";
}

// Generate <input type='search'>
function html_search($key, $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='search' id='$key' name='$key' value='$value' $attr>";
}

function html_search2($key, $attr = '', $value = null) {
    if ($value === null) {
        $value = encode($GLOBALS[$key] ?? '');
    } else {
        $value = encode($value);
    }
    echo "<input type='search' id='$key' name='$key' value='$value' $attr>";
}

// Generate <textarea>
function html_textarea($key, $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<textarea id='$key' name='$key' $attr>$value</textarea>";
}

function html_textarea2($key, $attr = '', $value = null) {
    if ($value === null) {
        $value = encode($GLOBALS[$key] ?? '');
    } else {
        $value = encode($value);
    }
    echo "<textarea id='$key' name='$key' $attr >$value</textarea>";
}

// Generate SINGLE <input type='checkbox'>
function html_checkbox($key, $label = '', $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    $status = $value == 1 ? 'checked' : '';
    echo "<label><input type='checkbox' id='$key' name='$key' value='1' $status $attr>$label</label>";
}

function html_checkbox2($key, $label = '', $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    $status = $value == 1 ? 'checked' : '';
    echo "<label><input type='checkbox' id='$key' name='$key' value='1' $status $attr>$label</label>";
}


// Generate <input type='radio'> list
function html_radios($key, $items, $br = false) {
    $value = encode($GLOBALS[$key] ?? '');
    echo '<div>';
    foreach ($items as $id => $text) {
        $state = $id == $value ? 'checked' : '';
        echo "<label><input type='radio' id='{$key}_$id' name='$key' value='$id' $state>$text</label>";
        if ($br) {
            echo '<br>';
        }
    }
    echo '</div>';
}

// Generate <select>
function html_select($key, $items, $default = '- Select One -', $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<select id='$key' name='$key' $attr>";
    if ($default !== null) {
        echo "<option value=''>$default</option>";
    }
    foreach ($items as $id => $text) {
        $state = $id == $value ? 'selected' : '';
        echo "<option value='$id' $state>$text</option>";
    }
    echo '</select>';
}

function html_select2($key, $items, $default = '- Select One -', $attr = '', $selected = null) {
    // Initialize the HTML string
    $html = "<select id='$key' name='$key' $attr>";
    
    // Add the default option
    if ($default !== null) {
        $html .= "<option value=''>$default</option>";
    }
    
    // Loop through items to create options
    foreach ($items as $value => $label) {
        $isSelected = ($value == $selected) ? ' selected' : '';
        $html .= "<option value=\"$value\"$isSelected>$label</option>";
    }
    
    // Close the select tag
    $html .= "</select>";
    
    return $html;
}


// Generate <input type='date'>
function html_date($key, $min= '', $max = '', $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='date' id='$key' name='$key' value='$value'
                 min='$min' max='$max' $attr>";
}

// Generate <input type='time'>
function html_time($key, $attr = '') {
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='time' id='$key' name='$key' value='$value' $attr>";
}


// Generate <input type='date'>
function html_date2($key, $min= '', $max = '', $attr = '', $value = null) {
    if ($value === null) {
        $value = encode($GLOBALS[$key] ?? '');
    } else {
        $value = encode($value);
    }
    echo "<input type='date' id='$key' name='$key' value='$value'
                 min='$min' max='$max' $attr>";
}

// Generate <input type='time'>
function html_time2($key, $attr = '', $value = null) {
    if ($value === null) {
        $value = encode($GLOBALS[$key] ?? '');
    } else {
        $value = encode($value);
    }
    $value = encode($GLOBALS[$key] ?? '');
    echo "<input type='time' id='$key' name='$key' value='$value' $attr>";
}



// Generate <input type='file'>
function html_file($key, $accept = '', $attr = '') {
    echo "<input type='file' id='$key' name='$key' accept='$accept' $attr>";
}

// Generate table headers <th>
function table_headers($fields, $sort, $dir, $href = '') {
    foreach ($fields as $k => $v) {
        $d = 'asc'; // Default direction
        $c = '';    // Default class for sorting
        
        if ($k == $sort) {
            $d = $dir == 'asc' ? 'desc' : 'asc'; // Toggle direction
            $c = $dir;  // Apply sorting class 'asc' or 'desc'
        }

        echo "<th><a href='?sort=$k&dir=$d&$href' class='$c'>$v</a></th>";
    }
}



// ============================================================================
// Error Handlings
// ============================================================================

// Global error array
$_err = [];

// Generate <span class='err'>
function err($key) {
    global $_err;
    if ($_err[$key] ?? false) {
        echo "<span class='err'>$_err[$key]</span>";
    }
    else {
        echo '<span></span>';
    }
}

// ============================================================================
// Security
// ============================================================================

// Global user object
$_user = $_SESSION['user'] ?? null;

// Login user
function loginMember($user, $url = 'index.php') {
    $_SESSION['user'] = $user;
    redirect($url);
}

function loginAdmin($user, $url = 'admin/admin_dashboard.php') {
    $_SESSION['user'] = $user;
    redirect($url);
}

// Logout user
function logout($url = 'index.php') {
    unset($_SESSION['user']);
    redirect($url);
}

// Authorization
function auth(...$roles) {
    global $_user;
    if ($_user) {
        if($_user->status == "active"){
            if ($roles) {
                if (in_array($_user->role, $roles)) {
                    return; // OK
                }
            }
            else {
                return; // OK
            }
        }
        
    }
    

    temp('info', 'Please login first');
    redirect('login.php');

}

// ============================================================================
// Email Functions
// ============================================================================

// Demo Accounts:
// --------------
// AACS3173@gmail.com           npsg gzfd pnio aylm
// BAIT2173.email@gmail.com     ytwo bbon lrvw wclr
// liaw.casual@gmail.com        wtpa kjxr dfcb xkhg
// liawcv1@gmail.com            obyj shnv prpa kzvj

// Initialize and return mail object
function get_mail() {

    require_once __DIR__ . '/../lib/PHPMailer.php';
    require_once  __DIR__ . '/../lib/SMTP.php';

    $m = new PHPMailer(true);
    $m->isSMTP();
    $m->SMTPAuth = true;
    $m->Host = 'smtp.gmail.com';
    $m->Port = 587;
    $m->Username = 'tursoassignment@gmail.com';
    $m->Password = 'unob wsqm mrxv kgdi';
    $m->CharSet = 'utf-8';
    $m->setFrom($m->Username, '😺 Admin');

    return $m;
}

// ============================================================================
// Shopping Cart
// ============================================================================

// Get shopping cart
function get_cart() {
    return $_SESSION['cart'] ?? [];
}

// Set shopping cart
function set_cart($cart = []) {
    $_SESSION['cart'] = $cart;
}

// Update shopping cart
function update_cart($id, $unit) {
    $cart = get_cart();

    if ($unit >= 1 && $unit <= 10 && is_exists($id, 'product', 'id')) {
        $cart[$id] = $unit;
        ksort($cart);
    }
    else {
        unset($cart[$id]);
    }

    set_cart($cart);
}

// ============================================================================
// Database Setups and Functions
// ============================================================================

try {
    $_db = new PDO(
        "mysql:host=rds-stack-myrdsinstance-iaphxerfkway.cng5rlrnyzca.us-east-1.rds.amazonaws.com;dbname=ecommerce;charset=utf8mb4",
        "admin",
        "admin123*",
        [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}



// Is unique?
function is_unique($value, $table, $field) {
    global $_db;
    $stm = $_db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetchColumn() == 0;
}

// Is exists?
function is_exists($value, $table, $field) {
    global $_db;
    $stm = $_db->prepare("SELECT COUNT(*) FROM $table WHERE $field = ?");
    $stm->execute([$value]);
    return $stm->fetchColumn() > 0;
}

// ============================================================================
// Global Constants and Variables
// ============================================================================

// Range 1-10
$_units = array_combine(range(1, 10), range(1, 10));
