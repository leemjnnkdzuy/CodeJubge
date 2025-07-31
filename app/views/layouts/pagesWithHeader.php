<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? htmlspecialchars($title) : 'CodeJudge' ?></title>
    <meta name="description" content="<?= isset($description) ? htmlspecialchars($description) : 'Practice coding problems with automatic evaluation' ?>">
    
    <link rel="stylesheet" href="/css/globalStyle.css">
    <link rel="stylesheet" href="/css/welcomeStyle.css">
    <link rel="stylesheet" href="/css/welcomeHeaderStyle.css">
    <link rel="stylesheet" href="/css/welcomeFooterStyle.css">

    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Dosis:wght@200..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter+Tight:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>    
    <style>
        body {
            font-family: 'Inter Tight', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        .welcome-header {
            background: white;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            height: 80px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .welcome-navbar {
            height: 100%;
            display: flex;
            align-items: center;
        }
        
        .welcome-navbar .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }
        
        .welcome-footer {
            background: #212529;
            color: white;
            padding: 3rem 0;
        }
    </style>
</head>
<body>
    <?php include VIEW_PATH . '/components/popupNotification.php'; ?>
    <?php include VIEW_PATH . '/components/welcomeHeader.php'; ?>

    <main style="height: calc(100vh - 80px); overflow-y: auto; margin-top: 80px; padding-top: 0;">
        <?= $content ?? '' ?>

        <?php include VIEW_PATH . '/components/welcomeFooter.php'; ?>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.problem-card, .stat-item, .language-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
            
            const popularityBars = document.querySelectorAll('.popularity-fill');
            popularityBars.forEach((bar, index) => {
                const width = bar.dataset.width || '0%';
                setTimeout(() => {
                    bar.style.width = width;
                }, 500 + index * 200);
            });
        });
        
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
