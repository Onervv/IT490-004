<?php
/**
 * Dashboard - Protected page that validates sessions via RabbitMQ
 */
$activePage = 'dashboard';

// Handle AJAX validation request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'validate') {
    header('Content-Type: application/json');
    
    $sessionKey = $_POST['session_key'] ?? '';
    
    if (empty($sessionKey)) {
        echo json_encode(['status' => 'error', 'message' => 'no session key']);
        exit(0);
    }
    
    // Include RabbitMQ client
    require_once __DIR__ . '/../includes/path.inc';
    require_once __DIR__ . '/../includes/get_host_info.inc';
    require_once __DIR__ . '/../includes/rabbitMQLib.inc';
    
    try {
        $client = new rabbitMQClient(__DIR__ . '/../config/testRabbitMQ.ini', 'testServer2');
        $request = [
            'type' => 'validate_session',
            'session_key' => $sessionKey
        ];
        $response = $client->send_request($request);
        echo json_encode($response);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'validation failed']);
    }
    exit(0);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link rel="stylesheet" href="assets/css/site.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/dashboard_nav.php'; ?>

    <div id="loading" class="container mt-5">
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Validating session...</p>
        </div>
    </div>
    
    <div id="dashboard" class="container mt-5" style="display: none;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Welcome, <span id="usernameDisplay"></span></h1>
        </div>
        
        <div class="alert alert-success">
            <strong>Session Valid</strong> - You are authenticated.
        </div>

        <!-- Stats overview row -->
        <div class="row g-3 mb-4" id="statsRow">
            <div class="col-6 col-md-4">
                <div class="card border-0 shadow-sm text-center p-3" style="background:#1a1a2e;color:#fff">
                    <div class="fs-2 fw-bold" id="statFavCount">0</div>
                    <div class="small text-uppercase" style="opacity:.7">Favorites</div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="card border-0 shadow-sm text-center p-3" style="background:#533483;color:#fff">
                    <div class="fs-2 fw-bold" id="statTotalListeners">0</div>
                    <div class="small text-uppercase" style="opacity:.7">Total Listeners</div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="card border-0 shadow-sm text-center p-3" style="background:#1b4332;color:#fff">
                    <div class="fs-2 fw-bold" id="statTotalPlays">0</div>
                    <div class="small text-uppercase" style="opacity:.7">Total Plays</div>
                </div>
            </div>
        </div>

        <!-- Charts row -->
        <div class="row g-4 mb-4" id="chartsRow">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-title fw-bold mb-3">Listeners by Favorite Artist</h6>
                        <div style="position:relative;height:280px">
                            <canvas id="listenersChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-title fw-bold mb-3">Play Count by Favorite Artist</h6>
                        <div style="position:relative;height:280px">
                            <canvas id="playsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-title fw-bold mb-3">Listeners vs Plays (Scatter)</h6>
                        <div style="position:relative;height:280px">
                            <canvas id="scatterChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h6 class="card-title fw-bold mb-3">Your Top 10 Most-Played Artists</h6>
                        <div style="position:relative;height:280px">
                            <canvas id="topArtistsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty state -->
        <div id="noFavsMsg" class="alert alert-info" style="display:none">
            Star some artists on the <a href="explore_page.php">Explore</a> page to see your preference charts here!
        </div>
    </div>

    <!-- Chart.js from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script src="js/shared.js"></script>

    <script>
        (function() {
            const sessionKey = sessionStorage.getItem('session_key');
            const username = sessionStorage.getItem('username');
            
            if (!sessionKey) {
                window.location.href = 'login_page.php';
                return;
            }
            
            // Validate session with backend
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 10000);
            
            const data = new URLSearchParams({
                action: 'validate',
                session_key: sessionKey
            });
            
            fetch('dashboard_page.php', {
                method: 'POST',
                body: data,
                signal: controller.signal
            })
            .then(response => response.json())
            .then(result => {
                clearTimeout(timeout);
                if (result.status === 'ok') {
                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('dashboard').style.display = 'block';
                    document.getElementById('usernameDisplay').textContent = result.username || username || 'User';
                    buildDashboardCharts();
                } else {
                    sessionStorage.removeItem('session_key');
                    sessionStorage.removeItem('username');
                    window.location.href = 'login_page.php';
                }
            })
            .catch((err) => {
                clearTimeout(timeout);
                sessionStorage.removeItem('session_key');
                sessionStorage.removeItem('username');
                window.location.href = 'login_page.php';
            });
            
            // Logout handler
            document.getElementById('logoutBtn').addEventListener('click', function(e) {
                e.preventDefault();
                sessionStorage.removeItem('session_key');
                sessionStorage.removeItem('username');
                window.location.href = 'login_page.php';
            });

            /* ── Dashboard Charts ──────────────────────────── */
            function buildDashboardCharts() {
                const liked   = M3.getLikedItems();
                const items   = Object.values(liked);

                // Stats cards
                const favCount    = items.length;
                const totalListen = items.reduce((s, i) => s + parseInt(i.listeners || 0, 10), 0);
                const totalPlays  = items.reduce((s, i) => s + parseInt(i.play_count || 0, 10), 0);

                document.getElementById('statFavCount').textContent      = favCount;
                document.getElementById('statTotalListeners').textContent = M3.formatNumber(totalListen);
                document.getElementById('statTotalPlays').textContent     = M3.formatNumber(totalPlays);

                if (!favCount) {
                    document.getElementById('chartsRow').style.display = 'none';
                    document.getElementById('noFavsMsg').style.display = 'block';
                    return;
                }

                // Sort by listeners desc, take top 10
                const sorted = [...items].sort((a, b) => parseInt(b.listeners||0) - parseInt(a.listeners||0));
                const top    = sorted.slice(0, 10);
                const labels = top.map(i => i.name || 'Unknown');

                // Brite-themed palette
                const COLORS = [
                    '#198754','#0d6efd','#6f42c1','#fd7e14','#dc3545',
                    '#20c997','#0dcaf0','#ffc107','#6610f2','#d63384'
                ];

                const bgColors    = top.map((_, i) => COLORS[i % COLORS.length]);
                const bgAlpha     = top.map((_, i) => COLORS[i % COLORS.length] + '99');

                const baseOpts = {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } }
                };

                // 1. Listeners bar chart
                new Chart(document.getElementById('listenersChart'), {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Listeners',
                            data: top.map(i => parseInt(i.listeners || 0)),
                            backgroundColor: bgAlpha,
                            borderColor: bgColors,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        ...baseOpts,
                        scales: {
                            y: { beginAtZero: true, ticks: { callback: v => M3.formatNumber(v) } },
                            x: { ticks: { maxRotation: 45, minRotation: 30, font: { size: 10 } } }
                        }
                    }
                });

                // 2. Plays doughnut chart
                new Chart(document.getElementById('playsChart'), {
                    type: 'doughnut',
                    data: {
                        labels,
                        datasets: [{
                            data: top.map(i => parseInt(i.play_count || 0)),
                            backgroundColor: bgColors,
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        ...baseOpts,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'right',
                                labels: { boxWidth: 12, font: { size: 10 } }
                            }
                        }
                    }
                });

                // 3. Scatter: listeners vs plays
                new Chart(document.getElementById('scatterChart'), {
                    type: 'scatter',
                    data: {
                        datasets: [{
                            label: 'Artists',
                            data: items.map(i => ({
                                x: parseInt(i.listeners || 0),
                                y: parseInt(i.play_count || 0),
                                name: i.name
                            })),
                            backgroundColor: '#19875499',
                            borderColor: '#198754',
                            borderWidth: 1,
                            pointRadius: 6,
                            pointHoverRadius: 9
                        }]
                    },
                    options: {
                        ...baseOpts,
                        scales: {
                            x: { title: { display: true, text: 'Listeners' }, ticks: { callback: v => M3.formatNumber(v) } },
                            y: { title: { display: true, text: 'Play Count' }, ticks: { callback: v => M3.formatNumber(v) } }
                        },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: ctx => `${ctx.raw.name}: ${M3.formatNumber(ctx.raw.x)} listeners, ${M3.formatNumber(ctx.raw.y)} plays`
                                }
                            }
                        }
                    }
                });

                // 4. Top artists horizontal bar (by play count)
                const topPlays = [...items].sort((a, b) => parseInt(b.play_count||0) - parseInt(a.play_count||0)).slice(0, 10);
                new Chart(document.getElementById('topArtistsChart'), {
                    type: 'bar',
                    data: {
                        labels: topPlays.map(i => i.name || 'Unknown'),
                        datasets: [{
                            label: 'Play Count',
                            data: topPlays.map(i => parseInt(i.play_count || 0)),
                            backgroundColor: topPlays.map((_, i) => COLORS[i % COLORS.length] + '99'),
                            borderColor: topPlays.map((_, i) => COLORS[i % COLORS.length]),
                            borderWidth: 1
                        }]
                    },
                    options: {
                        ...baseOpts,
                        indexAxis: 'y',
                        scales: {
                            x: { beginAtZero: true, ticks: { callback: v => M3.formatNumber(v) } },
                            y: { ticks: { font: { size: 10 } } }
                        }
                    }
                });
            }
        })();
    </script>
</body>
</html>
