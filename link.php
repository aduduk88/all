<?php
// Fungsi untuk mengambil konten dari URL menggunakan cURL
function fetchUrlContent($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Nonaktifkan verifikasi SSL
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && !empty($response)) {
        return $response;
    } else {
        return null;
    }
}

// Fungsi untuk mengekstrak semua link dari HTML
function extractLinks($html) {
    $dom = new DOMDocument();
    @$dom->loadHTML($html); // Gunakan @ untuk menghindari warning jika HTML tidak valid
    $xpath = new DOMXPath($dom);

    // Ambil semua elemen <a>
    $links = $xpath->query('//a');
    $result = [];

    foreach ($links as $link) {
        $href = $link->getAttribute('href');
        $text = $link->textContent;
        $result[] = [
            'text' => $text,
            'href' => $href
        ];
    }

    return $result;
}

// Proses form jika ada input URL
$links = [];
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['url'])) {
    $url = trim($_POST['url']);
    if (!empty($url)) {
        $content = fetchUrlContent($url);
        if ($content !== null) {
            $links = extractLinks($content);
            if (empty($links)) {
                $error = "Tidak ada link yang ditemukan.";
            }
        } else {
            $error = "Gagal mengambil data dari URL. Pastikan URL valid dan dapat diakses.";
        }
    } else {
        $error = "URL tidak boleh kosong!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Extract Links from URL</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #007bff;
        }
        form {
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .btn {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .result-box {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .result-box h2 {
            margin-top: 0;
        }
        .result-box ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .result-box li {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .result-box li:last-child {
            border-bottom: none;
        }
        .result-box a {
            color: #007bff;
            text-decoration: none;
        }
        .result-box a:hover {
            text-decoration: underline;
        }
        .alert {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Extract Links from URL</h1>

        <!-- Form untuk input URL -->
        <form method="POST" action="">
            <div class="form-group">
                <label for="url">Masukkan URL:</label>
                <input type="text" id="url" name="url" placeholder="Contoh: https://example.com" required>
            </div>
            <button type="submit" class="btn">Extract Links</button>
        </form>

        <!-- Hasil Extract Links -->
        <div class="result-box">
            <h2>Hasil Extract Links:</h2>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php elseif (!empty($links)): ?>
                <ul>
                    <?php foreach ($links as $link): ?>
                        <li>
                            <strong>Text:</strong> <?php echo htmlspecialchars($link['text']); ?><br>
                            <strong>URL:</strong> <a href="<?php echo htmlspecialchars($link['href']); ?>" target="_blank"><?php echo htmlspecialchars($link['href']); ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="alert alert-warning">Masukkan URL untuk mengekstrak link.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>