<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WHOIS Lookup</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; padding: 2rem; }
        input, button { padding: 0.5rem; margin: 0.5rem 0; }
        .result, .error, .loader { margin-top: 1rem; padding: 1rem; border: 1px solid #ccc; }
        .error { border-color: red; color: red; }
        .loader { border-color: #007bff; color: #007bff; }
        pre { white-space: pre-wrap; word-wrap: break-word; }
    </style>
</head>
<body>

<h1>WHOIS Lookup</h1>

<form id="whois-form">
    <label for="domain">Enter domain:</label><br>
    <input type="text" id="domain" name="domain" placeholder="example.com" required>
    <br>
    <button type="submit">Lookup</button>
</form>

<div id="loader" class="loader" style="display: none;">Loading...</div>
<div id="output" class="result" style="display: none;"></div>
<div id="error" class="error" style="display: none;"></div>

<script>
    document.getElementById('whois-form').addEventListener('submit', async function(e) {
        e.preventDefault();

        const domain = document.getElementById('domain').value.trim();
        const output = document.getElementById('output');
        const error = document.getElementById('error');
        const loader = document.getElementById('loader');

        // Reset state
        output.style.display = 'none';
        error.style.display = 'none';
        loader.style.display = 'block';

        if (!domain) {
            loader.style.display = 'none';
            error.textContent = 'Please enter a domain.';
            error.style.display = 'block';
            return;
        }

        try {
            const response = await fetch(`/api/whois?domain=${encodeURIComponent(domain)}`);
            const data = await response.json();
            loader.style.display = 'none';

            if (!response.ok || data.error) {
                error.textContent = data.error || 'WHOIS lookup failed.';
                error.style.display = 'block';
                return;
            }

            let html = `<strong>Domain:</strong> ${data.domain}<br>`;
            if (data.created_at) html += `<strong>Created at:</strong> ${data.created_at}<br>`;
            if (data.updated_at) html += `<strong>Updated at:</strong> ${data.updated_at}<br>`;
            if (data.expires_at) html += `<strong>Expires at:</strong> ${data.expires_at}<br>`;
            if (data.registrar) html += `<strong>Registrar:</strong> ${data.registrar}<br>`;
            if (data.dnssec) html += `<strong>DNSSEC:</strong> ${data.dnssec}<br>`;
            if (data.name_servers?.length)
                html += `<strong>Name servers:</strong> ${data.name_servers.join(', ')}<br>`;
            if (data.status?.length)
                html += `<strong>Status:</strong> ${data.status.join(', ')}<br>`;

            html += `<details><summary>Raw WHOIS</summary><pre>${data.whois_raw}</pre></details>`;

            output.innerHTML = html;
            output.style.display = 'block';
        } catch (e) {
            loader.style.display = 'none';
            error.textContent = 'Request failed: ' + e.message;
            error.style.display = 'block';
        }
    });
</script>

</body>
</html>
