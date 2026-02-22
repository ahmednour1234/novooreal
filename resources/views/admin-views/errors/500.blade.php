<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>خطأ 500 – Novoo</title>
  <!-- إضافة خط بهيج -->
  <style>
    @font-face {
      font-family: 'Bahij';
      src: url("{{ asset('public/assets/admin/css/fonts/Bahij_TheSansArabic-Plain.ttf') }}") format('truetype');
      font-weight: normal;
      font-style: normal;
    }
    :root {
      --primary: #001B63;
      --accent: #f8be1c;
      --bg: #ffffff;
      --text: #222222;
      --font: 'Bahij', sans-serif;
    }
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      font-family: var(--font);
      background: var(--bg);
      color: var(--text);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    /* Navbar */
    .navbar {
      background: var(--primary);
      height: 80px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 24px;
    }
    .navbar .logo {
      color: var(--accent);
      font-weight: 700;
      font-size: 2rem;
    }
    .navbar .links {
      display: flex;
      gap: 16px;
    }
    .navbar .links a {
      color: var(--accent);
      text-decoration: none;
      font-weight: 500;
      transition: opacity 0.3s;
    }
    .navbar .links a:hover {
      opacity: 0.8;
    }
    /* Main content */
    .main {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 20px;
      text-align: center;
    }
    .main img {
      width: 200px;
      max-width: 80%;
      margin-bottom: 24px;
    }
    .main h1 {
      font-size: 2.8rem;
      color: var(--primary);
      margin-bottom: 12px;
    }
    .main .subtitle {
      font-size: 1.1rem;
      line-height: 1.6;
      margin-bottom: 24px;
    }
    /* Loader */
    .loader {
      margin: 0 auto 24px;
      width: 60px;
      height: 60px;
      border: 6px solid #e0e0e0;
      border-top: 6px solid var(--primary);
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
    /* Footer */
    footer {
      text-align: center;
      padding: 16px 0;
      color: var(--text);
      font-size: 0.85rem;
      opacity: 0.6;
    }
    /* Responsive */
    @media (max-width: 400px) {
      .navbar {
        height: 70px;
        margin-bottom: 16px;
      }
      .navbar .logo {
        font-size: 1.7rem;
      }
      .main img {
        width: 160px;
      }
      .main h1 {
        font-size: 2.2rem;
      }
    }
  </style>
</head>
<body>
  <nav class="navbar">
    <div class="logo">Novoo</div>
    <div class="links">
      <a href="https://demo.novoosystem.com/admin">الرئيسية</a>
      <a href="https://novoosystem.com/">الدعم الفني</a>
      <a href="https://novoosystem.com/">التوثيق</a>
      <a href="https://novoosystem.com/">تواصل معنا</a>
    </div>
  </nav>

  <div class="main">
    <img src="{{ asset('public/impressed-young-blonde-female-engineer-wearing-uniform-safety-glasses-pointing-down_409827-642-removebg-preview.png') }}" alt="مبرمج كارتون">
    <h1>خطأ 500</h1>
    <p class="subtitle">عذرًا! حدث خلل داخلي في الخادم. فريق Novoo يعمل على إصلاحه سريعًا.</p>
    <div class="loader"></div>
    <p class="subtitle">شكرًا لصبركم—سنعود إليكم قريبًا!</p>
  </div>

  <footer>
    &copy; 2025 Novoo. جميع الحقوق محفوظة.
  </footer>
</body>
</html>
