<?php
/* ===============================================================
   AVÉA BEAUTY — GEO RESTRICTION (Philippines only)
   ---------------------------------------------------------------
   READY TO COPY-PASTE.

   I-paste ang BUONG block na ito sa PINAKATAAS ng index.php —
   ang "<?php" sa itaas ay dapat ang UNANG-UNANG karakter ng file.
   Walang espasyo, walang blankong linya, walang HTML bago nito,
   kung hindi ay "headers already sent" ang error.

   Kung may sarili nang "<?php" ang index.php mo, tanggalin mo ang
   pangalawang "<?php" at pagsamahin na lang sa iisang block.

   Gawin din ito sa apply.php at confirm.php — kung index.php lang
   ang protektado, puwedeng i-direct access ng iba ang apply.php.
=============================================================== */

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['path' => '/']);
    session_start();
}

$geoAllowedCountries = ['PH'];
$geoCacheHours       = 6;

function geo_visitor_ip() {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) return $_SERVER['HTTP_CF_CONNECTING_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
    return $_SERVER['REMOTE_ADDR'] ?? '';
}

function geo_country_code($ip) {
    global $geoCacheHours;

    /* 1. Cloudflare — pinakamabilis, walang API call.
          I-enable sa dashboard: Rules -> Settings -> IP Geolocation */
    if (!empty($_SERVER['HTTP_CF_IPCOUNTRY']) && $_SERVER['HTTP_CF_IPCOUNTRY'] !== 'XX') {
        return strtoupper($_SERVER['HTTP_CF_IPCOUNTRY']);
    }

    /* 2. Private/local IP — development, palaging payagan */
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
        return 'PH';
    }

    /* 3. Session cache — may expiry at naka-tie sa IP, kaya kapag
          nagpalit ng network ang user, muling chine-check. */
    if (
        isset($_SESSION['geo_country'], $_SESSION['geo_ip'], $_SESSION['geo_time']) &&
        $_SESSION['geo_ip'] === $ip &&
        (time() - $_SESSION['geo_time']) < ($geoCacheHours * 3600)
    ) {
        return $_SESSION['geo_country'];
    }

    /* 4. Lookup */
    $ch = curl_init("http://ip-api.com/json/" . urlencode($ip) . "?fields=status,countryCode");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode((string)$response, true);
    $code = (isset($data['status']) && $data['status'] === 'success')
        ? ($data['countryCode'] ?? null)
        : null;

    /* FAIL-OPEN: kapag down o timeout ang API, payagan — mas mabuti
       nang makapasok ang iilang dayuhan kaysa ma-block ang lahat ng
       lehitimong Pinoy applicant dahil sa isang outage.
       Gawing 'XX' ito kung fail-CLOSED ang gusto mo. */
    if (!$code) return 'PH';

    $_SESSION['geo_country'] = $code;
    $_SESSION['geo_ip']      = $ip;
    $_SESSION['geo_time']    = time();

    return $code;
}

$geoCountry = geo_country_code(geo_visitor_ip());

