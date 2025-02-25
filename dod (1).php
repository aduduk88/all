<?php
class DoodStream {
    private $apiKey;
    private $baseUrl;

    public function __construct($apiKey, $baseUrl = "https://doodapi.com/api/") {
        $this->apiKey = $apiKey;
        $this->baseUrl = $baseUrl;
    }

    private function makeRequest($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception("cURL Error: " . curl_error($ch));
        }
        curl_close($ch);
        return json_decode($response, true);
    }

    public function accountInfo() {
        $url = $this->baseUrl . "account/info?key=" . $this->apiKey;
        return $this->makeRequest($url);
    }

    public function listFiles($page = null, $perPage = null, $fldId = null) {
        $url = $this->baseUrl . "file/list?key=" . $this->apiKey;
        if ($page !== null) $url .= "&page=" . $page;
        if ($perPage !== null) $url .= "&per_page=" . $perPage;
        if ($fldId !== null) $url .= "&fld_id=" . $fldId;
        return $this->makeRequest($url);
    }

    public function fileInfo($fileCode) {
        $url = $this->baseUrl . "file/info?key=" . $this->apiKey . "&file_code=" . $fileCode;
        return $this->makeRequest($url);
    }

    public function remoteUpload($directLink, $fldId = null, $newTitle = null) {
        $url = $this->baseUrl . "upload/url?key=" . $this->apiKey . "&url=" . urlencode($directLink);
        if ($fldId !== null) $url .= "&fld_id=" . $fldId;
        if ($newTitle !== null) $url .= "&new_title=" . urlencode($newTitle);
        return $this->makeRequest($url);
    }

    public function createFolder($name, $parentId = null) {
        $url = $this->baseUrl . "folder/create?key=" . $this->apiKey . "&name=" . urlencode($name);
        if ($parentId !== null) $url .= "&parent_id=" . $parentId;
        return $this->makeRequest($url);
    }
}

// Proses form jika ada input
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $apiKey = trim($_POST['api_key']);
    $action = trim($_POST['action']);
    $doodStream = new DoodStream($apiKey);

    try {
        switch ($action) {
            case 'account_info':
                $result = $doodStream->accountInfo();
                $message = "<pre>" . print_r($result, true) . "</pre>";
                break;
            case 'list_files':
                $page = isset($_POST['page']) ? intval($_POST['page']) : null;
                $perPage = isset($_POST['per_page']) ? intval($_POST['per_page']) : null;
                $fldId = isset($_POST['fld_id']) ? trim($_POST['fld_id']) : null;
                $result = $doodStream->listFiles($page, $perPage, $fldId);
                $message = "<pre>" . print_r($result, true) . "</pre>";
                break;
            case 'file_info':
                $fileCode = trim($_POST['file_code']);
                $result = $doodStream->fileInfo($fileCode);
                $message = "<pre>" . print_r($result, true) . "</pre>";
                break;
            case 'remote_upload':
                $directLink = trim($_POST['direct_link']);
                $fldId = isset($_POST['fld_id']) ? trim($_POST['fld_id']) : null;
                $newTitle = isset($_POST['new_title']) ? trim($_POST['new_title']) : null;
                $result = $doodStream->remoteUpload($directLink, $fldId, $newTitle);
                $message = "<pre>" . print_r($result, true) . "</pre>";
                break;
            case 'create_folder':
                $name = trim($_POST['name']);
                $parentId = isset($_POST['parent_id']) ? trim($_POST['parent_id']) : null;
                $result = $doodStream->createFolder($name, $parentId);
                $message = "<pre>" . print_r($result, true) . "</pre>";
                break;
            default:
                $message = "Aksi tidak valid.";
                break;
        }
    } catch (Exception $e) {
        $message = "Error: " . htmlspecialchars($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doodstream API</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f9;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #007BFF;
        }
        form {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }
        button {
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .message {
            margin-top: 20px;
            padding: 10px;
            border-radius: 4px;
            font-size: 16px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Doodstream API</h1>
        <form method="POST">
            <label for="api_key">API Key:</label>
            <input type="text" name="api_key" id="api_key" required>

            <label for="action">Aksi:</label>
            <select name="action" id="action" required>
                <option value="account_info">Info Akun</option>
                <option value="list_files">Daftar File</option>
                <option value="file_info">Info File</option>
                <option value="remote_upload">Unggah Remote</option>
                <option value="create_folder">Buat Folder</option>
            </select>

            <div id="additionalFields"></div>

            <button type="submit">Submit</button>
        </form>

        <?php if (!empty($message)): ?>
            <div class="message">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        const actionSelect = document.getElementById('action');
        const additionalFields = document.getElementById('additionalFields');

        actionSelect.addEventListener('change', function() {
            let html = '';
            switch (this.value) {
                case 'list_files':
                    html = `
                        <label for="page">Halaman:</label>
                        <input type="number" name="page" id="page">
                        <label for="per_page">Per Halaman:</label>
                        <input type="number" name="per_page" id="per_page">
                        <label for="fld_id">Folder ID:</label>
                        <input type="text" name="fld_id" id="fld_id">
                    `;
                    break;
                case 'file_info':
                    html = `
                        <label for="file_code">File Code:</label>
                        <input type="text" name="file_code" id="file_code" required>
                    `;
                    break;
                case 'remote_upload':
                    html = `
                        <label for="direct_link">Direct Link:</label>
                        <input type="text" name="direct_link" id="direct_link" required>
                        <label for="fld_id">Folder ID:</label>
                        <input type="text" name="fld_id" id="fld_id">
                        <label for="new_title">Judul Baru:</label>
                        <input type="text" name="new_title" id="new_title">
                    `;
                    break;
                case 'create_folder':
                    html = `
                        <label for="name">Nama Folder:</label>
                        <input type="text" name="name" id="name" required>
                        <label for="parent_id">Parent Folder ID:</label>
                        <input type="text" name="parent_id" id="parent_id">
                    `;
                    break;
            }
            additionalFields.innerHTML = html;
        });
    </script>
</body>
</html>