<?php
require_once __DIR__ . '/includes/db.php';

// Fetch recent testimonials from database
$query = "SELECT r.comment, u.fullname FROM reviews r JOIN users u ON r.user_id = u.user_id ORDER BY r.created_at DESC LIMIT 3";
$result = mysqli_query($conn, $query);

$testimonials = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $testimonials[] = $row;
    }
} else {
    // Default testimonials if db reviews table is empty
    $testimonials = [
        ['comment' => "My son used to cry when doing math homework. Now he asks to play DysCover Nexus every day!", 'fullname' => "Sarah M."],
        ['comment' => "The visual representation of numbers in the 3D space finally made things click for my daughter.", 'fullname' => "David K."],
        ['comment' => "A perfect blend of engaging gameplay and solid educational foundation. Highly recommended.", 'fullname' => "Elena R."]
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DysCover Nexus - 3D Educational Game Platform</title>
    <meta name="description" content="Play Smarter, Learn Better. A gamified 3D educational game designed to help children with dyscalculia learning challenges.">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hero {
            padding: 8rem 0;
            text-align: center;
            position: relative;
        }
        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            text-transform: uppercase;
        }
        .hero p {
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto 2.5rem;
            color: var(--text-muted);
        }
        .hero-banner {
            width: 100%;
            max-width: 900px;
            margin: 3rem auto 0;
            border-radius: 20px;
            overflow: hidden;
            border: 2px solid var(--primary);
            box-shadow: 0 0 30px rgba(0, 210, 255, 0.3);
        }
        .hero-banner img {
            width: 100%;
            height: auto;
            display: block;
        }
        .stats-section {
            background: rgba(0,0,0,0.3);
            padding: 4rem 0;
            margin-top: -5rem;
            position: relative;
            z-index: 2;
        }
        .stat-item {
            text-align: center;
        }
        .stat-number {
            font-size: 2.5rem;
            font-family: var(--font-heading);
            color: var(--primary);
            font-weight: 700;
        }
        .featured-game {
            padding: 6rem 0;
        }
        .game-card {
            display: flex;
            gap: 2rem;
            align-items: center;
            padding: 2rem;
        }
        .game-card img {
            width: 50%;
            border-radius: 12px;
        }
        .game-info h2 {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        @media (max-width: 768px) {
            .hero h1 { font-size: 2rem; }
            .game-card { flex-direction: column; }
            .game-card img { width: 100%; }
        }
    </style>
</head>
<body>

    <?php include 'includes/header.php'; ?>

    <main>
        <!-- Hero Section -->
        <section class="hero">
            <div class="container">
                <h1 class="gradient-text">Play Smarter, Learn Better</h1>
                <p>Unlock the power of numbers through immersive 3D gaming. Designed specifically for children with dyscalculia learning challenges.</p>
                <div>
                    <a href="pages/product.php" class="btn btn-primary" style="margin-right: 1rem;">Explore Game</a>
                    <a href="pages/about.php" class="btn btn-secondary">How it Works</a>
                </div>
                
                <div class="hero-banner">
                    <img src="https://images.unsplash.com/photo-1542751371-adc38448a05e?q=80&w=2070&auto=format&fit=crop" alt="DysCover Nexus Game Preview">
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="stats-section glass-panel">
            <div class="container grid-3">
                <div class="stat-item">
                    <div class="stat-number">95%</div>
                    <div>Positive Feedback</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">10k+</div>
                    <div>Active Learners</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">50+</div>
                    <div>Interactive Levels</div>
                </div>
            </div>
        </section>

        <!-- Featured Section -->
        <section class="featured-game container">
            <div class="glass-panel game-card">
                <img src="https://images.unsplash.com/photo-1552820728-8b83bb6b773f?q=80&w=2070&auto=format&fit=crop" alt="Game Graphics">
                <div class="game-info">
                    <h2 class="gradient-text">DysCover Nexus: Core Edition</h2>
                    <p style="margin-bottom: 1.5rem; color: var(--text-muted);">Dive into a sci-fi universe where math is your weapon. Solve puzzles, defeat enemies, and master fundamental arithmetic in a stress-free, engaging environment tailored for dyscalculia.</p>
                    <ul style="margin-bottom: 1.5rem; line-height: 2;">
                        <li><i class="fa-solid fa-check" style="color: var(--primary);"></i> Adaptive difficulty scaling</li>
                        <li><i class="fa-solid fa-check" style="color: var(--primary);"></i> Visual-spatial learning mechanics</li>
                        <li><i class="fa-solid fa-check" style="color: var(--primary);"></i> Progress tracking for parents</li>
                    </ul>
                    <a href="pages/product.php" class="btn btn-primary">View in Store</a>
                </div>
            </div>
        </section>

        <!-- Testimonials (Dynamic reviews from database) -->
        <section class="container section-padding">
            <h2 class="text-center gradient-text" style="margin-bottom: 3rem;">What Parents Say</h2>
            <div class="grid-3">
                <?php foreach ($testimonials as $t): ?>
                    <div class="glass-panel" style="padding: 2rem;">
                        <p style="font-style: italic; margin-bottom: 1rem;">"<?php echo htmlspecialchars($t['comment']); ?>"</p>
                        <h4 style="color: var(--primary);">- <?php echo htmlspecialchars($t['fullname']); ?></h4>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="js/main.js"></script>
</body>
</html>