if (!in_array($geoCountry, $geoAllowedCountries, true)) {

    http_response_code(403);
    header('Content-Type: text/html; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');

    /* Nowdoc (<<<'HTML') — walang PHP parsing sa loob, kaya ligtas
       ang lahat ng CSS braces at quotes. Ang HTML; sa dulo ay dapat
       nasa simula mismo ng linya, walang espasyo sa unahan. */
    echo <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Not Available in Your Region | AVÉA Beauty</title>
<meta name="robots" content="noindex">
<meta name="theme-color" content="#FBF7F3">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400&family=Jost:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="icon" href="files/images/avealogo.png" type="image/png">
<style>
  :root{
    --porcelain:#FBF7F3; --sand:#F1EAE1; --hairline:#E4DACE;
    --gold:#B08D57; --gold-light:#E9CFA8;
    --rose:#8E3A4B; --rose-deep:#6C2A38;
    --ink:#1E1A17; --muted:#7B6E64;
    --ff-display:'Cormorant Garamond', serif;
    --ff-body:'Jost', sans-serif;
  }
  *{margin:0;padding:0;box-sizing:border-box;}
  body{
    font-family:var(--ff-body); background:var(--porcelain); color:var(--ink);
    min-height:100vh; display:flex; align-items:center; justify-content:center;
    padding:32px 20px; line-height:1.7; -webkit-font-smoothing:antialiased;
    background-image:
      radial-gradient(circle at 12% 18%, rgba(233,207,168,.28), transparent 42%),
      radial-gradient(circle at 88% 82%, rgba(239,221,225,.42), transparent 45%);
  }
  .card{
    position:relative; max-width:560px; width:100%; text-align:center;
    background:rgba(255,255,255,.7); backdrop-filter:blur(8px);
    border:1px solid var(--hairline); padding:clamp(40px,7vw,68px) clamp(26px,5vw,54px);
    animation:rise .9s cubic-bezier(.22,.8,.28,1) both;
  }
  /* inset gold frame — same signature as the apply page */
  .card::after{
    content:""; position:absolute; inset:10px; pointer-events:none;
    border:1px solid rgba(176,141,87,.28);
  }
  @keyframes rise{from{opacity:0;transform:translateY(18px);}to{opacity:1;transform:none;}}
  @media (prefers-reduced-motion:reduce){ .card{animation:none;} }

  .logo{
    font-family:var(--ff-display); font-size:1.7rem; letter-spacing:.26em;
    color:var(--ink); margin-bottom:34px;
  }
  .logo span{color:var(--gold); font-style:italic;}

  .kicker{
    display:inline-flex; align-items:center; gap:12px;
    font-size:.68rem; font-weight:500; letter-spacing:.32em;
    text-transform:uppercase; color:var(--gold); margin-bottom:20px;
  }
  .kicker::before,.kicker::after{content:""; height:1px; width:22px; background:var(--gold); opacity:.55;}

  h1{
    font-family:var(--ff-display); font-weight:500;
    font-size:clamp(1.9rem,5.2vw,2.6rem); line-height:1.2; margin-bottom:18px;
  }
  h1 em{font-style:italic; color:var(--rose);}

  p{color:var(--muted); font-weight:300; font-size:.97rem; max-width:42ch; margin:0 auto;}

  .rule{height:1px; background:var(--hairline); margin:32px auto; max-width:80px;}

  .note{
    font-size:.82rem; color:var(--muted);
    background:var(--sand); border-left:2px solid var(--gold-light);
    padding:14px 18px; text-align:left; margin-top:8px;
  }

  .contact{margin-top:30px; font-size:.72rem; letter-spacing:.2em; text-transform:uppercase;}
  .contact a{
    color:var(--rose); text-decoration:none;
    border-bottom:1px solid var(--gold); padding-bottom:3px;
    transition:letter-spacing .25s ease, color .25s ease;
  }
  .contact a:hover,.contact a:focus-visible{letter-spacing:.26em; color:var(--rose-deep);}
  a:focus-visible{outline:2px solid var(--gold); outline-offset:4px;}
</style>
</head>
<body>
  <main class="card">
    <div class="logo">AV<span>É</span>A</div>

    <span class="kicker">Region Notice</span>

    <h1>Our creator programme is <em>Philippines-only</em></h1>

    <p>
      AVÉA Beauty ships products and runs campaigns within the Philippines,
      so applications are open to creators based there.
    </p>

    <div class="rule"></div>

    <p class="note">
      Nasa Pilipinas ka pero nakikita mo ito? Malamang naka-VPN ka o proxy ang
      network mo. I-off ito, tapos i-refresh ang page.
    </p>

    <p class="contact">
      <a href="mailto:hello@aveabeauty.com">Email us</a>
    </p>
  </main>
</body>
</html>
HTML;

    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AVÉA Beauty — Create With Us</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="icon" type="image/png" href="files/images/avealogo.png">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  :root{
    /* neutral base — quieter, more premium than pure rose */
    --porcelain:#FBF7F3;      /* page background — warm off-white */
    --sand:#F1EAE1;           /* soft alternating panel */
    --linen:#E7DDD1;           /* deeper neutral card / band */
    --hairline:#E4DACE;        /* borders, dividers */
    /* accents — refined */
    --gold:#B08D57;            /* champagne / brass — primary accent */
    --gold-deep:#8C6C3E;       /* darker gold for hover / links */
    --rose:#8E3A4B;            /* bordeaux — used sparingly for emphasis */
    --rose-deep:#6C2A38;       /* deeper bordeaux */
    --rosewood:#B99098;        /* dusty pink — light editorial touches */
    --blush:#EFDDE1;           /* faint blush wash */
    --ink:#1E1A17;             /* near-black text — warm, not cold */
    --muted:#7B6E64;           /* body-text secondary — warmer than before */
    --ff-display:'Cormorant Garamond', serif;
    --ff-body:'Jost', sans-serif;
  }
  *{margin:0;padding:0;box-sizing:border-box;}
  html{scroll-behavior:smooth;}
  body{font-family:var(--ff-body);background:var(--porcelain);color:var(--ink);line-height:1.7;font-size:16px;font-weight:400;}
  img{max-width:100%;display:block;}
  a{text-decoration:none;color:inherit;}
  button{font-family:var(--ff-body);}

  /* ---------- Type utilities ---------- */
  .kicker{
    font-size:.72rem;font-weight:500;letter-spacing:.32em;text-transform:uppercase;color:var(--gold);
    display:inline-flex;align-items:center;gap:14px;
  }
  .kicker::before,.kicker::after{content:"";height:1px;width:28px;background:var(--gold);opacity:.6;}
  .kicker.left::before{display:none;}
  h1,h2,h3{font-family:var(--ff-display);font-weight:500;}

  /* ---------- Buttons ---------- */
  .btn{
    display:inline-block;padding:15px 40px;
    font-weight:500;font-size:.78rem;letter-spacing:.24em;text-transform:uppercase;
    transition:all .3s ease;cursor:pointer;border:1px solid transparent;
  }
  .btn-primary{background:var(--rose);color:#fff;}
  .btn-primary:hover{background:var(--rose-deep);letter-spacing:.3em;}
  .btn-outline{background:transparent;color:var(--rose);border-color:var(--rose);}
  .btn-outline:hover{background:var(--rose);color:#fff;}
  .btn-light{background:transparent;color:#fff;border-color:rgba(255,255,255,.7);}
  .btn-light:hover{background:#fff;color:var(--rose);}
  .btn-white{background:#fff;color:var(--rose);}
  .btn-white:hover{background:var(--blush);letter-spacing:.3em;}
  .btn:focus-visible{outline:2px solid var(--gold);outline-offset:3px;}
  .text-link{
    font-size:.78rem;letter-spacing:.22em;text-transform:uppercase;color:var(--rose);
    border-bottom:1px solid var(--gold);padding-bottom:4px;transition:all .25s;
  }
  .text-link:hover{letter-spacing:.28em;color:var(--rose-deep);}

  /* ---------- Header ---------- */
  .top-bar{
    background:var(--rose);color:#F8E4E9;text-align:center;
    font-size:.7rem;letter-spacing:.26em;text-transform:uppercase;padding:9px 16px;
  }
  header{position:sticky;top:0;z-index:200;background:rgba(251,247,243,.94);backdrop-filter:blur(12px);border-bottom:1px solid var(--hairline);}
  .nav{max-width:1280px;margin:0 auto;display:grid;grid-template-columns:1fr auto 1fr;align-items:center;padding:20px 28px;gap:20px;}
  .logo{font-family:var(--ff-display);font-size:1.75rem;font-weight:500;letter-spacing:.26em;color:var(--ink);justify-self:start;}
  .logo span{color:var(--gold);font-style:italic;}
  .nav-links{display:flex;gap:36px;align-items:center;list-style:none;justify-self:center;}
  .nav-links a{font-size:.76rem;font-weight:500;letter-spacing:.2em;text-transform:uppercase;color:var(--muted);transition:color .25s;position:relative;padding:6px 0;}
  .nav-links a:hover{color:var(--gold-deep);}
  .nav-links a::after{content:"";position:absolute;left:0;right:0;bottom:0;height:1px;background:var(--gold);transform:scaleX(0);transform-origin:center;transition:transform .3s ease;}
  .nav-links a:hover::after{transform:scaleX(1);}
  .nav-apply{justify-self:end;}
  .nav-apply .btn{padding:11px 26px;}
  .menu-toggle{display:none;background:none;border:none;font-size:1.5rem;color:var(--ink);cursor:pointer;}

  /* ==========================================================
     HERO — SPLIT SLIDER
     Bawat slide ay hati sa dalawa: text sa kaliwa, video/image
     sa kanan (object-fit:cover, buong height, walang crop-padding
     kaya nakikita ng maayos ang larawan/video).
  ========================================================== */
  .hero-slider{position:relative;overflow:hidden;background:var(--ink);}
  .slides{display:flex;transition:transform .7s cubic-bezier(.6,.05,.2,1);}
  .slide{
    min-width:100%;display:grid;grid-template-columns:1fr 1fr;align-items:center;gap:56px;
    min-height:min(78vh,640px);padding:64px clamp(28px,5vw,80px);
  }
  .hero-media{height:min(64vh,520px);position:relative;overflow:hidden;background:#000;}
  .hero-media img,.hero-media video{
    width:100%;height:100%;object-fit:cover;object-position:center;display:block;
  }
  .hero-text{color:#fff;position:relative;z-index:2;}
  .hero-text .kicker{color:var(--gold);}
  .hero-text .kicker::before,.hero-text .kicker::after{background:var(--gold);}
  .hero-text h1{font-family:var(--ff-display);font-weight:500;font-size:clamp(2.3rem,4.2vw,3.5rem);line-height:1.1;margin:20px 0 18px;color:#fff;}
  .hero-text h1 em{font-style:italic;color:var(--blush);}
  .hero-text p{color:rgba(255,255,255,.72);font-weight:300;max-width:440px;margin-bottom:30px;font-size:1rem;}
  .hero-text p strong{color:#fff;font-weight:600;}

  /* Slider controls (bottom-center) */
  .hero-controls{
    position:absolute;bottom:28px;left:50%;transform:translateX(-50%);z-index:10;
    display:flex;align-items:center;gap:20px;
  }
  .slider-nav{
    width:48px;height:48px;border-radius:50%;border:1px solid rgba(255,255,255,.6);cursor:pointer;
    background:rgba(36,26,30,.35);backdrop-filter:blur(6px);color:#fff;font-size:1.05rem;
    display:flex;align-items:center;justify-content:center;transition:all .3s;
  }
  .slider-nav:hover{background:#fff;color:var(--rose);border-color:#fff;}
  .slider-dots{display:flex;gap:10px;}
  .dot{width:30px;height:3px;background:rgba(255,255,255,.45);border:none;cursor:pointer;transition:background .3s;box-shadow:0 0 0 1px rgba(0,0,0,.15);}
  .dot.active{background:#fff;}

  /* ---------- Section shell ---------- */
  section{padding:110px 28px;}
  .wrap{max-width:1280px;margin:0 auto;}
  .section-head{max-width:680px;margin:0 auto 64px;text-align:center;}
  .section-head h2{font-size:clamp(2.1rem,3.8vw,3.1rem);line-height:1.12;margin:20px 0 16px;letter-spacing:.01em;}
  .section-head h2 em{font-style:italic;color:var(--rose);}
  .section-head p{color:var(--muted);font-weight:300;}

  /* ---------- Why Choose Us (5 reasons) ---------- */
  .reasons{background:var(--porcelain);padding-bottom:40px;}
  .reasons-grid{
    display:grid;grid-template-columns:repeat(6,1fr);gap:26px;max-width:1080px;margin:0 auto;
  }
  .reason-card{
    grid-column:span 2;
    background:var(--sand);border:1px solid var(--hairline);padding:34px 30px 30px;
    transition:transform .3s ease, box-shadow .3s ease, border-color .3s ease;
  }
  .reason-card:nth-child(4){grid-column:2 / span 2;}
  .reason-card:nth-child(5){grid-column:4 / span 2;}
  .reason-card:hover{transform:translateY(-4px);border-color:var(--rosewood);box-shadow:0 18px 40px -22px rgba(124,51,69,.35);}
  .reason-icon{
    width:52px;height:52px;border-radius:50%;background:var(--porcelain);border:1px solid var(--hairline);
    display:flex;align-items:center;justify-content:center;margin-bottom:22px;color:var(--gold);
  }
  .reason-icon svg{width:22px;height:22px;stroke:var(--gold);fill:none;stroke-width:1.4;}
  .reason-card h3{font-size:1.25rem;margin-bottom:10px;letter-spacing:.01em;color:var(--ink);}
  .reason-card p{font-size:.9rem;color:var(--muted);font-weight:300;line-height:1.65;}
  @media(max-width:820px){
    .reasons-grid{grid-template-columns:1fr 1fr;}
    .reason-card,.reason-card:nth-child(4),.reason-card:nth-child(5){grid-column:auto;}
  }
  @media(max-width:560px){
    .reasons-grid{grid-template-columns:1fr;}
  }

  /* ---------- Category tabs ---------- */
  .cat-tabs{display:flex;gap:0;flex-wrap:wrap;justify-content:center;margin-bottom:56px;border-bottom:1px solid var(--hairline);}
  .cat-tab{
    padding:14px 26px;border:none;background:none;color:var(--muted);
    font-weight:500;font-size:.76rem;letter-spacing:.2em;text-transform:uppercase;cursor:pointer;
    position:relative;transition:color .25s;
  }
  .cat-tab::after{
    content:"";position:absolute;left:26px;right:26px;bottom:-1px;height:2px;
    background:var(--rose);transform:scaleX(0);transition:transform .3s ease;
  }
  .cat-tab:hover{color:var(--rose);}
  .cat-tab.active{color:var(--rose);}
  .cat-tab.active::after{transform:scaleX(1);}

  /* ---------- Show more / show less ---------- */
  .show-more-wrap{text-align:center;margin-top:52px;}

  /* ---------- Product cards (hover = 2nd image) ---------- */
  .product-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:40px 26px;}
  .product-card{cursor:pointer;position:relative;}
  .product-img{
    height:310px;position:relative;overflow:hidden;background:#fff;
    border:1px solid var(--hairline);
  }
  .img-fallback{position:absolute;inset:0;}
  .product-img img{
    position:absolute;inset:0;width:100%;height:100%;object-fit:contain;padding:18px;
    transition:opacity .45s ease, transform .6s ease;background:#fff;
  }
  .product-img .img-hover{opacity:0;}
  .product-card:hover .img-hover{opacity:1;}
  .product-card:hover .img-main{transform:scale(1.03);}
  .badge{
    position:absolute;top:0;left:0;z-index:5;background:var(--rose);color:#fff;
    font-size:.6rem;font-weight:500;letter-spacing:.2em;text-transform:uppercase;padding:7px 16px;
  }
  .hover-veil{
    position:absolute;left:0;right:0;bottom:0;z-index:4;
    background:rgba(255,255,255,.96);text-align:center;
    padding:13px 0;transform:translateY(100%);transition:transform .35s ease;
  }
  .product-card:hover .hover-veil{transform:translateY(0);}
  .hover-veil span{font-size:.7rem;letter-spacing:.24em;text-transform:uppercase;color:var(--rose);border-bottom:1px solid var(--gold);padding-bottom:3px;}
  .product-info{padding:18px 4px 0;text-align:center;}
  .product-info .cat{font-size:.64rem;letter-spacing:.26em;text-transform:uppercase;color:var(--gold);margin-bottom:8px;}
  .product-info h3{font-size:1.35rem;margin-bottom:6px;letter-spacing:.02em;line-height:1.25;}
  .product-info .desc{font-size:.85rem;color:var(--muted);font-weight:300;margin-bottom:12px;min-height:44px;}
  .price-row{display:flex;align-items:center;justify-content:center;gap:12px;}
  .price{font-size:.98rem;letter-spacing:.06em;color:var(--rose);font-weight:500;}
  .price-old{font-size:.82rem;color:#BCA6AC;text-decoration:line-through;font-weight:300;}
  .stars{color:var(--gold);font-size:.78rem;letter-spacing:3px;}
  .rating-count{font-size:.72rem;color:var(--muted);font-weight:300;}

  /* placeholder gradients (fallback kapag walang image file) */
  .g1{background:linear-gradient(150deg,#E4D2C4,#B08D57);}
  .g2{background:linear-gradient(150deg,#E9DDC8,#8C6C3E);}
  .g3{background:linear-gradient(150deg,#DBC7BC,#8E3A4B);}
  .g4{background:linear-gradient(150deg,#EBC5B6,#C08A75);}
  .g5{background:linear-gradient(150deg,#C4CDDD,#8290AC);}
  .g6{background:linear-gradient(150deg,#C6D5C5,#84A382);}
  .g7{background:linear-gradient(150deg,#E3B7CA,#A96684);}
  .g8{background:linear-gradient(150deg,#D8C1B2,#A5826C);}

  /* ---------- TRENDING (banner slideshow) ---------- */
  .trending{background:var(--sand);padding:110px 0;}
  .trending .section-head{padding:0 28px;}
  .tr-shell{position:relative;max-width:1280px;margin:0 auto;padding:0 28px;}
  .tr-window{overflow:hidden;}
  .tr-track{display:flex;transition:transform .65s cubic-bezier(.6,.05,.2,1);}
  .tr-slide{
    min-width:100%;display:grid;grid-template-columns:1fr 1fr;align-items:center;gap:64px;
    background:var(--porcelain);
  }
  .tr-media{height:460px;position:relative;overflow:hidden;background:#fff;display:flex;align-items:center;justify-content:center;}
  .tr-media img{width:100%;height:100%;object-fit:cover;position:relative;z-index:2;background:#fff;}
  .tr-media .img-fallback{z-index:1;}
  .tr-text{padding:40px 56px 40px 0;}
  .tr-text h3{font-size:clamp(2rem,3.4vw,2.9rem);line-height:1.1;margin:20px 0 14px;}
  .tr-text h3 em{font-style:italic;color:var(--rose);}
  .tr-text .tagline{font-size:.8rem;letter-spacing:.3em;text-transform:uppercase;color:var(--muted);margin-bottom:16px;}
  .tr-text p{color:var(--muted);font-weight:300;font-size:.95rem;max-width:420px;margin-bottom:16px;}
  .tr-text p strong,.spot-card p strong,.step p strong,.faq-a p strong,.req-list li strong{color:var(--rose);font-weight:600;}
  .tr-text .price-row{justify-content:flex-start;margin-bottom:28px;}
  .tr-nav{
    position:absolute;top:50%;transform:translateY(-50%);z-index:5;
    width:52px;height:52px;border-radius:50%;border:1px solid var(--hairline);cursor:pointer;
    background:#fff;color:var(--rose);font-size:1.1rem;
    display:flex;align-items:center;justify-content:center;
    box-shadow:0 10px 30px rgba(36,26,30,.12);transition:all .3s;
  }
  .tr-nav:hover{background:var(--rose);color:#fff;border-color:var(--rose);}
  .tr-prev{left:0;}
  .tr-next{right:0;}
  .tr-dots{display:flex;gap:12px;justify-content:center;margin-top:34px;}
  .tr-dots .dot{background:var(--hairline);}
  .tr-dots .dot.active{background:var(--rose);}

  /* ---------- Split editorial sections ---------- */
  .split .wrap{display:grid;grid-template-columns:1fr 1fr;gap:80px;align-items:center;}
  .split-media{height:480px;position:relative;overflow:hidden;}
  .split-media::after{content:"";position:absolute;inset:14px;border:1px solid rgba(255,255,255,.5);pointer-events:none;}
  .split-media.no-border::after{display:none;}
  .split-text h2{font-size:clamp(1.9rem,3.2vw,2.7rem);line-height:1.14;margin:20px 0 20px;}
  .split-text h2 em{font-style:italic;color:var(--rose);}
  .split-text p{color:var(--muted);font-weight:300;margin-bottom:18px;}
  .split-text p strong{color:var(--ink);font-weight:500;}
  .split-stats{display:flex;gap:0;margin:34px 0 38px;border-top:1px solid var(--hairline);border-bottom:1px solid var(--hairline);}
  .stat{flex:1;text-align:center;padding:22px 10px;}
  .stat + .stat{border-left:1px solid var(--hairline);}
  .stat b{display:block;font-family:var(--ff-display);font-size:2.1rem;color:var(--rose);line-height:1;font-weight:500;margin-bottom:6px;}
  .stat span{font-size:.66rem;letter-spacing:.22em;text-transform:uppercase;color:var(--muted);}
  .split.flip .split-media{order:2;}
  .split.flip .split-text{order:1;}
  .ph-img{
    position:absolute;inset:0;display:flex;align-items:center;justify-content:center;
    color:rgba(255,255,255,.85);font-size:.62rem;font-weight:500;letter-spacing:.2em;text-transform:uppercase;
  }
  .ph-img span{border:1px dashed rgba(255,255,255,.55);padding:9px 16px;background:rgba(0,0,0,.1);}
  .split-media-link,.become-media-link{position:absolute;inset:0;display:block;z-index:2;}
  .split-media img,.become-media img{
    position:absolute;inset:0;width:100%;height:100%;object-fit:cover;transition:transform .6s ease;
  }
  .split-media:hover img,.become-media:hover img{transform:scale(1.04);}

  /* ---------- Collab kit ---------- */
  .kit{background:var(--porcelain);color:var(--ink);position:relative;}
  .kit .section-head p{color:var(--muted);}
  .kit-stats{display:flex;max-width:640px;margin:0 auto 64px;gap:0;position:relative;z-index:2;}
  .kit-stats .stat{flex:1;text-align:center;padding:0 10px;}
  .kit-stats .stat+.stat{border-left:1px solid var(--hairline);}
  .kit-stats .stat b{display:block;font-family:var(--ff-display);font-size:clamp(1.4rem,2.2vw,1.8rem);color:var(--rose);font-weight:500;line-height:1;margin-bottom:8px;}
  .kit-stats .stat span{font-size:.62rem;letter-spacing:.18em;text-transform:uppercase;color:var(--muted);}
  .kit-list{max-width:820px;margin:0 auto;position:relative;z-index:2;}
  .kit-row{display:grid;grid-template-columns:56px 1fr;gap:28px;padding:36px 0;border-top:1px solid var(--hairline);}
  .kit-row:last-child{border-bottom:1px solid var(--hairline);}
  .kit-num{font-family:var(--ff-display);font-style:italic;font-size:1.5rem;color:var(--rosewood);line-height:1;padding-top:4px;}
  .kit-row h3{font-size:1.4rem;font-weight:500;margin-bottom:8px;letter-spacing:.01em;}
  .kit-row .kit-sub{font-size:.64rem;letter-spacing:.24em;text-transform:uppercase;color:var(--gold);margin-bottom:10px;font-weight:500;}
  .kit-row p{color:var(--muted);font-weight:300;font-size:.94rem;max-width:640px;}
  .kit-row p strong{color:var(--ink);font-weight:600;}
  .kit-tags{display:flex;flex-wrap:wrap;gap:6px 18px;margin-top:14px;}
  .kit-tag{font-size:.72rem;font-weight:400;color:var(--muted);position:relative;padding-left:14px;}
  .kit-tag::before{content:"";position:absolute;left:0;top:.55em;width:5px;height:5px;border-radius:50%;background:var(--rosewood);}
  .kit-cta{text-align:center;margin-top:52px;position:relative;z-index:2;}
  .kit-cta .fine{margin-top:16px;font-size:.8rem;color:var(--muted);font-weight:300;}
  .kit-cta .fine strong{color:var(--ink);font-weight:600;}
  @media (max-width:640px){.kit-stats{flex-direction:column;gap:20px;}.kit-stats .stat+.stat{border-left:none;border-top:1px solid var(--hairline);padding-top:20px;}}

  /* ---------- Become a creator ---------- */
  .become{background:var(--porcelain);}
  .become .wrap{display:grid;grid-template-columns:1fr 1fr;gap:80px;align-items:center;}
  .become h2{font-size:clamp(2rem,3.6vw,3rem);line-height:1.1;margin:20px 0 20px;}
  .become h2 em{font-style:italic;color:var(--rose);}
  .become > .wrap p{color:var(--muted);font-weight:300;margin-bottom:14px;}
  .req-list{list-style:none;margin:30px 0 38px;border-top:1px solid var(--hairline);}
  .req-list li{padding:18px 4px 18px 40px;position:relative;color:var(--ink);font-size:.94rem;font-weight:300;border-bottom:1px solid var(--hairline);}
  .req-list li::before{content:"";position:absolute;left:8px;top:50%;transform:translateY(-50%) rotate(45deg);width:7px;height:7px;border:1px solid var(--gold);}
  .become-media{height:520px;position:relative;overflow:hidden;}
  .become-media::after{content:"";position:absolute;inset:14px;border:1px solid rgba(255,255,255,.5);pointer-events:none;}
  .gDark{background:linear-gradient(140deg,#3A1D26,var(--rose) 60%,#D8A2AE);}

  /* ---------- Referral / Collab program (MODAL) ---------- */
  .ref-modal-backdrop{
    position:fixed;inset:0;background:rgba(24,10,14,.6);backdrop-filter:blur(4px);
    z-index:500;display:none;align-items:center;justify-content:center;padding:20px;
  }
  .ref-modal-backdrop.show{display:flex;}
  .ref-modal{
    background:var(--porcelain);max-width:980px;width:100%;max-height:92vh;overflow:auto;
    position:relative;padding:56px 48px 48px;animation:pop .35s ease;
  }
  .ref-modal .section-head{margin-bottom:48px;}

  .ref-own{max-width:640px;margin:0 auto 48px;}
  .ref-own-label{font-size:.66rem;letter-spacing:.26em;text-transform:uppercase;color:var(--gold-deep);font-weight:500;margin-bottom:10px;}
  .ref-own-active{
    background:var(--blush);border:1px solid var(--rosewood);padding:30px 32px;text-align:center;
  }
  .ref-own-hi{font-size:.92rem;color:var(--ink);font-weight:400;margin-bottom:16px;}
  .ref-own-row{display:flex;gap:12px;flex-wrap:wrap;}
  .ref-own-row input[type="text"]{
    flex:1;min-width:180px;padding:14px 16px;border:1px solid var(--rosewood);background:#fff;
    font-family:var(--ff-body);font-size:.84rem;letter-spacing:.02em;color:var(--rose-deep);
  }
  .ref-own-row .btn{border-color:var(--rose);color:var(--rose);}
  .ref-own-row .btn:hover{background:var(--rose);color:#fff;}
  .ref-own-copied{
    margin-top:10px;font-size:.72rem;letter-spacing:.14em;text-transform:uppercase;color:var(--rose);
    opacity:0;transition:opacity .25s;
  }
  .ref-own-copied.show{opacity:1;}
  .ref-own-empty{
    background:var(--sand);border:1px dashed var(--hairline);padding:30px 32px;text-align:center;
  }
  .ref-own-empty p{font-size:.9rem;color:var(--muted);font-weight:300;margin-bottom:18px;}

  .ref-tiers{display:grid;grid-template-columns:repeat(3,1fr);gap:26px;max-width:1080px;margin:0 auto 56px;}
  .ref-tier{
    background:#fff;border:1px solid var(--hairline);padding:36px 30px 32px;position:relative;
    transition:transform .3s ease, box-shadow .3s ease, border-color .3s ease;
  }
  .ref-tier:hover{transform:translateY(-4px);border-color:var(--rosewood);box-shadow:0 18px 40px -22px rgba(124,51,69,.35);}
  .ref-tier.featured{border-color:var(--gold);}
  .ref-tier .ref-badge{
    display:inline-block;font-size:.62rem;font-weight:500;letter-spacing:.22em;text-transform:uppercase;
    color:var(--gold-deep);background:var(--blush);padding:6px 14px;margin-bottom:18px;
  }
  .ref-tier h3{font-size:1.3rem;margin-bottom:18px;letter-spacing:.01em;}
  .ref-tier ul{list-style:none;}
  .ref-tier li{font-size:.88rem;color:var(--muted);font-weight:300;padding:8px 0 8px 22px;position:relative;border-top:1px solid var(--hairline);}
  .ref-tier li:first-of-type{border-top:none;}
  .ref-tier li::before{content:"";position:absolute;left:0;top:16px;transform:rotate(45deg);width:6px;height:6px;border:1px solid var(--gold);}
  .ref-tier li strong{color:var(--ink);font-weight:500;}

  .ref-entry{
    max-width:560px;margin:0 auto;background:#fff;border:1px solid var(--hairline);
    padding:40px 44px;text-align:center;
  }
  .ref-entry p.ref-lead{font-size:.94rem;color:var(--muted);font-weight:300;margin-bottom:22px;}
  .ref-entry .ref-form{display:flex;gap:12px;flex-wrap:wrap;}
  .ref-entry input[type="text"]{
    flex:1;min-width:180px;padding:15px 18px;border:1px solid var(--hairline);background:#fff;
    font-family:var(--ff-body);font-size:.9rem;letter-spacing:.03em;color:var(--ink);
  }
  .ref-entry input[type="text"]::placeholder{color:#B8A99C;}
  .ref-entry input[type="text"]:focus{outline:none;border-color:var(--gold);}
  .ref-entry .btn{border:none;}
  .ref-entry .ref-or{margin-top:18px;font-size:.8rem;color:var(--muted);font-weight:300;}
  .ref-entry .ref-or a{color:var(--rose);border-bottom:1px solid var(--gold);padding-bottom:2px;}
  .ref-entry .ref-or a:hover{color:var(--rose-deep);}
  @media(max-width:820px){.ref-tiers{grid-template-columns:1fr;}}
  @media(max-width:640px){.ref-modal{padding:70px 24px 36px;}}

  /* ---------- Steps ---------- */
  .steps{background:var(--sand);}
  .step-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:0;border:1px solid var(--hairline);background:var(--porcelain);}
  .step{padding:48px 40px;position:relative;}
  .step + .step{border-left:1px solid var(--hairline);}
  .step-num{font-family:var(--ff-display);font-style:italic;font-size:2.8rem;color:var(--rosewood);opacity:.5;line-height:1;margin-bottom:18px;display:block;}
  .step h3{font-size:1.5rem;margin-bottom:10px;}
  .step p{font-size:.9rem;color:var(--muted);font-weight:300;}

  /* ---------- FAQ ---------- */
  .faq-list{max-width:840px;margin:0 auto;border-top:1px solid var(--hairline);}
  .tr-media-link{display:block;width:100%;height:100%;}
  .faq-item{border-bottom:1px solid var(--hairline);}
  .faq-q{
    width:100%;text-align:left;background:none;border:none;cursor:pointer;
    padding:26px 56px 26px 4px;font-family:var(--ff-display);font-weight:500;font-size:1.28rem;color:var(--ink);position:relative;
    transition:color .25s;letter-spacing:.01em;
  }
  .faq-q:hover{color:var(--rose);}
  .faq-q::after{
    content:"+";position:absolute;right:14px;top:50%;transform:translateY(-50%);
    font-family:var(--ff-body);font-weight:300;font-size:1.5rem;color:var(--gold);transition:transform .3s;
  }
  .faq-item.open .faq-q::after{transform:translateY(-50%) rotate(45deg);}
  .faq-a{max-height:0;overflow:hidden;transition:max-height .35s ease;}
  .faq-a p{padding:0 4px 28px;color:var(--muted);font-size:.94rem;font-weight:300;max-width:720px;}

  /* ---------- CTA band ---------- */
  .cta-section{padding:60px 28px 120px;background:var(--porcelain);}
  .cta-band{
    background:var(--ink);color:#fff;text-align:center;
    max-width:1280px;margin:0 auto;padding:96px 32px;position:relative;
  }
  .cta-band::before{content:"";position:absolute;inset:16px;border:1px solid rgba(233,207,168,.35);pointer-events:none;}
  .cta-band .kicker{color:#E9CFA8;}
  .cta-band .kicker::before,.cta-band .kicker::after{background:#E9CFA8;}
  .cta-band h2{font-size:clamp(2.1rem,3.8vw,3.2rem);margin:20px 0 16px;}
  .cta-band h2 em{font-style:italic;color:#F0CBD3;}
  .cta-band p{color:#CBB5BB;max-width:520px;margin:0 auto 38px;font-weight:300;}

  /* ---------- Spotlight (zigzag feature blocks) ---------- */
  .spotlight{background:var(--porcelain);}
  .spot-row{
    display:grid;grid-template-columns:1fr 1fr;align-items:center;
    max-width:1180px;margin:0 auto 90px;
  }
  .spot-row:last-child{margin-bottom:0;}
  .spot-media{
    position:relative;height:430px;overflow:hidden;background:#fff;border:1px solid var(--hairline);
  }
  .spot-media img{
    position:absolute;inset:0;width:100%;height:100%;object-fit:cover;
    transition:transform .7s ease;
  }
  .spot-row:hover .spot-media img{transform:scale(1.04);}
  .spot-card{
    position:relative;z-index:2;background:#fff;border:1px solid var(--hairline);
    padding:44px 46px;margin-left:-70px;
    box-shadow:0 24px 60px rgba(36,26,30,.08);
    transition:box-shadow .35s ease, transform .35s ease;
  }
  .spot-row:hover .spot-card{box-shadow:0 30px 70px rgba(36,26,30,.14);transform:translateY(-4px);}
  .spot-card .spot-tag{font-size:.64rem;letter-spacing:.28em;text-transform:uppercase;color:var(--gold);margin-bottom:12px;}
  .spot-card h3{font-size:1.9rem;line-height:1.15;color:var(--rose);margin-bottom:14px;letter-spacing:.01em;}
  .spot-card p{font-size:.92rem;color:var(--muted);font-weight:300;margin-bottom:10px;max-width:420px;}
  .spot-card .spot-more{
    display:inline-block;margin-top:14px;font-size:.72rem;letter-spacing:.24em;text-transform:uppercase;
    color:var(--rose);border-bottom:1px solid var(--gold);padding-bottom:4px;transition:letter-spacing .25s;
  }
  .spot-row:hover .spot-more{letter-spacing:.3em;}
  .spot-row.flip .spot-media{order:2;}
  .spot-row.flip .spot-card{order:1;margin-left:0;margin-right:-70px;}
  @media (max-width: 880px){
    .spot-row{grid-template-columns:1fr;margin-bottom:64px;}
    .spot-media{height:320px;}
    .spot-card{margin:-50px 20px 0;padding:34px 28px;}
    .spot-row.flip .spot-media{order:1;}
    .spot-row.flip .spot-card{order:2;margin:-50px 20px 0;}
  }

  /* ---------- Modal + gallery ---------- */
  .modal-backdrop{
    position:fixed;inset:0;background:rgba(24,10,14,.6);backdrop-filter:blur(4px);
    z-index:500;display:none;align-items:center;justify-content:center;padding:20px;
  }
  .modal-backdrop.show{display:flex;}
  .modal{
    background:var(--porcelain);max-width:940px;width:100%;max-height:92vh;overflow:auto;
    display:grid;grid-template-columns:1.05fr 1fr;position:relative;animation:pop .35s ease;
  }
  @keyframes pop{from{transform:translateY(18px);opacity:0;}to{transform:translateY(0);opacity:1;}}
  .modal-gallery{padding:28px;background:#fff;border-right:1px solid var(--hairline);}
  .g-main{
    position:relative;height:380px;border:1px solid var(--hairline);background:#fff;
    display:flex;align-items:center;justify-content:center;cursor:zoom-in;overflow:hidden;
  }
  .g-main img{width:100%;height:100%;object-fit:contain;padding:16px;position:relative;z-index:2;background:#fff;}
  .g-zoom-hint{
    position:absolute;bottom:12px;right:12px;z-index:3;
    background:rgba(255,255,255,.92);border:1px solid var(--hairline);
    font-size:.62rem;letter-spacing:.2em;text-transform:uppercase;color:var(--muted);
    padding:6px 12px;
  }
  .g-thumbs{display:flex;gap:12px;margin-top:14px;}
  .g-thumb{
    width:86px;height:86px;border:1px solid var(--hairline);background:#fff;cursor:pointer;
    position:relative;overflow:hidden;padding:0;transition:border-color .25s;
  }
  .g-thumb img{width:100%;height:100%;object-fit:contain;padding:6px;position:relative;z-index:2;background:#fff;}
  .g-thumb.active{border-color:var(--rose);box-shadow:inset 0 0 0 1px var(--rose);}
  .modal-body{padding:44px 40px;}
  .modal-close{
    position:absolute;top:16px;right:16px;z-index:5;width:40px;height:40px;
    background:var(--porcelain);border:1px solid var(--hairline);color:var(--ink);font-size:1rem;cursor:pointer;
    transition:all .25s;
  }
  .modal-close:hover{background:var(--rose);color:#fff;border-color:var(--rose);}
  .modal-body .cat{font-size:.66rem;letter-spacing:.28em;text-transform:uppercase;color:var(--gold);}
  .modal-body h3{font-size:2rem;margin:10px 0 8px;line-height:1.15;}
  .modal-body .stars{font-size:.9rem;}
  .modal-price{margin:16px 0;display:flex;gap:14px;align-items:baseline;border-bottom:1px solid var(--hairline);padding-bottom:18px;flex-wrap:wrap;}
  .modal-price .price{font-size:1.4rem;}
  .modal-price .price-old{font-size:.95rem;}
  .save-tag{font-size:.66rem;letter-spacing:.16em;text-transform:uppercase;background:var(--blush);color:var(--rose-deep);padding:5px 12px;}
  .modal-body .long{color:var(--muted);font-size:.94rem;font-weight:300;margin:18px 0;}
  .modal-body h4{font-size:.68rem;letter-spacing:.26em;text-transform:uppercase;color:var(--rose);margin:22px 0 12px;}
  .modal-body ul{list-style:none;}
  .modal-body li{font-size:.9rem;color:var(--muted);font-weight:300;padding:6px 0 6px 26px;position:relative;}
  .modal-body li::before{content:"";position:absolute;left:4px;top:14px;transform:rotate(45deg);width:6px;height:6px;border:1px solid var(--gold);}
  .modal-cta{margin-top:30px;display:flex;gap:14px;flex-wrap:wrap;}

  /* ---------- Lightbox (close-up view) ---------- */
  .lightbox{
    position:fixed;inset:0;background:rgba(15,7,10,.92);z-index:600;
    display:none;align-items:center;justify-content:center;padding:32px;cursor:zoom-out;
  }
  .lightbox.show{display:flex;}
  .lightbox img{max-width:94vw;max-height:90vh;object-fit:contain;background:#fff;padding:20px;}
  .lightbox-close{
    position:absolute;top:22px;right:26px;background:none;border:1px solid rgba(255,255,255,.5);
    color:#fff;width:44px;height:44px;font-size:1.1rem;cursor:pointer;transition:all .25s;
  }
  .lightbox-close:hover{background:#fff;color:var(--ink);}
  .lightbox-hint{
    position:absolute;bottom:22px;left:50%;transform:translateX(-50%);
    color:rgba(255,255,255,.65);font-size:.66rem;letter-spacing:.24em;text-transform:uppercase;
  }

  /* ---------- Footer ---------- */
  footer{background:var(--ink);color:#B8A0A7;padding:70px 28px 34px;}
  .foot-grid{max-width:1280px;margin:0 auto 50px;display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:48px;}
  footer .logo{color:#fff;margin-bottom:18px;display:block;}
  footer p{font-size:.86rem;max-width:300px;font-weight:300;}
  footer h4{color:#E9CFA8;font-size:.7rem;letter-spacing:.28em;text-transform:uppercase;margin-bottom:18px;font-weight:500;}
  footer ul{list-style:none;}
  footer li{margin-bottom:12px;}
  footer a{font-size:.86rem;font-weight:300;transition:color .25s;}
  footer a:hover{color:#fff;}
  .foot-bottom{
    max-width:1280px;margin:0 auto;border-top:1px solid rgba(255,255,255,.1);
    padding-top:26px;font-size:.74rem;letter-spacing:.08em;display:flex;justify-content:space-between;flex-wrap:wrap;gap:10px;font-weight:300;
  }

  /* ---------- Responsive ---------- */
  @media (max-width: 1020px){
    .product-grid{grid-template-columns:repeat(3,1fr);}
    .tr-text{padding:32px 40px 32px 0;}
  }
  @media (max-width: 880px){
    .slide{grid-template-columns:1fr;gap:28px;padding:48px 28px 24px;min-height:0;}
    .hero-media{order:-1;height:300px;}
    .hero-text p{max-width:100%;}
    .hero-controls{
      position:static;left:auto;bottom:auto;transform:none;
      justify-content:center;margin:8px 0 32px;
    }
    .split .wrap,.become .wrap{grid-template-columns:1fr;gap:44px;}
    .split.flip .split-media{order:0;}
    .split-media,.become-media{height:340px;}
    .product-grid{grid-template-columns:repeat(2,1fr);}
    .tr-slide{grid-template-columns:1fr;gap:0;}
    .tr-media{height:320px;}
    .tr-text{padding:36px 32px 44px;}
    .step-grid{grid-template-columns:1fr;}
    .step + .step{border-left:none;border-top:1px solid var(--hairline);}
    .modal{grid-template-columns:1fr;}
    .modal-gallery{border-right:none;border-bottom:1px solid var(--hairline);}
    .g-main{height:300px;}
    .foot-grid{grid-template-columns:1fr 1fr;}
    .kit-row{grid-template-columns:1fr;gap:10px;padding:34px 6px;}
  }
  @media (max-width: 640px){
    .nav{grid-template-columns:auto 1fr auto;}
    .logo{grid-column:1;}
    .nav-apply{grid-column:3;justify-self:end;}
    .nav-apply .btn{padding:8px 16px;font-size:.68rem;}
    .menu-toggle{grid-column:2;justify-self:end;display:flex;align-items:center;justify-content:center;width:36px;height:36px;}
    .nav-links{
      display:none;position:absolute;top:100%;left:0;right:0;background:var(--porcelain);
      flex-direction:column;gap:0;border-bottom:1px solid var(--hairline);padding:6px 0 14px;
      box-shadow:0 16px 24px -12px rgba(30,26,23,.14);
    }
    .nav-links.open{display:flex;}
    .nav-links li{width:100%;text-align:center;padding:13px 0;}
    .nav-links li:not(:last-child){border-bottom:1px solid var(--hairline);}
    .nav-links a::after{display:none;}
    .product-grid{grid-template-columns:1fr;}
    .tr-nav{width:42px;height:42px;}
    .foot-grid{grid-template-columns:1fr;}
    section{padding:80px 22px;}
    .hero-media{height:230px;}
  }
  @media (prefers-reduced-motion: reduce){
    html{scroll-behavior:auto;}
    .slides,.tr-track,.product-img img{transition:none;}
  }
</style>
</head>
<body>

<div class="top-bar">We're looking for content creators &nbsp;·&nbsp; Apply, get verified &amp; qualified, and receive exclusive gifts from AVÉA Beauty</div>

<header>
  <nav class="nav">
    <a class="logo" href="#top">AVÉA<span>.</span></a>
    <button class="menu-toggle" id="menuToggle" aria-label="Open menu" aria-expanded="false" onclick="toggleNav()">☰</button>
    <ul class="nav-links" id="navLinks">
      <li><a href="#products">Products</a></li>
      <li><a href="#trending">Trending</a></li>
      <li><a href="#spotlight">Spotlight</a></li>
      <li><a href="#kit">The Kit</a></li>
      <li><a href="#become">Creators</a></li>
      <li><a href="#" onclick="openReferralModal();return false;">Refer &amp; Earn</a></li>
      <li><a href="#faq">FAQ</a></li>
    </ul>
    <div class="nav-apply"><a class="btn btn-primary" href="apply.php">Apply</a></div>
  </nav>
</header>
<script>
  function toggleNav(){
    var nav=document.getElementById('navLinks'), btn=document.getElementById('menuToggle');
    var open=nav.classList.toggle('open');
    btn.textContent=open?'✕':'☰';
    btn.setAttribute('aria-expanded',open?'true':'false');
  }
  document.getElementById('navLinks').addEventListener('click',function(e){
    if(e.target.tagName==='A'){toggleNav.hasOpened=false;this.classList.remove('open');document.getElementById('menuToggle').textContent='☰';document.getElementById('menuToggle').setAttribute('aria-expanded','false');}
  });
</script>

<!-- ==========================================================
 HERO SLIDER — split layout: text sa kaliwa, video/image sa kanan.

 PARA MAGDAGDAG NG SLIDE: kopyahin ang buong <div class="slide">
 at idikit pagkatapos ng huling slide. Automatic na kasali sa slider.

 PARA SA VIDEO SLIDE (Slide 1): palitan ang src ng
 files/videos/hero-video.mp4 ng aktwal na video file. Ang poster
 attribute ang ipapakita habang naglo-load pa ang video.
=========================================================== -->
<div class="hero-slider" id="top">
  <div class="slides" id="heroSlides">

    <div class="slide">
      <div class="hero-text">
        <span class="kicker left">We're Looking For Content Creators</span>
        <h1>Create with us. <em>Get rewarded.</em></h1>
        <p>AVÉA Beauty is searching for content creators of every size. <strong>Submit your application, get verified and qualified</strong>, and start receiving benefits and gifts from AVÉA Beauty.</p>
        <a class="btn btn-primary" href="apply.php">Apply Now</a>
      </div>
      <div class="hero-media">
        <video autoplay muted loop playsinline poster="files/images/hero1.png">
          <source src="files/video/hero1.mp4" type="video/mp4">
        </video>
      </div>
    </div>

    <div class="slide">
      <div class="hero-text">
        <span class="kicker left">Benefits &amp; Gifts Await</span>
        <h1>Verified creators get <em>the full experience.</em></h1>
        <p><strong>Once your application is verified and qualified</strong>, a curated gift box of full-size AVÉA bestsellers — plus exclusive creator benefits — is on its way to your doorstep.</p>
        <a class="btn btn-primary" href="apply.php">Start Your Application</a>
      </div>
      <div class="hero-media">
        <img src="files/images/hero2.png" alt="AVÉA Beauty — Mauve Muse Collection">
      </div>
    </div>

    <div class="slide">
      <div class="hero-text">
        <span class="kicker left">Our Community</span>
        <h1>Join 500+ creators <em>already with AVÉA.</em></h1>
        <p><strong>TikTok, Instagram, YouTube, and Facebook creators are all welcome.</strong> Apply today, pass verification, and unlock the gifts and perks our creator community enjoys.</p>
        <a class="btn btn-primary" href="apply.php">Become a Creator</a>
      </div>
      <div class="hero-media">
        <img src="files/images/hero1.png" alt="AVÉA Beauty — Real Creators, Real Partnerships">
      </div>
    </div>

  </div>

  <div class="hero-controls">
    <button class="slider-nav" aria-label="Previous slide" onclick="moveHero(-1)">‹</button>
    <div class="slider-dots" id="heroDots"></div>
    <button class="slider-nav" aria-label="Next slide" onclick="moveHero(1)">›</button>
  </div>
</div>

<!-- ================= WHY CHOOSE US ================= -->
<section class="reasons" id="why-avea">
  <div class="wrap">
    <div class="section-head">
      <span class="kicker">The Five Reasons</span>
      <h2>Why thousands choose <em>AVÉA</em></h2>
      <p>Clean, cruelty-free formulas made to work with Filipina skin — not against it. Here's what makes AVÉA different, straight from the jar to your daily routine.</p>
    </div>
    <div class="reasons-grid">
      <div class="reason-card">
        <div class="reason-icon">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v3M12 19v3M4.2 4.2l2.1 2.1M17.7 17.7l2.1 2.1M2 12h3M19 12h3M4.2 19.8l2.1-2.1M17.7 6.3l2.1-2.1"></path></svg>
        </div>
        <h3>Our Bestselling Glow</h3>
        <p>Hydra Pro Glow is our most-loved serum for a reason — a lightweight formula that leaves skin looking visibly brighter from the very first use.</p>
      </div>
      <div class="reason-card">
        <div class="reason-icon">
          <svg viewBox="0 0 24 24"><path d="M12 2s6 7 6 12a6 6 0 0 1-12 0c0-5 6-12 6-12z"></path></svg>
        </div>
        <h3>Built for Humid Weather</h3>
        <p>The Hydra Pro Serum and SPF 50 Sun Protection Cream hold moisture and protect skin through heat and humidity, all day, every day.</p>
      </div>
      <div class="reason-card">
        <div class="reason-icon">
          <svg viewBox="0 0 24 24"><path d="M4 12l4 4L20 6"></path></svg>
        </div>
        <h3>8–12 Hours of Wear</h3>
        <p>Bestsellers like the 3D Hydra Lip Gloss and Full Coverage 2-in-1 Foundation & Concealer are made to last through long days without touch-ups.</p>
      </div>
      <div class="reason-card">
        <div class="reason-icon">
          <svg viewBox="0 0 24 24"><path d="M20 12c-2 4-6 7-8 7s-6-3-8-7c2-4 6-7 8-7s6 3 8 7z"></path><circle cx="12" cy="12" r="1.6"></circle></svg>
        </div>
        <h3>Shades Made for Filipina Skin</h3>
        <p>Every shade range is developed and tested on fair, morena, and deep skin tones so you always find your true match.</p>
      </div>
      <div class="reason-card">
        <div class="reason-icon">
          <svg viewBox="0 0 24 24"><path d="M20 15.5A8.5 8.5 0 0 1 8.5 4a8.5 8.5 0 1 0 11.5 11.5z"></path></svg>
        </div>
        <h3>Cruelty-Free, Dermatologist-Tested</h3>
        <p>Every AVÉA formula is FDA-notified, paraben-free, and patch-tested with a panel of Filipina testers before it ever reaches you.</p>
      </div>
    </div>
  </div>
</section>

<!-- ================= PRODUCTS ================= -->
<section id="products" style="padding-top:40px;">
  <div class="wrap">
    <div class="section-head">
      <span class="kicker">The Collection</span>
      <h2>Composed to be <em>shown off</em></h2>
      <p>Clean, cruelty-free formulas made for everyday Filipinas. Hover to see another angle, and select any piece for its full story.</p>
    </div>
    <div class="cat-tabs" id="catTabs"></div>
    <div class="product-grid" id="productGrid"></div>
    <div class="show-more-wrap" id="showMoreWrap">
      <button class="btn btn-outline" id="showMoreBtn" onclick="toggleShowMore()">Show More</button>
    </div>
  </div>
</section>

<!-- ================= TRENDING (banner slideshow) ================= -->
<section class="trending" id="trending">
  <div class="section-head">
    <span class="kicker">This Season</span>
    <h2>Trending <em>right now</em></h2>
    <p>The pieces our community cannot stop talking about — presented one at a time, as they deserve.</p>
  </div>
  <div class="tr-shell">
    <button class="tr-nav tr-prev" aria-label="Previous" onclick="moveTrend(-1)">‹</button>
    <div class="tr-window">
      <div class="tr-track" id="trTrack">

        <!-- TRENDING SLIDE 1 -->
        <div class="tr-slide">
          <div class="tr-media">
            <div class="img-fallback g1"></div>
            <img src="files/images/AVEA_Pressed_Powder_Compact.png" alt="AVÉA Second Skin Pressed Powder" onerror="this.remove()">
          </div>
          <div class="tr-text">
            <span class="kicker left">Bestseller</span>
            <h3>Second Skin <em>Pressed Powder</em></h3>
            <p class="tagline">Soft focus, zero cake</p>
            <p><strong>A silk-fine pressed powder</strong> that sets makeup without masking skin — the finishing touch every AVÉA creator reaches for before camera roll.</p>
            <div class="price-row"><span class="price">₱1,450</span><span class="price-old">₱1,750</span></div>
            <a class="btn btn-outline" href="apply.php">Feature This Product</a>
          </div>
        </div>

        <!-- TRENDING SLIDE 2 -->
        <div class="tr-slide">
          <div class="tr-media">
            <div class="img-fallback g2"></div>
            <img src="files/images/AVEA_Skin_Lover_Serum_Foundation.png" alt="AVÉA Skin Lover Intensive Serum Foundation" onerror="this.remove()">
          </div>
          <div class="tr-text">
            <span class="kicker left">New Obsession</span>
            <h3>Skin Lover <em>Serum Foundation</em></h3>
            <p class="tagline">Skincare and coverage, in one drop</p>
            <p><strong>An intensive serum foundation</strong> that melts into skin for a lit-from-within finish — buildable coverage that never feels heavy on camera.</p>
            <div class="price-row"><span class="price">₱2,100</span><span class="price-old">₱2,450</span></div>
            <a class="btn btn-outline" href="apply.php">Feature This Product</a>
          </div>
        </div>

        <!-- TRENDING SLIDE 3 -->
        <div class="tr-slide">
          <div class="tr-media">
            <div class="img-fallback g4"></div>
            <img src="files/images/AVEA_Power_Shade_Energizing_Contour.jpg" alt="AVÉA Power Shade Energizing Contour" onerror="this.remove()">
          </div>
          <div class="tr-text">
            <span class="kicker left">Trending Now</span>
            <h3>Power Shade <em>Energizing Contour</em></h3>
            <p class="tagline">Definition that wakes up your face</p>
            <p><strong>A cream contour stick</strong> with an energizing finish — blends effortlessly for sculpted, camera-ready dimension in seconds.</p>
            <div class="price-row"><span class="price">₱1,600</span><span class="price-old">₱1,900</span></div>
            <a class="btn btn-outline" href="apply.php">Feature This Product</a>
          </div>
        </div>

      </div>
    </div>
    <button class="tr-nav tr-next" aria-label="Next" onclick="moveTrend(1)">›</button>
    <div class="tr-dots" id="trDots"></div>
  </div>
</section>

<!-- ================= SPLIT: OUR STORY ================= -->
<section class="split">
  <div class="wrap">
    <div class="split-media">
      <div class="ph-img g7"><span>Brand Editorial Image</span></div>
      <a  class="split-media-link" aria-label="View brand editorial image">
        <img src="files/images/88f272f3-c2a1-4acf-83bb-19c193ed138e.png" alt="Brand Editorial" loading="lazy" onerror="this.remove()">
      </a>
    </div>
    <div class="split-text">
      <span class="kicker left">Our Story</span>
      <h2>Beauty made <em>with</em> creators, not merely for them</h2>
      <p><strong>AVÉA began with one conviction:</strong> the most honest beauty content comes from real people, not studios. Every shade we release is tested first with our creator community — from undertones that flatter morena skin to formulas that hold through a Manila commute.</p>
      <p><strong>When you collaborate with us, you are not simply presenting products.</strong> You sit in on shade reviews, vote on packaging concepts, and help decide what we create next.</p>
      <div class="split-stats">
        <div class="stat"><b>500+</b><span>Partner Creators</span></div>
        <div class="stat"><b>42</b><span>Products Launched</span></div>
        <div class="stat"><b>4.8</b><span>Average Rating</span></div>
      </div>
      <a class="btn btn-primary" href="apply.php">Begin Your Collaboration</a>
    </div>
  </div>
</section>

<!-- ================= SPLIT: CONSCIOUS BEAUTY ================= -->
<section class="split flip" style="background:var(--sand);">
  <div class="wrap">
    <div class="split-media no-border">
      <div class="ph-img g6"><span>Clean Beauty Editorial Image</span></div>
      <div class="split-media-link" aria-label="Clean beauty editorial image">
        <img src="files/images/b7a3a32c-3a8b-4edc-a19f-a2d79d4aad87.png" alt="Clean Beauty Editorial" loading="lazy" onerror="this.remove()">
      </div>
    </div>
    <div class="split-text">
      <span class="kicker left">The Avéa Promise</span>
      <h2>Our approach to <em>conscious beauty</em></h2>
      <p><strong>AVÉA CARES</strong> is our long-term commitment to people and planet: cruelty-free formulation, responsibly sourced botanicals, and recyclable packaging across the entire line by 2027.</p>
      <p><strong>Every product is FDA-notified, dermatologist-tested, and free from parabens and harsh sulfates</strong> — beauty you can present to your audience with a genuinely clear conscience.</p>
      <a class="text-link" href="apply.php">Find Out More</a>
    </div>
  </div>
</section>

<!-- ================= SPOTLIGHT (zigzag features) ================= -->
<section class="spotlight" id="spotlight">
  <div class="wrap">
    <div class="section-head">
      <span class="kicker">Spotlight</span>
      <h2>Pieces worth a <em>closer look</em></h2>
      <p>Our most-loved formulas, told one story at a time. Select any piece to explore its own page.</p>
    </div>

    <div class="spot-row">
      <div class="spot-media">
        <div class="img-fallback g4"></div>
        <!-- Ilagay dito ang sarili mong image path, hal. src="files/images/banner1.png" -->
            <img src="files/images/Bouncy_Cloud_Cream.jpg" alt="Bouncy Cloud Cream" loading="lazy" onerror="this.remove()">
      </div>
      <div class="spot-card">
        <p class="spot-tag">The Base Essential</p>
        <h3>Bouncy <em>Cloud Cream</em></h3>
        <p><strong>A whipped, cushiony cream</strong> that bounces back onto skin, blending buildable coverage with a hydrating* feel in a single sweep.</p>
        <p>For a complexion that looks like skin, not makeup — even under the harshest studio lights.</p>
      </div>
    </div>

    <div class="spot-row flip">
      <div class="spot-media">
        <div class="img-fallback g1"></div>
        <!-- Ilagay dito ang sarili mong image path, hal. src="files/images/banner1.png" -->
            <img src="files/images/58e41ba9-32d8-4d62-a15c-8c97a9aae904.png" alt="Power Shake Pearly Magic Serum" loading="lazy" onerror="this.remove()">
      </div>
      <div class="spot-card">
        <p class="spot-tag">The Glow Essential</p>
        <h3>Power Shake <em>Pearly Magic Serum</em></h3>
        <p><strong>A pearlescent serum</strong> that melts into skin with a soft-focus shimmer, catching the light from every angle.</p>
        <p>For a lit-from-within finish that photographs as beautifully as it feels.</p>
      </div>
    </div>

    <div class="spot-row">
      <div class="spot-media">
        <div class="img-fallback g2"></div>
        <!-- Ilagay dito ang sarili mong image path, hal. src="files/images/banner1.png" -->
            <img src="files/images/63c5d4f8-5c68-4fb5-a672-2c642b23ff9e.png" alt="Power Shake Diva Refining Cream 3in1" loading="lazy" onerror="this.remove()">
      </div>
      <div class="spot-card">
        <p class="spot-tag">The Refining Essential</p>
        <h3>Power Shake <em>Diva Refining Cream 3in1</em></h3>
        <p><strong>A triple-duty cream</strong> that refines the look of texture, blurs the appearance of pores, and primes skin — all shaken into one bottle.</p>
        <p>For a smoother canvas before every application, no extra steps required.</p>
      </div>
    </div>

    <div class="spot-row flip">
      <div class="spot-media">
        <div class="img-fallback g5"></div>
        <!-- Ilagay dito ang sarili mong image path, hal. src="files/images/banner1.png" -->
            <img src="files/images/Cool_Crystal_Stick.jpg" alt="Cool Crystal Stick" loading="lazy" onerror="this.remove()">
      </div>
      <div class="spot-card">
        <p class="spot-tag">The Eye Essential</p>
        <h3>Cool <em>Crystal Stick</em></h3>
        <p><strong>A chilled crystal-tip stick</strong> glided along the eye area to de-puff* and brighten in just a few swipes.</p>
        <p>For a rested, energised look, even after the longest shoot days.</p>
      </div>
    </div>

    <div class="spot-row">
      <div class="spot-media">
        <div class="img-fallback g6"></div>
        <!-- Ilagay dito ang sarili mong image path, hal. src="files/images/banner1.png" -->
            <img src="files/images/fdf1ae04-8da6-4b5d-a8c7-fd305933a624.png" alt="So Chic Trio Blush" loading="lazy" onerror="this.remove()">
      </div>
      <div class="spot-card">
        <p class="spot-tag">The Cheek Essential</p>
        <h3>So Chic <em>Trio Blush</em></h3>
        <p><strong>Three complementary shades</strong> in one sculpted bottle, blending into a soft, second-skin flush.</p>
        <p>For a natural-looking colour that's easy to build and hard to overdo.</p>
      </div>
    </div>

    <div class="spot-row flip">
      <div class="spot-media">
        <div class="img-fallback g8"></div>
        <!-- Ilagay dito ang sarili mong image path, hal. src="files/images/banner1.png" -->
            <img src="files/images/Glow_Me_Up_Mask.jpg" alt="Power Shake Glow Me Up Mask" loading="lazy" onerror="this.remove()">
      </div>
      <div class="spot-card">
        <p class="spot-tag">The Mask Essential</p>
        <h3>Power Shake <em>Glow Me Up Mask</em></h3>
        <p>A vibrant treatment mask that works in minutes to leave skin looking brighter and visibly refreshed.</p>
        <p>For an instant glow-up before the camera rolls.</p>
      </div>
    </div>

  </div>
</section>

<!-- ================= COLLAB KIT ================= -->
<section class="kit" id="kit">
  <div class="wrap">
    <div class="section-head">
      <span class="kicker">The Creator Kit</span>
      <h2>Everything you receive once you're <em>verified &amp; qualified</em></h2>
      <p><strong>No guesswork, no fine print.</strong> After you submit your application and are verified and qualified, here is exactly what lands at your doorstep — and in your inbox — as your benefits and gifts from AVÉA Beauty, spelled out in full.</p>
    </div>

    <div class="kit-stats">
      <div class="stat"><b>₱8,000+</b><span>Average Kit Value</span></div>
      <div class="stat"><b>3–5 Days</b><span>From Approval to Dispatch</span></div>
      <div class="stat"><b>15 Days</b><span>Campaign Fee Payout</span></div>
    </div>

    <div class="kit-list">

      <div class="kit-row">
        <div class="kit-num">1</div>
        <div>
          <p class="kit-sub">Delivered to Your Door</p>
          <h3>The Full-Size Press Collection</h3>
          <p><strong>Six to eight full-size bestsellers and unreleased launches</strong> — never sachets, never miniatures. Your box is refreshed every campaign cycle, so you're always the first to shoot what's new, weeks ahead of anyone else on the timeline.</p>
          <div class="kit-tags">
            <span class="kit-tag">Full-size only</span>
            <span class="kit-tag">Refreshed every cycle</span>
            <span class="kit-tag">Pre-launch access</span>
          </div>
        </div>
      </div>

      <div class="kit-row">
        <div class="kit-num">2</div>
        <div>
          <p class="kit-sub">Delivered to Your Door</p>
          <h3>A Personal Welcome Édition</h3>
          <p>A <strong>handwritten welcome note</strong>, the full AVÉA lookbook, and a shade-matching guide mapped specifically to Filipina undertones — plus signature creator pieces, from a monogrammed vanity tray to a linen backdrop, made to look beautiful on camera from day one.</p>
          <div class="kit-tags">
            <span class="kit-tag">Handwritten note</span>
            <span class="kit-tag">Shade-match guide</span>
            <span class="kit-tag">Styling props</span>
          </div>
        </div>
      </div>

      <div class="kit-row">
        <div class="kit-num">3</div>
        <div>
          <p class="kit-sub">In Your Inbox</p>
          <h3>Campaign Briefs &amp; Brand Assets</h3>
          <p><strong>Clear briefs with talking points, deadlines, and reference imagery</strong> — never a script to read word for word. We hand you the story; the voice, the framing, and every creative decision stay entirely yours to make.</p>
          <div class="kit-tags">
            <span class="kit-tag">No forced scripts</span>
            <span class="kit-tag">Hi-res brand assets</span>
            <span class="kit-tag">Clear deadlines</span>
          </div>
        </div>
      </div>

      <div class="kit-row">
        <div class="kit-num">4</div>
        <div>
          <p class="kit-sub">Your Earnings</p>
          <h3>A Personal Code &amp; Commission</h3>
          <p>Your own promo code gives your audience a <strong>private discount</strong> and earns you a <strong>commission on every order it brings in</strong>. Campaign fees settle by bank transfer or e-wallet within fifteen days of content approval; commissions are paid out monthly, without you having to ask.</p>
          <div class="kit-tags">
            <span class="kit-tag">Bank transfer or e-wallet</span>
            <span class="kit-tag">Monthly commission</span>
            <span class="kit-tag">Zero hidden fees</span>
          </div>
        </div>
      </div>

      <div class="kit-row">
        <div class="kit-num">5</div>
        <div>
          <p class="kit-sub">Your Dashboard</p>
          <h3>Complete Transparency</h3>
          <p>A <strong>private creator dashboard</strong> tracks your code usage, commission balance, and payout schedule in real time — so the numbers are always a glance away, with nothing to chase and nothing left to guess at.</p>
          <div class="kit-tags">
            <span class="kit-tag">Real-time tracking</span>
            <span class="kit-tag">Payout history</span>
            <span class="kit-tag">Downloadable reports</span>
          </div>
        </div>
      </div>

      <div class="kit-row">
        <div class="kit-num">6</div>
        <div>
          <p class="kit-sub">The Community</p>
          <h3>Invitations &amp; Access</h3>
          <p><strong>Priority invitations</strong> to product launches, studio days, and creator salons, plus a seat in our private community — where our team previews concepts early and our creators help shape what AVÉA makes next.</p>
          <div class="kit-tags">
            <span class="kit-tag">Studio day invites</span>
            <span class="kit-tag">Early concept previews</span>
            <span class="kit-tag">Private creator chat</span>
          </div>
        </div>
      </div>

    </div>
    <div class="kit-cta">
      <a class="btn btn-primary" href="apply.php">Start Your Application</a>
      <p class="fine">Submit your application — once you're <strong>verified and qualified</strong>, your benefits and gifts from AVÉA Beauty are on their way. No cost, no obligation to accept.</p>
    </div>
  </div>
</section>

<!-- ================= BECOME A CREATOR ================= -->
<section class="become" id="become">
  <div class="wrap">
    <div>
      <span class="kicker left">Join the Programme</span>
      <h2>Become a <em>AVÉA Creator</em></h2>
      <p><strong>AVÉA Beauty is actively looking for content creators.</strong> We are not searching for flawless feeds — we are searching for voices audiences trust. Submit your application, and once you are verified and qualified, you'll start receiving benefits and gifts from AVÉA Beauty.</p>
      <ul class="req-list">
        <li><strong>Open to creators of every size</strong> — even below 1,000 followers, as long as you're willing to create</li>
        <li><strong>TikTok, Instagram, YouTube, and Facebook</strong> creators are all welcome to apply</li>
        <li><strong>No exclusivity clause</strong> — collaborate freely with other beauty brands at the same time</li>
        <li><strong>Applications reviewed within 1–3 business days</strong>, with a personal reply either way</li>
      </ul>
      <a class="btn btn-primary" href="apply.php">Apply to Become a Creator</a>
    </div>
    <div class="become-media">
      <div class="ph-img gDark"><span>Creator Portrait / Film</span></div>
      <div class="become-media-link" aria-label="Creator portrait image">
        <img src="files/images/New_Power_sobrang_final.webp" alt="Creator Portrait" loading="lazy" onerror="this.remove()">
      </div>
    </div>
  </div>
</section>

<!-- REFER & EARN is now a MODAL, not an inline section — opened by
     clicking the "Refer & Earn" nav link (header + footer). See the
     .ref-modal-backdrop markup near the other modals (product modal /
     lightbox) further down the page, and the openReferralModal() /
     closeReferralModal() functions in the script block. -->

<!-- ================= HOW IT WORKS ================= -->
<section class="steps" id="how">
  <div class="wrap">
    <div class="section-head">
      <span class="kicker">The Process</span>
      <h2>Three steps to your <em>benefits &amp; gifts</em></h2>
    </div>
    <div class="step-grid">
      <article class="step">
        <span class="step-num">1.</span>
        <h3>Submit Your Application</h3>
        <p><strong>Share your social links</strong> and a short note on your content style through our application form — it takes less than five minutes, and there's no cost to apply.</p>
      </article>
      <article class="step">
        <span class="step-num">2.</span>
        <h3>Get Verified &amp; Qualified</h3>
        <p><strong>Our team reviews every application personally</strong> — never an algorithm. Once your profile is verified and you qualify for the programme, you'll receive a confirmation email within 1–3 business days.</p>
      </article>
      <article class="step">
        <span class="step-num">3.</span>
        <h3>Receive Your Benefits &amp; Gifts</h3>
        <p><strong>Your gift box and creator benefits from AVÉA Beauty ship straight to you</strong> — full-size products, campaign fees, and monthly commissions from your personal code, all yours as a verified creator.</p>
      </article>
    </div>
  </div>
</section>

<!-- ================= FAQ ================= -->
<section id="faq">
  <div class="wrap">
    <div class="section-head">
      <span class="kicker">Questions &amp; Answers</span>
      <h2>Frequently asked <em>questions</em></h2>
      <p>On the products, the programme, and everything in between.</p>
    </div>
    <div class="cat-tabs" id="faqTabs">
      <button class="cat-tab active" data-faqcat="All">All</button>
      <button class="cat-tab" data-faqcat="Brand">Ask about brand</button>
      <button class="cat-tab" data-faqcat="Collaboration">Ask about collaboration</button>
    </div>

    <div class="faq-list" id="faqList">

      <div class="faq-item" data-faqcat="Brand">
        <button class="faq-q">Are AVÉA products cruelty-free and safe for sensitive skin?</button>
        <div class="faq-a"><p>Yes. All AVÉA products are cruelty-free, FDA-notified, and dermatologist-tested. Our formulas are free from parabens and harsh sulfates, and most are suitable for sensitive skin. Full ingredient lists appear on every box, and every new formula goes through a dedicated patch-test phase with a panel of Filipina testers before it is approved for release. If you have a known allergy or a specific skin concern, we always recommend doing a small patch test on your inner arm 24 hours before first full use, and our customer care team is happy to walk you through the ingredient breakdown of any product on request.</p></div>
      </div>
      <div class="faq-item" data-faqcat="Brand">
        <button class="faq-q">Do the shades flatter morena skin tones?</button>
        <div class="faq-a"><p>They are made for them. Every shade range is developed and tested with Filipina creators across fair, morena, and deep skin tones before launch, so you will always find undertones that flatter. Our complexion products are built with warm, neutral, and cool undertone options in mind — not just lighter-to-darker gradients — so morena skin gets shades formulated specifically to complement its natural warmth rather than a shade simply lifted from a lighter range. Our creator community routinely tests new shade drops months before public release, and their feedback directly shapes which undertones make the final cut, and each shade page online includes swatch photos on multiple real skin tones so you can shop with confidence.</p></div>
      </div>
      <div class="faq-item" data-faqcat="Brand">
        <button class="faq-q">How long do the products wear?</button>
        <div class="faq-a"><p>Our lip and complexion pieces are formulated for eight to twelve hours of transfer-resistant wear, built to hold through humid weather, sweat, and long shoot days without the need for constant touch-ups. Skincare such as the Hydra Pro Serum shows visible results within two to four weeks of consistent use, with most users reporting improved hydration and a more even skin tone by the end of the first month. For best results, we recommend applying skincare items on cleansed skin both morning and night, and pairing lip and complexion products with our setting mist for maximum longevity, especially outdoors or under studio lighting.</p></div>
      </div>
      <div class="faq-item" data-faqcat="Collaboration">
        <button class="faq-q">How many followers do I need to become a AVÉA Creator?</button>
        <div class="faq-a"><p>There's no strict follower minimum — even accounts below 1,000 followers are welcome to apply, as long as you're genuinely willing to create and share honest content. Engagement and the quality of your content matter far more to us than follower count — nano creators with an active, loyal following are very welcome and are often prioritized over larger accounts with low engagement. When we review applications, we look at your content style, consistency of posting, audience interaction rate, and how well your aesthetic aligns with the AVÉA brand, so a smaller but highly engaged and clean, on-brand feed can absolutely stand out during review.</p></div>
      </div>
      <div class="faq-item" data-faqcat="Collaboration">
        <button class="faq-q">Is the collaboration truly free? Do I need to purchase anything?</button>
        <div class="faq-a"><p>You never pay anything. Accepted creators receive press packages at no cost, and paid campaigns come with clearly stated rates that are shared with you in writing before any content is created. A legitimate brand collaboration will never ask you to purchase products, pay a membership or "activation" fee, or cover shipping costs to receive your kit. If anyone reaches out claiming to represent AVÉA and asks for payment, gift cards, or personal banking details upfront, please report it to us immediately through our official application form — it is not a real offer from our team.</p></div>
      </div>
      <div class="faq-item" data-faqcat="Collaboration">
        <button class="faq-q">How and when are creators paid?</button>
        <div class="faq-a"><p>Campaign fees are settled by bank transfer or e-wallet (GCash and Maya supported) within fifteen days of content approval, and you will receive an email confirmation once payment has been sent. Affiliate commissions from your personal promo code are tracked in real time in your creator dashboard, where you can monitor clicks, conversions, and pending earnings, and are paid out monthly as long as the minimum payout threshold has been reached. If a payment is ever delayed beyond the stated window, our creator support team will proactively notify you with an updated timeline.</p></div>
      </div>
      <div class="faq-item" data-faqcat="Collaboration">
        <button class="faq-q">May I continue working with other beauty brands?</button>
        <div class="faq-a"><p>Yes. Our standard programme carries no exclusivity requirement, so you are free to work with other beauty brands, including direct competitors, at the same time as your AVÉA collaboration. We only ask that competing brand content is not published on the same day as your AVÉA campaign deliverables, to keep both partnerships looking clean and intentional on your feed. Should a specific campaign ever require a short exclusivity window — for example, around a major product launch — this will always be discussed and agreed with you in advance, never assumed.</p></div>
      </div>
      <div class="faq-item" data-faqcat="Collaboration">
        <button class="faq-q">What happens after I apply?</button>
        <div class="faq-a"><p>Applications are reviewed within 1–3 days by our creator relations team, who verify your profile, content samples, and audience fit. Once you are verified and qualified, you will receive onboarding details by email, and your benefits and gifts from AVÉA Beauty — including your first creator kit — typically ship within one to two weeks after that. If it is not yet a match, we will let you know by email rather than leave you wondering, and you are welcome to reapply after ninety days — many creators are accepted on a second application once their content or audience has grown.</p></div>
      </div>

    </div>
  </div>
</section>

<!-- ================= FINAL CTA ================= -->
<div class="cta-section">
  <div class="cta-band">
    <span class="kicker">Now Recruiting Content Creators</span>
    <h2>Ready to bloom <em>with AVÉA?</em></h2>
    <p>Submit your application in under five minutes. Once you're verified and qualified, your benefits and gifts from AVÉA Beauty will be on their way.</p>
    <a class="btn btn-white" href="apply.php">Apply to Become a Creator</a>
  </div>
</div>

<!-- ================= FOOTER ================= -->
<footer>
  <div class="foot-grid">
    <div>
      <a class="logo" href="#top">AVÉA<span>.</span></a>
      <p>Clean, feminine beauty made in the Philippines — created with, and for, a community of creators.</p>
    </div>
    <div>
      <h4>Explore</h4>
      <ul>
        <li><a href="#products">Products</a></li>
        <li><a href="#trending">Trending</a></li>
        <li><a href="#kit">The Creator Kit</a></li>
        <li><a href="#faq">FAQ</a></li>
      </ul>
    </div>
    <div>
      <h4>Creators</h4>
      <ul>
        <li><a href="apply.php">Apply Now</a></li>
        <li><a href="#become">Become a Creator</a></li>
        <li><a href="#" onclick="openReferralModal();return false;">Refer &amp; Earn</a></li>
        <li><a href="#how">How It Works</a></li>
      </ul>
    </div>
    <div>
      <h4>Connect</h4>
      <ul>
        <li><a href="#">Instagram</a></li>
        <li><a href="#">TikTok</a></li>
        <li><a href="#">Facebook</a></li>
      </ul>
    </div>
  </div>
  <div class="foot-bottom">
    <span>© 2026 AVÉA Beauty. All rights reserved.</span>
    <span>Made in the Philippines</span>
  </div>
</footer>

<!-- ================= PRODUCT MODAL (with gallery) ================= -->
<div class="modal-backdrop" id="modalBackdrop" onclick="if(event.target===this)closeModal()">
  <div class="modal" role="dialog" aria-modal="true">
    <button class="modal-close" aria-label="Close" onclick="closeModal()">✕</button>
    <div class="modal-gallery">
      <div class="g-main" id="gMain" onclick="openLightbox()">
        <img id="gMainImg" src="" alt="">
        <span class="g-zoom-hint">Click to zoom</span>
      </div>
      <div class="g-thumbs" id="gThumbs"></div>
    </div>
    <div class="modal-body">
      <p class="cat" id="mCat"></p>
      <h3 id="mName"></h3>
      <p><span class="stars" id="mStars"></span> <span class="rating-count" id="mRating"></span></p>
      <div class="modal-price">
        <span class="price" id="mPrice"></span>
        <span class="price-old" id="mOld"></span>
        <span class="save-tag" id="mSave"></span>
      </div>
      <p class="long" id="mDesc"></p>
      <h4>Key Benefits</h4>
      <ul id="mBenefits"></ul>
      <div class="modal-cta">
        <a class="btn btn-primary" href="apply.php">Feature This as a Creator</a>
        <button class="btn btn-outline" onclick="closeModal()">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- ================= LIGHTBOX (close-up) ================= -->
<div class="lightbox" id="lightbox" onclick="closeLightbox()">
  <button class="lightbox-close" aria-label="Close zoom" onclick="closeLightbox()">✕</button>
  <img id="lightboxImg" src="" alt="">
  <span class="lightbox-hint">Click anywhere to close</span>
</div>

<!-- =========================================================
 REFER & EARN MODAL — Collab / referral program
 -----------------------------------------------------------
 Opened only via the "Refer & Earn" nav link (header + footer),
 using openReferralModal() / closeReferralModal() below. It does
 NOT live inline on the page.

 Paano gumagana ang buong flow (end-to-end):

 A) SHARING YOUR OWN CODE (bagong bahagi — #refOwnBox)
    1. Sinumang bumisita dito na wala pang code sa localStorage
       key na "aveaOwnReferralCode" ay makikita lang ang
       #refOwnEmpty state -> "Apply First to Get Your Link".
    2. PAGKATAPOS ng SUCCESSFUL na pag-submit sa apply.php (susunod
       na step, hindi pa ginagawa dito), dapat gumawa ang apply.php
       ng sariling referral code para sa applicant, tapos i-save
       ito sa browser gamit ang inline script bago mag-redirect o
       sa success/thank-you page:
         localStorage.setItem('aveaOwnReferralCode', 'AVEA-TITE934');
         localStorage.setItem('aveaOwnReferralName', 'Tite Baluktot');
    3. Sa susunod na balik niya rito at buksan ang modal na ito,
       makikita na niya agad ang sarili niyang shareable link sa
       #refOwnActive state, kasama ng Copy button.
    (Buong spec para sa referral code format at apply.php logic:
    hiningi ito sa chat bilang hiwalay na prompt/spec.)

 B) APPLYING WITH SOMEONE ELSE'S CODE (dati nang bahagi — .ref-entry)
    1. Ang taong may hawak ng code (galing sa kaibigan niyang
       nag-share ng sariling link/code) ay ipinapaste/i-type ang
       code sa kahon sa ibaba, pindutin ang "Continue to
       Application" -> pupunta sa apply.php?ref=CODE.
    2. SUSUNOD NA STEP (sa apply.php, hindi pa ginagawa dito):
       - Basahin ang $_GET['ref'] pagdating sa apply.php
       - I-populate ang isang <input name="referral_code" readonly>
         gamit ang value na galing sa URL, para hindi na ito
         mababago pa ng applicant
       - Kung walang ?ref= sa URL, puwedeng iwan itong blangko/
         optional (hindi required ang referral para makapag-apply)
       - I-save ang referral_code kasama ng application papunta sa
         database, para malaman kung sinong nag-invite
       - Pagka-verify/approve ng application na iyon, doon lang
         dapat kredit sa nag-invite (tier progress) — hindi agad
         pagka-submit lang ng application
========================================================= -->
<div class="ref-modal-backdrop" id="referralModalBackdrop" onclick="if(event.target===this)closeReferralModal()">
  <div class="ref-modal" role="dialog" aria-modal="true" aria-label="Refer and earn programme">
    <button class="modal-close" aria-label="Close" onclick="closeReferralModal()">✕</button>

    <div class="section-head">
      <span class="kicker">Invite &amp; Earn</span>
      <h2>Bring a creator in, <em>unlock more</em></h2>
      <p>Know another creator who'd be perfect for AVÉA? Invite them with your referral code. Once they apply and get verified &amp; qualified, you both move forward — and the more creators you bring in, the more your own benefits grow.</p>
    </div>

    <!-- ---------- YOUR OWN REFERRAL LINK ----------
     Dalawang state, JS ang nagpapasya kung alin ang lalabas
     (tingnan ang renderOwnReferralStatus() sa script):
     - #refOwnActive  = meron na siyang sariling code (galing sa
       localStorage, na sina-set ng apply.php pagkatapos ng
       successful submission — see comment block sa taas ng
       .ref-modal-backdrop para sa buong spec).
     - #refOwnEmpty   = wala pang code, kailangan mag-apply muna.
     Parehas silang naka-render sa HTML, JS lang ang nagtatago/
     nagpapakita depende kung may laman ang localStorage. -->
    <div class="ref-own" id="refOwnBox">
      <div class="ref-own-active" id="refOwnActive" style="display:none;">
        <p class="ref-own-label">Your Referral Link</p>
        <p class="ref-own-hi" id="refOwnHi">Share this with other creators:</p>
        <div class="ref-own-row">
          <input type="text" id="refOwnLinkInput" readonly aria-label="Your referral link">
          <button class="btn btn-outline" id="refOwnCopyBtn" onclick="copyOwnReferralLink()">Copy</button>
        </div>
        <p class="ref-own-copied" id="refOwnCopied">Copied!</p>
      </div>
      <div class="ref-own-empty" id="refOwnEmpty">
        <p class="ref-own-label">Your Referral Link</p>
        <p>Submit your creator application first to unlock your own referral link — once you've applied, you'll be able to share it right here with other creators.</p>
        <a class="btn btn-primary" href="apply.php">Apply First to Get Your Link</a>
      </div>
    </div>

    <div class="ref-tiers">
      <div class="ref-tier">
        <span class="ref-badge">Tier 1 · Connector</span>
        <h3>1–4 Verified Invites</h3>
        <ul>
          <li><strong>₱300 cash reward</strong> per verified invite who gets approved</li>
          <li>Your invite gets <strong>₱250 off</strong> their first press box — you both win</li>
          <li><strong>10% commission</strong> on every sale from your referral code</li>
          <li>Early access to the next campaign brief</li>
        </ul>
      </div>
      <div class="ref-tier featured">
        <span class="ref-badge">Tier 2 · Curator</span>
        <h3>5–9 Verified Invites</h3>
        <ul>
          <li>Everything in Tier 1, plus:</li>
          <li><strong>₱500 cash reward</strong> per verified invite (raised from ₱300)</li>
          <li><strong>15% commission</strong> on your referral code, ongoing</li>
          <li>One <strong>free full-size product</strong> added to your next press box</li>
          <li>Priority review on future applications you send</li>
        </ul>
      </div>
      <div class="ref-tier">
        <span class="ref-badge">Tier 3 · Ambassador</span>
        <h3>10+ Verified Invites</h3>
        <ul>
          <li>Everything in Tier 2, plus:</li>
          <li><strong>₱800 cash reward</strong> per verified invite (raised from ₱500)</li>
          <li><strong>20% commission</strong> on your referral code, ongoing</li>
          <li>Exclusive <strong>Ambassador PR box</strong> every launch, free</li>
          <li>Priority invitations to studio days &amp; product launches</li>
        </ul>
      </div>
    </div>

    <!-- Itinatago ang buong section na ito sa renderOwnReferralStatus()
         kapag may SARILING referral code na ang user (nag-apply na siya),
         dahil hindi na siya puwedeng gumamit ng code ng iba. -->
    <div class="ref-entry" id="refEntrySection">
      <p class="ref-own-label" style="margin-bottom:10px;">Have Someone Else's Code?</p>
      <p class="ref-lead">Got a referral code from an AVÉA creator? Enter it below to link your application to them.</p>
      <div class="ref-form">
        <input type="text" id="refCodeInput" placeholder="Enter referral code, e.g. AVEA-JANA24" aria-label="Referral code" onkeydown="if(event.key==='Enter'){event.preventDefault();proceedWithReferral();}">
        <button class="btn btn-primary" onclick="proceedWithReferral()">Continue to Application</button>
      </div>
      <p class="ref-or">Don't have a code? <a href="apply.php">Apply without one</a></p>
    </div>
  </div>
</div>

<script>
/* -------------------------------------------------------------
   HASH REFERRAL LINK FIX / FALLBACK
   Kapag na-paste ang lumang sirang link na may
   "#referral/apply.php?ref=CODE" (o kahit anong hash na may
   apply.php?ref= sa loob), i-redirect agad sa totoong
   apply.php?ref=CODE para diretso na sa application form na
   naka-auto-fill ang referral code, imbes na maiwan dito sa
   index.php (dahil fragment lang lahat pagkatapos ng "#").
------------------------------------------------------------- */
(function () {
  const m = window.location.hash.match(/apply\.php\?ref=([^&#]+)/i);
  if (m) {
    window.location.replace("apply.php?ref=" + m[1]);
  }
})();

/* =============================================================
   PRODUCT DATA
   - IMG_BASE: folder ng product images mo. Palitan kung iba.
   - img: pangunahing larawan; hoverImg: pangalawang larawan
     (null kung wala, gaya ng Smart All Over Powder Brush).
   - price: kasalukuyang presyo; old: dating presyo.
   - fb: fallback gradient kapag hindi ma-load ang image.
============================================================= */
const IMG_BASE = "files/images/products/";

const PRODUCTS = [
  /* ---------------- LIPS ---------------- */
  {id:1, name:"3D Hydra Lip Gloss", cat:"Lips", price:1300, old:1600, badge:"Bestseller",
   img:"3dHydraLipgloss1.png", hoverImg:"3dHydraLipgloss2.png", fb:"g1", stars:5, ratings:"1,164 reviews",
   desc:"A cushioning gloss with dimensional, mirror-like shine.",
   long:"A plush, non-sticky gloss that wraps lips in dimensional shine while hyaluronic spheres keep them cushioned and hydrated for hours. Worn alone or over colour, it is the finishing gesture of every AVÉA look.",
   benefits:["Mirror-like, non-sticky shine","Hyaluronic hydration for hours","Comfortable cushioned feel","Wears alone or over any lip colour"]},
  {id:2, name:"Unlimited Double Touch", cat:"Lips", price:1400, old:1750, badge:null,
   img:"unlimiteddoubletouch1.png", hoverImg:"unlimiteddoubletouch2.png", fb:"g4", stars:5, ratings:"927 reviews",
   desc:"A two-step liquid lip colour with extreme staying power.",
   long:"A liquid colour base and glossy top coat in one piece: apply the colour, let it set, then seal with the top coat for up to twelve hours of transfer-proof wear that never cracks or dries.",
   benefits:["Up to twelve hours of wear","Transfer-proof two-step system","Glossy finish without stickiness","Comfortable, flexible feel"]},
  {id:3, name:"Lip Balm", cat:"Lips", price:600, old:750, badge:null,
   img:"lipbalm1.png", hoverImg:"lipbalm2.png", fb:"g1", stars:4, ratings:"512 reviews",
   desc:"Daily nourishment for soft, conditioned lips.",
   long:"A melting balm of shea butter and vitamin E that softens, smooths, and protects. The quiet essential beneath every lip look — and a beautiful finish on its own.",
   benefits:["Shea butter and vitamin E","Immediate, lasting softness","Preps lips for colour","Subtle, natural sheen"]},
  {id:4, name:"Smart Fusion Lip Pencil", cat:"Lips", price:450, old:550, badge:null,
   img:"smartfusionlippencil1.png", hoverImg:"smartfusionlippencil2.png", fb:"g4", stars:4, ratings:"689 reviews",
   desc:"A creamy pencil that defines and fills with ease.",
   long:"A soft, blendable pencil that glides on without tugging — precise enough to define, creamy enough to fill the entire lip. The quiet architect of a perfect lip line.",
   benefits:["Glides without tugging","Defines and fills in one","Blends seamlessly with colour","Long-wearing creamy formula"]},
  {id:5, name:"Creamy Comfort Lip Liner", cat:"Lips", price:1000, old:1250, badge:null,
   img:"creamycomfortlipliner1.png", hoverImg:"creamycomfortlipliner2.png", fb:"g1", stars:5, ratings:"341 reviews",
   desc:"Comfort-first definition with a velvet finish.",
   long:"An ultra-creamy liner that hugs the lip contour with a velvet finish, preventing feathering while keeping lips comfortable from morning through evening.",
   benefits:["Velvet, comfort-first texture","Prevents feathering all day","Rich, even colour laydown","Sharpens to a fine point"]},
  {id:6, name:"Invisible Lip Liner", cat:"Lips", price:900, old:1100, badge:null,
   img:"invisiblelipliner1.png", hoverImg:"invisiblelipliner2.png", fb:"g4", stars:4, ratings:"278 reviews",
   desc:"A clear liner that locks any lip colour in place.",
   long:"A transparent, waxy liner that creates an invisible barrier around the lips — locking any shade in place and stopping feathering before it begins. One pencil for your entire lip wardrobe.",
   benefits:["Works with every lip shade","Invisible anti-feathering barrier","Smooths fine lines around lips","Waterproof, all-day hold"]},

  /* ---------------- EYES ---------------- */
  {id:7, name:"Essential Dossier Colour Kajal", cat:"Eyes", price:800, old:980, badge:null,
   img:"essentialdossiercolourkajal1.png", hoverImg:"essentialdossiercolourkajal2.png", fb:"g5", stars:4, ratings:"433 reviews",
   desc:"Intense colour kajal for the waterline and beyond.",
   long:"A richly pigmented kajal soft enough for the waterline yet intense enough for a full graphic line. One stroke of saturated colour that holds through humid days.",
   benefits:["Safe and soft on the waterline","Saturated colour in one stroke","Smudge-resistant once set","Blends for smoky looks"]},
  {id:8, name:"Lasting Precision Automatic Eyeliner & Khol", cat:"Eyes", price:950, old:1150, badge:null,
   img:"lastingprecisionautomaticeyelinerkhol1.png", hoverImg:"lastingprecisionautomaticeyelinerkhol2.png", fb:"g5", stars:5, ratings:"602 reviews",
   desc:"An automatic liner of extreme precision and hold.",
   long:"A twist-up liner with a precision tip that draws the finest lines and the boldest wings alike — then refuses to move for up to fourteen hours.",
   benefits:["Up to fourteen hours of hold","Precision tip, no sharpening","Doubles as liner and khol","Waterproof and sweat-resistant"]},
  {id:9, name:"Super Colour Waterproof Eyeliner", cat:"Eyes", price:880, old:1050, badge:null,
   img:"supercolourwaterproofeyeliner1.png", hoverImg:"supercolourwaterproofeyeliner2.png", fb:"g3", stars:4, ratings:"356 reviews",
   desc:"Vivid waterproof colour that will not budge.",
   long:"High-impact colour in a waterproof formula built for the tropics — vivid from first stroke to final hour, through heat, humidity, and everything the day brings.",
   benefits:["Fully waterproof formula","Vivid, high-impact pigment","Glides on without skipping","Built for humid weather"]},
  {id:10, name:"Twistable Mascara", cat:"Eyes", price:1200, old:1450, badge:"Bestseller",
   img:"twistablemascara1.png", hoverImg:"twistablemascara2.png", fb:"g5", stars:5, ratings:"881 reviews",
   desc:"One mascara, adjustable from natural to dramatic.",
   long:"Twist the collar to transform the brush — from a defined, natural fan to full dramatic volume. One mascara for every mood, with zero clumps and zero smudging.",
   benefits:["Adjustable brush, two looks","Zero clumping or smudging","Buildable volume and length","Holds a curl all day"]},
  {id:11, name:"Eyebrow Marker (No-Transfer Natural Tattoo)", cat:"Eyes", price:950, old:1150, badge:null,
   img:"eyebrowmarker1.png", hoverImg:"eyebrowmarker2.png", fb:"g8", stars:4, ratings:"294 reviews",
   desc:"Hair-like strokes that stay put like a tattoo.",
   long:"A micro-tip marker that draws individual, hair-like strokes with a no-transfer tattoo effect — brows that survive workouts, weather, and long days on camera.",
   benefits:["Realistic hair-like strokes","No-transfer tattoo effect","Lasts through sweat and humidity","Micro-tip for full control"]},

  /* ---------------- FACE ---------------- */
  {id:12, name:"Make Up Fixer", cat:"Face", price:1400, old:1700, badge:null,
   img:"makeupfixer1.png", hoverImg:"makeupfixer2.png", fb:"g2", stars:5, ratings:"723 reviews",
   desc:"A weightless mist that locks makeup in place.",
   long:"A fine, weightless mist that sets the entire look in a single veil — extending wear for hours while keeping skin fresh, never tight or shiny.",
   benefits:["Extends makeup wear for hours","Fine, even micro-mist","No tightness, no shine","Refreshes throughout the day"]},
  {id:13, name:"Sculpting Touch Creamy Stick Contour", cat:"Face", price:1400, old:1650, badge:null,
   img:"sculptingtouchcreamystickcontour1.png", hoverImg:"sculptingtouchcreamystickcontour2.png", fb:"g8", stars:4, ratings:"388 reviews",
   desc:"Creamy, blendable sculpting in a single stroke.",
   long:"A creamy contour stick that melts into skin with a fingertip — natural shadow and structure without a hard edge, in shades composed for Filipina undertones.",
   benefits:["Melts in with a fingertip","Natural, buildable shadow","No hard edges or patchiness","Undertones for morena skin"]},
  {id:14, name:"Full Coverage 2-in-1 Foundation & Concealer", cat:"Face", price:2000, old:2400, badge:"Fan Favourite",
   img:"fullcoverage2in1foundationconcealer1.png", hoverImg:"fullcoverage2in1foundationconcealer2.png", fb:"g4", stars:5, ratings:"1,046 reviews",
   desc:"Foundation and concealer in a single gesture.",
   long:"Buildable full coverage that conceals as it perfects — one piece, two purposes. Photographs flawlessly, holds through the longest shoot days, and never settles into fine lines.",
   benefits:["Full coverage, two purposes","Photographs without flashback","Does not settle into lines","All-day transfer resistance"]},
  {id:15, name:"Instamoisture Foundation", cat:"Face", price:1280, old:1500, badge:null,
   img:"instamoisturefoundation1.png", hoverImg:"instamoisturefoundation2.png", fb:"g2", stars:4, ratings:"567 reviews",
   desc:"Skincare-infused coverage with a fresh finish.",
   long:"A serum-like foundation that hydrates as it evens — medium coverage with a fresh, skin-like finish that lasts through warm, humid days.",
   benefits:["Hydrating serum-like texture","Fresh, skin-like finish","Medium buildable coverage","Comfortable in humid weather"]},
  {id:16, name:"Instamoisture Glow Foundation", cat:"Face", price:1400, old:1650, badge:null,
   img:"instamoistureglowfoundation1.png", hoverImg:"instamoistureglowfoundation2.png", fb:"g2", stars:5, ratings:"441 reviews",
   desc:"Luminous hydrating coverage, lit from within.",
   long:"The radiant sister of our Instamoisture line — the same skincare-infused comfort, finished with a soft luminosity that reads as glow, never as shine.",
   benefits:["Soft, lit-from-within glow","Hydrates while it covers","Never greasy or shiny","Blends with brush or sponge"]},
  {id:17, name:"Glow Fusion Highlighting Drops", cat:"Face", price:1100, old:1350, badge:null,
   img:"glowfusionhighlightingdrops1.png", hoverImg:"glowfusionhighlightingdrops2.png", fb:"g2", stars:5, ratings:"329 reviews",
   desc:"Liquid light, in precisely measured drops.",
   long:"Concentrated liquid highlighter to wear alone, mixed into foundation, or tapped onto high points — a glass-like radiance that catches every camera flash beautifully.",
   benefits:["Wear alone or mix into base","Glass-like, refined radiance","A drop is all it takes","No visible glitter particles"]},

  /* ---------------- HANDS & NAILS ---------------- */
  {id:18, name:"Self Tan Serum", cat:"Hands & Nails", price:1280, old:1500, badge:null,
   img:"selftanserum1.png", hoverImg:"selftanserum2.png", fb:"g8", stars:4, ratings:"187 reviews",
   desc:"A gradual, streak-free golden glow.",
   long:"A lightweight self-tanning serum that builds a natural, golden warmth over days — streak-free, transfer-resistant once developed, and kind to skin.",
   benefits:["Gradual, buildable warmth","Streak-free application","Hydrating serum base","Natural golden undertone"]},

  /* ---------------- SKINCARE ---------------- */
  {id:19, name:"Sun Protection Face Cream SPF 50", cat:"Skincare", price:1400, old:1700, badge:null,
   img:"sunprotectionfacecreamspf501.png", hoverImg:"sunprotectionfacecreamspf502.png", fb:"g6", stars:5, ratings:"812 reviews",
   desc:"High protection that disappears into skin.",
   long:"Broad-spectrum SPF 50 in a weightless cream that leaves no white cast and no greasy veil — daily protection that sits beautifully beneath makeup.",
   benefits:["Broad-spectrum SPF 50","No white cast on any skin tone","Sits beautifully under makeup","Weightless, non-greasy feel"]},
  {id:20, name:"Hydra Pro Glow", cat:"Skincare", price:2150, old:2500, badge:"Bestseller",
   img:"hydraproglow1.png", hoverImg:"hydraproglow2.png", fb:"g6", stars:5, ratings:"934 reviews",
   desc:"Intensive hydration with a luminous finish.",
   long:"An intensive hydrating treatment that leaves skin visibly plumper, smoother, and lit from within — the piece our creators call their close-up secret.",
   benefits:["Visibly plumper skin","Luminous, healthy finish","Deep, lasting hydration","Layers under any routine"]},
  {id:21, name:"Skin Trainer Youth-Generating Serum", cat:"Skincare", price:2500, old:2950, badge:null,
   img:"skintraineryouthgeneratingserum1.png", hoverImg:"skintraineryouthgeneratingserum2.png", fb:"g3", stars:5, ratings:"456 reviews",
   desc:"Trains skin toward firmness and bounce.",
   long:"A concentrated serum that supports skin's own renewal — improving visible firmness, elasticity, and texture with consistent use over four to eight weeks.",
   benefits:["Improves visible firmness","Supports elasticity and bounce","Refines texture over time","Suitable for nightly use"]},
  {id:22, name:"Hydra Pro Serum", cat:"Skincare", price:2300, old:2700, badge:null,
   img:"hydraproserum1.png", hoverImg:"hydraproserum2.png", fb:"g6", stars:5, ratings:"673 reviews",
   desc:"Multi-weight hyaluronic hydration, layer by layer.",
   long:"Multiple weights of hyaluronic acid draw and hold moisture at every level of the skin — an immediate quench with results that build day after day.",
   benefits:["Multi-weight hyaluronic acid","Immediate and lasting hydration","Plumps fine dehydration lines","Feather-light, fast-absorbing"]},
  {id:23, name:"Radiance Boost Serum Face Base", cat:"Skincare", price:1500, old:1800, badge:null,
   img:"radianceboostserumfacebase1.png", hoverImg:"radianceboostserumfacebase2.png", fb:"g2", stars:4, ratings:"301 reviews",
   desc:"A serum-primer that begins the glow.",
   long:"Part serum, part primer — a radiance-boosting base that smooths, grips makeup, and lends skin a soft luminosity before a single drop of foundation.",
   benefits:["Serum care, primer grip","Soft, natural luminosity","Extends foundation wear","Smooths texture and pores"]},

  /* ---------------- ACCESSORIES ---------------- */
  {id:24, name:"Make Up Blender", cat:"Accessories", price:850, old:1000, badge:null,
   img:"makeupblender1.png", hoverImg:"", fb:"g7", stars:5, ratings:"519 reviews",
   desc:"A latex-free sponge for a seamless base.",
   long:"A supple, latex-free blender that doubles in size when damp — pressing foundation into a seamless, airbrushed finish without drinking your product.",
   benefits:["Latex-free, gentle on skin","Airbrushed, streak-free finish","Absorbs less product","Precision tip for corners"]},
  {id:25, name:"Travel Brush Set", cat:"Accessories", price:1850, old:2200, badge:"New",
   img:"travelbrushset1.png", hoverImg:"travelbrushset2.png", fb:"g8", stars:5, ratings:"228 reviews",
   desc:"The complete look, wherever you create.",
   long:"The complete architecture of a look in one considered travel case — densely set vegan brushes for base, cheeks, and eyes that neither shed nor drink your product.",
   benefits:["Complete face and eye set","Fully vegan bristles","No shedding, easy to clean","Presented in a travel case"]},
  {id:26, name:"Face 03 Flat Foundation Brush", cat:"Accessories", price:1240, old:1450, badge:null,
   img:"face03flatfoundationbrush1.png", hoverImg:"face03flatfoundationbrush2.png", fb:"g7", stars:4, ratings:"176 reviews",
   desc:"A classic flat brush for precise, even base.",
   long:"A classic flat brush that lays liquid and cream foundation with precision and control — full coverage where you want it, sheered out where you do not.",
   benefits:["Precise, controlled application","Works with liquids and creams","Streak-free even laydown","Firm yet soft bristles"]},
  {id:27, name:"Face 13 Kabuki Brush", cat:"Accessories", price:1400, old:1650, badge:null,
   img:"face13kabukibrush1.png", hoverImg:"face13kabukibrush2.png", fb:"g7", stars:5, ratings:"203 reviews",
   desc:"A dense kabuki for buffed, airbrushed skin.",
   long:"A densely set kabuki that buffs powder and liquid alike into an airbrushed second skin — the brush that makes any base look expensive.",
   benefits:["Buffs to an airbrushed finish","For powders and liquids","Ultra-dense, ultra-soft head","Comfortable short handle"]},
  {id:28, name:"Smart All Over Powder Brush", cat:"Accessories", price:1200, old:1400, badge:null,
   img:"smartalloverpowderbrush1.png", hoverImg:null, fb:"g7", stars:4, ratings:"142 reviews",
   desc:"One fluffy brush for every powder step.",
   long:"A generously fluffy brush that sweeps setting powder, bronzer, and finishing veils across the face in soft, even passes — the one brush that does it all.",
   benefits:["One brush, every powder","Soft, even diffusion","Generous fluffy head","Tapers for light contouring"]},
  {id:29, name:"Face 10 Blush Brush", cat:"Accessories", price:1550, old:1800, badge:null,
   img:"face10blushbrush1.png", hoverImg:"face10blushbrush2.png", fb:"g7", stars:5, ratings:"167 reviews",
   desc:"Angled softness for a perfectly placed flush.",
   long:"An angled brush shaped to the curve of the cheek — picking up just enough colour and diffusing it into a flush that looks born there, not brushed on.",
   benefits:["Angled to hug the cheekbone","Diffuses colour seamlessly","Ideal pigment pickup","For powder and cream blush"]},
];

const CATEGORIES = ["All","Lips","Eyes","Face","Hands & Nails","Skincare","Accessories"];

/* ---------- helpers ---------- */
const peso = n => "₱" + n.toLocaleString("en-PH");
const starStr = n => "★".repeat(n) + "☆".repeat(5-n);

function cardHTML(p){
  const hover = p.hoverImg
    ? `<img class="img-hover" src="${IMG_BASE + p.hoverImg}" alt="" loading="lazy" onerror="this.remove()">`
    : "";
  return `
  <article class="product-card" onclick="openModal(${p.id})" tabindex="0" role="button"
           aria-label="View details of ${p.name}"
           onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openModal(${p.id});}">
    <div class="product-img">
      ${p.badge ? `<span class="badge">${p.badge}</span>` : ""}
      <div class="img-fallback ${p.fb}"></div>
      <img class="img-main" src="${IMG_BASE + p.img}" alt="${p.name}" loading="lazy" onerror="this.remove()">
      ${hover}
      <div class="hover-veil"><span>More Details</span></div>
    </div>
    <div class="product-info">
      <p class="cat">${p.cat}</p>
      <h3>${p.name}</h3>
      <p class="desc">${p.desc}</p>
      <p><span class="stars">${starStr(p.stars)}</span> <span class="rating-count">${p.ratings}</span></p>
      <div class="price-row">
        <span class="price">${peso(p.price)}</span>
        ${p.old ? `<span class="price-old">${peso(p.old)}</span>` : ""}
      </div>
    </div>
  </article>`;
}

/* ---------- category tabs + grid ---------- */
const tabsEl = document.getElementById("catTabs");
const gridEl = document.getElementById("productGrid");
const showMoreWrap = document.getElementById("showMoreWrap");
const showMoreBtn = document.getElementById("showMoreBtn");
let activeCat = "All";
let productsExpanded = false;

function renderTabs(){
  tabsEl.innerHTML = CATEGORIES.map((c,i) =>
    `<button class="cat-tab ${c===activeCat?'active':''}" onclick="setCat(${i})">${c}</button>`).join("");
}
function setCat(i){ activeCat = CATEGORIES[i]; productsExpanded = false; renderTabs(); renderGrid(); }
function renderGrid(){
  const list = activeCat==="All" ? PRODUCTS : PRODUCTS.filter(p=>p.cat===activeCat);
  // Kapag naka "show less": ipakita lang ang kalahati (min 4). Kapag "show more": ipakita lahat.
  const collapsedCount = Math.max(4, Math.ceil(list.length / 2));
  const visible = productsExpanded ? list : list.slice(0, collapsedCount);
  gridEl.innerHTML = visible.map(cardHTML).join("");

  if (list.length <= collapsedCount){
    showMoreWrap.style.display = "none";
  } else {
    showMoreWrap.style.display = "";
    showMoreBtn.textContent = productsExpanded ? "Show Less" : "Show More";
  }
}
function toggleShowMore(){
  productsExpanded = !productsExpanded;
  renderGrid();
  if (!productsExpanded){
    document.getElementById("products").scrollIntoView({behavior:"smooth", block:"start"});
  }
}
renderTabs(); renderGrid();

/* ---------- trending banner slideshow ---------- */
const trTrack = document.getElementById("trTrack");
const trDots = document.getElementById("trDots");
const trCount = trTrack.children.length;
let trIndex = 0, trTimer = null;

trDots.innerHTML = Array.from({length:trCount},(_,i)=>
  `<button class="dot ${i===0?'active':''}" aria-label="Go to trending item ${i+1}" onclick="goTrend(${i})"></button>`).join("");

function updateTrend(){
  trTrack.style.transform = `translateX(-${trIndex*100}%)`;
  [...trDots.children].forEach((d,i)=>d.classList.toggle("active", i===trIndex));
}
function moveTrend(dir){ trIndex = (trIndex + dir + trCount) % trCount; updateTrend(); restartTrend(); }
function goTrend(i){ trIndex = i; updateTrend(); restartTrend(); }
function restartTrend(){
  clearInterval(trTimer);
  if (!window.matchMedia("(prefers-reduced-motion: reduce)").matches){
    trTimer = setInterval(()=>{ trIndex=(trIndex+1)%trCount; updateTrend(); }, 7000);
  }
}
restartTrend();

/* ---------- hero slideshow ---------- */
const slidesEl = document.getElementById("heroSlides");
const dotsEl = document.getElementById("heroDots");
const slideCount = slidesEl.children.length;
let heroIndex = 0, heroTimer = null;

dotsEl.innerHTML = Array.from({length:slideCount},(_,i)=>
  `<button class="dot ${i===0?'active':''}" aria-label="Go to slide ${i+1}" onclick="goHero(${i})"></button>`).join("");

function updateHero(){
  slidesEl.style.transform = `translateX(-${heroIndex*100}%)`;
  [...dotsEl.children].forEach((d,i)=>d.classList.toggle("active", i===heroIndex));
}
function moveHero(dir){ heroIndex = (heroIndex + dir + slideCount) % slideCount; updateHero(); restartHero(); }
function goHero(i){ heroIndex = i; updateHero(); restartHero(); }
function restartHero(){
  clearInterval(heroTimer);
  if (!window.matchMedia("(prefers-reduced-motion: reduce)").matches){
    heroTimer = setInterval(()=>{ heroIndex=(heroIndex+1)%slideCount; updateHero(); }, 6000);
  }
}
restartHero();

/* ---------- product modal + gallery ---------- */
const backdrop = document.getElementById("modalBackdrop");
let galleryImages = [];
let galleryIndex = 0;

function openModal(id){
  const p = PRODUCTS.find(x=>x.id===id);
  if(!p) return;

  // Gallery: pangunahing larawan + hover na larawan (kung meron)
  galleryImages = [IMG_BASE + p.img];
  if (p.hoverImg) galleryImages.push(IMG_BASE + p.hoverImg);
  galleryIndex = 0;
  renderGallery(p);

  document.getElementById("mCat").textContent = p.cat;
  document.getElementById("mName").textContent = p.name;
  document.getElementById("mStars").textContent = starStr(p.stars);
  document.getElementById("mRating").textContent = p.ratings;
  document.getElementById("mPrice").textContent = peso(p.price);
  document.getElementById("mOld").textContent = p.old ? peso(p.old) : "";
  const saveEl = document.getElementById("mSave");
  if (p.old){
    const pct = Math.round((1 - p.price/p.old)*100);
    saveEl.textContent = "Save " + pct + "%";
    saveEl.style.display = "inline-block";
  } else {
    saveEl.style.display = "none";
  }
  document.getElementById("mDesc").textContent = p.long;
  document.getElementById("mBenefits").innerHTML = p.benefits.map(b=>`<li>${b}</li>`).join("");

  backdrop.classList.add("show");
  document.body.style.overflow = "hidden";
}

function renderGallery(p){
  const mainImg = document.getElementById("gMainImg");
  mainImg.style.display = "block";
  mainImg.onerror = function(){ this.style.display = "none"; };
  mainImg.src = galleryImages[galleryIndex];
  mainImg.alt = p ? p.name : "";

  const thumbs = document.getElementById("gThumbs");
  thumbs.innerHTML = galleryImages.map((src,i)=>`
    <button class="g-thumb ${i===galleryIndex?'active':''}" onclick="setGallery(${i})" aria-label="View image ${i+1}">
      <img src="${src}" alt="" onerror="this.style.display='none'">
    </button>`).join("");
}
function setGallery(i){
  galleryIndex = i;
  const mainImg = document.getElementById("gMainImg");
  mainImg.style.display = "block";
  mainImg.src = galleryImages[i];
  [...document.getElementById("gThumbs").children].forEach((t,x)=>t.classList.toggle("active", x===i));
}
function closeModal(){
  backdrop.classList.remove("show");
  document.body.style.overflow = "";
}

/* ---------- lightbox (close-up) ---------- */
const lightbox = document.getElementById("lightbox");
function openLightbox(){
  document.getElementById("lightboxImg").src = galleryImages[galleryIndex];
  lightbox.classList.add("show");
}
function closeLightbox(){
  lightbox.classList.remove("show");
}
/* ---------- Refer & Earn modal ---------- */
const referralBackdrop = document.getElementById("referralModalBackdrop");
function openReferralModal(){
  renderOwnReferralStatus();
  referralBackdrop.classList.add("show");
  document.body.style.overflow = "hidden";
}
function closeReferralModal(){
  referralBackdrop.classList.remove("show");
  document.body.style.overflow = "";
}

/* ---------- Your own referral link (own-code detection) -------------
   TEMPORARY client-side check lang ito gamit ang localStorage, hangga't
   walang totoong account/login system. Ang apply.php ang dapat mag-set
   ng dalawang key na ito PAGKATAPOS ng successful application submit:
     localStorage.setItem('aveaOwnReferralCode', 'AVEA-TITE934');
     localStorage.setItem('aveaOwnReferralName', 'Tite Baluktot');
   Tingnan ang buong spec/prompt para dito sa comment block sa itaas ng
   .ref-modal-backdrop markup. Kapag wala pang laman ang key na ito
   (ibig sabihin hindi pa nag-apply), #refOwnEmpty ang lalabas. */
const OWN_REF_CODE_KEY = "aveaOwnReferralCode";
const OWN_REF_NAME_KEY = "aveaOwnReferralName";

function renderOwnReferralStatus(){
  const code = localStorage.getItem(OWN_REF_CODE_KEY);
  const activeBox = document.getElementById("refOwnActive");
  const emptyBox = document.getElementById("refOwnEmpty");
  const entrySection = document.getElementById("refEntrySection");

  /* Kapag may sariling code na (tapos na mag-apply), itago ang
     "Have Someone Else's Code?" section — wala nang saysay na
     mag-enter pa siya ng code ng iba. */
  if (entrySection) entrySection.style.display = code ? "none" : "";

  if (code){
    const name = localStorage.getItem(OWN_REF_NAME_KEY) || "";
    document.getElementById("refOwnHi").textContent = name
      ? `Hi ${name.split(" ")[0]}! Share this with other creators:`
      : "Share this with other creators:";
    /* Gumamit ng origin + pathname LANG (walang #hash o ?query) para
       hindi madala ang "#referral/..." sa shareable link. Dati kasi
       window.location.href ang ginagamit — kapag may hash ang URL,
       naisasama ito at nagiging ".../#referral/apply.php?ref=..."
       ang link, na hindi nagre-redirect dahil fragment lang lahat
       pagkatapos ng "#". */
    let base = window.location.origin + window.location.pathname;
    base = base.replace(/index\.php.*$/, "").replace(/\/?$/, "/");
    document.getElementById("refOwnLinkInput").value = base + "apply.php?ref=" + encodeURIComponent(code);
    activeBox.style.display = "";
    emptyBox.style.display = "none";
  } else {
    activeBox.style.display = "none";
    emptyBox.style.display = "";
  }
}

function copyOwnReferralLink(){
  const input = document.getElementById("refOwnLinkInput");
  input.focus();
  input.select();
  const done = () => {
    const msg = document.getElementById("refOwnCopied");
    msg.classList.add("show");
    setTimeout(() => msg.classList.remove("show"), 1800);
  };
  if (navigator.clipboard && navigator.clipboard.writeText){
    navigator.clipboard.writeText(input.value).then(done).catch(() => {
      document.execCommand("copy");
      done();
    });
  } else {
    document.execCommand("copy");
    done();
  }
}

document.addEventListener("keydown", e=>{
  if(e.key==="Escape"){
    if (lightbox.classList.contains("show")) closeLightbox();
    else if (referralBackdrop.classList.contains("show")) closeReferralModal();
    else closeModal();
  }
});

/* ---------- Refer & Earn: send referral code to apply.php ----------
   NOTE: apply.php should read $_GET['ref'] and put it into a
   readonly input (name="referral_code") so it can't be edited
   after arriving from this link. See comment block above the
   .ref-modal-backdrop markup in the HTML for the full backend flow. */
function proceedWithReferral(){
  const input = document.getElementById("refCodeInput");
  const code = input.value.trim();
  window.location.href = code
    ? "apply.php?ref=" + encodeURIComponent(code)
    : "apply.php";
}

/* ---------- FAQ category tabs ---------- */
const faqTabsEl = document.getElementById("faqTabs");
if (faqTabsEl){
  faqTabsEl.addEventListener("click", e=>{
    const btn = e.target.closest(".cat-tab");
    if (!btn) return;
    const cat = btn.dataset.faqcat;
    [...faqTabsEl.children].forEach(t=>t.classList.toggle("active", t===btn));
    document.querySelectorAll("#faqList .faq-item").forEach(item=>{
      const show = cat==="All" || item.dataset.faqcat===cat;
      item.style.display = show ? "" : "none";
      if (!show && item.classList.contains("open")){
        item.classList.remove("open");
        item.querySelector(".faq-a").style.maxHeight = null;
      }
    });
  });
}

/* ---------- FAQ accordion ---------- */
document.querySelectorAll(".faq-item").forEach(item=>{
  const q = item.querySelector(".faq-q");
  const a = item.querySelector(".faq-a");
  q.addEventListener("click", ()=>{
    const open = item.classList.contains("open");
    document.querySelectorAll(".faq-item.open").forEach(o=>{
      o.classList.remove("open");
      o.querySelector(".faq-a").style.maxHeight = null;
    });
    if(!open){
      item.classList.add("open");
      a.style.maxHeight = a.scrollHeight + "px";
    }
  });
});
</script>
</body>
</html>
