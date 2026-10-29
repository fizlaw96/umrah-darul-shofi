<!DOCTYPE html>
<html lang="ms">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow">
        <title>Sedang Diselenggara · SalamHaji Travel & Tours</title>
        <style>
            :root { color-scheme: light; }
            * { box-sizing: border-box; }
            body {
                align-items: center;
                background: #f7f5f0;
                color: #0b3b30;
                display: flex;
                font-family: Arial, Helvetica, sans-serif;
                justify-content: center;
                margin: 0;
                min-height: 100vh;
                padding: 24px;
            }
            main {
                background: #ffffff;
                border: 1px solid #e7e1d5;
                border-radius: 28px;
                box-shadow: 0 22px 55px rgba(7, 62, 52, 0.12);
                max-width: 620px;
                overflow: hidden;
                padding: 52px 36px 42px;
                position: relative;
                text-align: center;
                width: 100%;
            }
            main::before {
                background: #c69a3a;
                border-radius: 999px;
                content: '';
                height: 180px;
                left: -94px;
                opacity: 0.16;
                position: absolute;
                top: -92px;
                width: 180px;
            }
            .brand { font-size: 13px; font-weight: 700; letter-spacing: 0.16em; margin: 0; position: relative; text-transform: uppercase; }
            .icon {
                align-items: center;
                background: #edf7ef;
                border-radius: 999px;
                color: #0b5c4b;
                display: inline-flex;
                font-size: 31px;
                height: 72px;
                justify-content: center;
                margin: 30px 0 20px;
                position: relative;
                width: 72px;
            }
            h1 { font-family: Georgia, 'Times New Roman', serif; font-size: clamp(32px, 7vw, 46px); line-height: 1.08; margin: 0; position: relative; }
            p { color: #5a6c64; font-size: 17px; line-height: 1.65; margin: 20px auto 0; max-width: 470px; position: relative; }
            .note { color: #9a762e; font-size: 14px; font-weight: 700; margin-top: 28px; }
            .code { color: #9a762e; font-size: 12px; font-weight: 700; letter-spacing: 0.12em; margin-top: 30px; }
            @media (max-width: 480px) {
                main { border-radius: 22px; padding: 42px 24px 34px; }
                p { font-size: 16px; }
            }
        </style>
    </head>
    <body>
        <main>
            <p class="brand">SalamHaji Travel &amp; Tours</p>
            <span class="icon" aria-hidden="true">✦</span>
            <h1>Kami akan kembali sebentar lagi.</h1>
            <p>Kami sedang menjalankan kerja penyelenggaraan untuk memastikan pengalaman anda lebih lancar. Terima kasih atas kesabaran anda.</p>
            @if (filled($retryAfter ?? null))
                <p class="note">Sila cuba semula dalam kira-kira seminit.</p>
            @endif
            <p class="code">503 · SEDANG DISELENGGARA</p>
        </main>
    </body>
</html>
