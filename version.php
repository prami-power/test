<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Developer Change Log - Entry</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f3f4f6;
      padding: 20px;
    }
    .container {
      max-width: 700px;
      margin: auto;
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    h1 {
      text-align: center;
      color: #111827;
    }
    input, textarea, button {
      width: 100%;
      margin-top: 10px;
      margin-bottom: 20px;
      padding: 10px;
      font-size: 1rem;
      border-radius: 5px;
      border: 1px solid #ccc;
    }
    button {
      background-color: #2563eb;
      color: white;
      font-weight: bold;
      cursor: pointer;
    }
    button:hover {
      background-color: #1e40af;
    }
    .link {
      display: block;
      margin-top: 20px;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Add Change Log Entry</h1>
    <form id="logForm">
      <input type="text" id="website" placeholder="Website/Project Name" required />
      <input type="text" id="version" placeholder="Version (e.g., v1.2.3)" required />
      <textarea id="changes" placeholder="Describe changes made..." rows="4" required></textarea>
      <button type="submit">Save Entry</button>
    </form>
    <div class="link">
      <a href="log.html">View Change Log</a>
    </div>
  </div>

  <script>
    const form = document.getElementById('logForm');
    form.addEventListener('submit', function(e) {
      e.preventDefault();

      const website = document.getElementById('website').value.trim();
      const version = document.getElementById('version').value.trim();
      const changes = document.getElementById('changes').value.trim();
      const date = new Date().toLocaleString();

      const entry = { website, version, changes, date };

      const logs = JSON.parse(localStorage.getItem('changeLogs')) || [];
      logs.push(entry);
      localStorage.setItem('changeLogs', JSON.stringify(logs));

      alert('Log saved successfully!');
      form.reset();
    });
  </script>
</body>
</html>
