<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Fetch all users
$stmt = $pdo->query('SELECT id, username, email, role, is_active, created_at FROM users ORDER BY id ASC');
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Users - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-heartbeat text-primary me-2"></i>
                HealthCare Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="all_users.php">All Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="users.php">User Management</a></li>
                    <li class="nav-item"><a class="nav-link" href="doctors.php">Doctors</a></li>
                    <li class="nav-item"><a class="nav-link" href="patients.php">Patients</a></li>
                    <li class="nav-item"><a class="nav-link" href="logs.php">Logs</a></li>
                    <li class="nav-item"><a class="nav-link btn btn-primary ms-2" href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <section class="py-5">
        <div class="container mt-4">
            <h2>All Users</h2>
            <!-- Export buttons -->
            <div class="mb-3 d-flex gap-2">
                <button class="btn btn-outline-success" id="exportCSV">Export to Excel (CSV)</button>
                <button class="btn btn-outline-danger" id="exportPDF">Export to PDF</button>
            </div>
            <!-- Search box -->
            <div class="mb-3">
                <input type="text" id="searchInput" class="form-control" placeholder="Search by any field...">
            </div>
            <table class="table table-bordered mt-3 table-custom" id="usersTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Registered</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['id'] ?? '') ?></td>
                        <td><?= htmlspecialchars($u['username'] ?? '') ?></td>
                        <td><?= htmlspecialchars($u['email'] ?? '') ?></td>
                        <td><?= htmlspecialchars($u['role'] ?? '') ?></td>
                        <td><?= $u['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>' ?></td>
                        <td><?= htmlspecialchars($u['created_at'] ?? '') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <a href="dashboard.php" class="btn btn-custom btn-secondary">Back to Dashboard</a>
        </div>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jsPDF and autoTable for PDF export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.7.0/jspdf.plugin.autotable.min.js"></script>
    <script>
    // Client-side search for users table
    const searchInput = document.getElementById('searchInput');
    const table = document.getElementById('usersTable');
    searchInput.addEventListener('keyup', function() {
        const filter = searchInput.value.toLowerCase();
        const rows = table.getElementsByTagName('tr');
        for (let i = 1; i < rows.length; i++) { // skip header
            const row = rows[i];
            let text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        }
    });
    // Export to CSV
    function downloadCSV(csv, filename) {
        const csvFile = new Blob([csv], {type: 'text/csv'});
        const downloadLink = document.createElement('a');
        downloadLink.download = filename;
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = 'none';
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    }
    function exportTableToCSV(filename) {
        const rows = table.querySelectorAll('tr');
        let csv = '';
        for (let i = 0; i < rows.length; i++) {
            if (rows[i].style.display === 'none') continue; // skip hidden rows
            let row = [], cols = rows[i].querySelectorAll('td, th');
            for (let j = 0; j < cols.length; j++)
                row.push('"' + cols[j].innerText.replace(/"/g, '""') + '"');
            csv += row.join(',') + '\n';
        }
        downloadCSV(csv, filename);
    }
    document.getElementById('exportCSV').addEventListener('click', function() {
        exportTableToCSV('users.csv');
    });
    // Export to PDF
    document.getElementById('exportPDF').addEventListener('click', function() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        doc.text('All Users', 14, 14);
        // Gather only visible rows
        const rows = Array.from(table.querySelectorAll('tbody tr')).filter(row => row.style.display !== 'none');
        const data = rows.map(row => Array.from(row.children).map(cell => cell.innerText.replace(/\n/g, ' ')));
        // Get headers
        const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.innerText);
        doc.autoTable({
            head: [headers],
            body: data,
            startY: 20,
            styles: { fontSize: 8 },
            headStyles: { fillColor: [41, 14, 140] }
        });
        doc.save('users.pdf');
    });
    </script>
</body>
</html> 