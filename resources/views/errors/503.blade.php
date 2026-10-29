<!DOCTYPE html>
<html lang="ms">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow">
        <title>Sedang Diselenggara · SalamHaji Travel &amp; Tours</title>
        <style>
            :root {
                color-scheme: light;
            }

            * {
                box-sizing: border-box;
            }

            body {
                background: #f8f5ee;
                color: #083d31;
                font-family: Arial, Helvetica, sans-serif;
                margin: 0;
                min-height: 100vh;
            }

            .maintenance-page {
                align-items: center;
                display: flex;
                isolation: isolate;
                justify-content: center;
                min-height: 100vh;
                overflow: hidden;
                padding: 40px 24px;
                position: relative;
            }

            .maintenance-page::before {
                background:
                    linear-gradient(90deg, rgba(250, 248, 241, 0.94) 0%, rgba(250, 248, 241, 0.7) 36%, rgba(250, 248, 241, 0.42) 64%, rgba(250, 248, 241, 0.86) 100%),
                    linear-gradient(180deg, rgba(255, 255, 255, 0.76), rgba(255, 255, 255, 0.6)),
                    url('{{ asset('darul-shofi/images/hero/umrah-premium-makkah-hero.png') }}') center / cover no-repeat;
                content: '';
                inset: 0;
                position: absolute;
                z-index: -2;
            }

            .maintenance-page::after,
            .ornament {
                background:
                    linear-gradient(30deg, transparent 48%, rgba(205, 160, 61, 0.18) 49% 51%, transparent 52%) 0 0 / 38px 38px,
                    linear-gradient(-30deg, transparent 48%, rgba(205, 160, 61, 0.18) 49% 51%, transparent 52%) 0 0 / 38px 38px;
                content: '';
                height: 300px;
                opacity: 0.74;
                pointer-events: none;
                position: absolute;
                width: 300px;
                z-index: -1;
            }

            .maintenance-page::after {
                left: -120px;
                top: -116px;
                transform: rotate(12deg);
            }

            .ornament {
                bottom: -176px;
                right: -104px;
                transform: rotate(-18deg);
            }

            .maintenance-card {
                background: rgba(255, 255, 255, 0.94);
                border: 1px solid rgba(209, 196, 169, 0.72);
                border-radius: 30px;
                box-shadow: 0 28px 74px rgba(35, 43, 36, 0.16);
                max-width: 735px;
                overflow: hidden;
                padding: 40px 58px 36px;
                position: relative;
                text-align: center;
                width: 100%;
            }

            .maintenance-card::before {
                background: radial-gradient(circle at bottom right, rgba(227, 191, 111, 0.22), rgba(255, 255, 255, 0) 69%);
                content: '';
                height: 210px;
                left: -102px;
                position: absolute;
                top: -120px;
                width: 210px;
            }

            .brand {
                display: block;
                height: auto;
                margin: 0 auto 24px;
                max-width: 218px;
                position: relative;
                width: 58%;
            }

            .illustration {
                display: block;
                height: 138px;
                margin: 0 auto 16px;
                max-width: 100%;
                position: relative;
                width: 236px;
            }

            h1 {
                color: #064637;
                font-family: Georgia, 'Times New Roman', serif;
                font-size: clamp(38px, 6vw, 58px);
                font-weight: 700;
                letter-spacing: -0.035em;
                line-height: 0.98;
                margin: 0 auto;
                max-width: 570px;
                position: relative;
            }

            .heading-rule {
                background: #d6a433;
                border-radius: 999px;
                height: 3px;
                margin: 22px auto 18px;
                position: relative;
                width: 52px;
            }

            .message {
                color: #53635d;
                font-size: 18px;
                line-height: 1.55;
                margin: 0 auto;
                max-width: 560px;
                position: relative;
            }

            .status {
                align-items: center;
                background: linear-gradient(100deg, #fff7df, #fdf0cd);
                border: 1px solid rgba(213, 166, 56, 0.18);
                border-radius: 999px;
                color: #a97008;
                display: inline-flex;
                font-size: 14px;
                font-weight: 700;
                gap: 10px;
                letter-spacing: 0.045em;
                margin-top: 24px;
                padding: 13px 27px;
                position: relative;
                text-transform: uppercase;
            }

            .status svg {
                height: 23px;
                width: 23px;
            }

            .retry-note {
                color: #90712e;
                font-size: 14px;
                font-weight: 700;
                margin: 17px 0 0;
                position: relative;
            }

            .contact {
                align-items: center;
                color: #69756f;
                display: flex;
                font-size: 14px;
                gap: 14px;
                justify-content: center;
                line-height: 1.55;
                margin: 26px auto 0;
                max-width: 510px;
                position: relative;
            }

            .contact::before,
            .contact::after {
                background: #e5ded0;
                content: '';
                flex: 1 1 65px;
                height: 1px;
                max-width: 150px;
            }

            .contact-mark {
                color: #d2a23a;
                font-size: 17px;
                line-height: 1;
            }

            @media (max-width: 640px) {
                .maintenance-page {
                    align-items: flex-start;
                    padding: 24px 16px;
                }

                .maintenance-card {
                    border-radius: 24px;
                    margin: auto 0;
                    padding: 32px 24px 28px;
                }

                .brand {
                    margin-bottom: 22px;
                    max-width: 192px;
                    width: 64%;
                }

                .illustration {
                    height: 116px;
                    margin-bottom: 12px;
                    width: 204px;
                }

                h1 {
                    font-size: clamp(35px, 11vw, 45px);
                    line-height: 1.01;
                }

                .message {
                    font-size: 16px;
                }

                .status {
                    font-size: 12px;
                    padding: 12px 18px;
                }

                .contact {
                    font-size: 13px;
                    gap: 10px;
                }

                .maintenance-page::after {
                    left: -194px;
                    top: -160px;
                }

                .ornament {
                    bottom: -205px;
                    right: -180px;
                }
            }
        </style>
    </head>
    <body>
        <main class="maintenance-page">
            <span class="ornament" aria-hidden="true"></span>

            <section class="maintenance-card" aria-labelledby="maintenance-heading">
                <img class="brand" src="{{ asset('darul-shofi/images/branding/salam-haji-logo.png') }}" alt="SalamHaji Travel &amp; Tours">

                <svg class="illustration" aria-hidden="true" viewBox="0 0 236 138" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M28 101C28 87.193 39.193 76 53 76C59.201 76 64.875 78.258 69.244 81.997C76.595 67.17 92.013 57 109.807 57C131.992 57 150.483 72.812 154.629 93.79C158.187 90.666 162.85 88.767 167.956 88.767C179.11 88.767 188.15 97.807 188.15 108.961H47.652C36.797 108.961 28 101.163 28 91.544V101Z" fill="#E6F5EC"/>
                    <path d="M77 53C77 32.013 94.013 15 115 15C135.987 15 153 32.013 153 53C153 73.987 135.987 91 115 91C94.013 91 77 73.987 77 53Z" fill="#D8EFE4"/>
                    <path d="M115 29L120.716 39.67L132.827 38.827L131.984 50.938L142.654 56.654L131.984 62.37L132.827 74.481L120.716 73.638L115 84.308L109.284 73.638L97.173 74.481L98.016 62.37L87.346 56.654L98.016 50.938L97.173 38.827L109.284 39.67L115 29Z" fill="#13735A"/>
                    <circle cx="115" cy="56.654" r="12.1" fill="#F8FCF8"/>
                    <circle cx="157" cy="89" r="21" fill="#D7A32C"/>
                    <path d="M157 77V89L165.4 94.1" stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="3.4"/>
                    <path d="M63 37L66.3 43.7L73 47L66.3 50.3L63 57L59.7 50.3L53 47L59.7 43.7L63 37Z" fill="#D7A32C"/>
                    <path d="M176 30L178.9 35.1L184 38L178.9 40.9L176 46L173.1 40.9L168 38L173.1 35.1L176 30Z" fill="#D7A32C"/>
                    <path d="M193 53L195 56.5L198.5 58.5L195 60.5L193 64L191 60.5L187.5 58.5L191 56.5L193 53Z" fill="#9BCFBA"/>
                </svg>

                <h1 id="maintenance-heading">Kami akan kembali<br>sebentar lagi.</h1>
                <span class="heading-rule" aria-hidden="true"></span>
                <p class="message">Kami sedang menjalankan kerja penyelenggaraan untuk memastikan pengalaman anda lebih lancar. Terima kasih atas kesabaran anda.</p>

                <p class="status">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M14.5 6.5L17.5 9.5M5.8 20.2L3.8 18.2L13.9 8.1L15.9 10.1L5.8 20.2ZM14.7 4.7L16.6 2.8C17.12 2.28 17.96 2.28 18.48 2.8L21.2 5.52C21.72 6.04 21.72 6.88 21.2 7.4L19.3 9.3L14.7 4.7ZM9.2 13.2L10.8 14.8L8.8 16.8L7.2 15.2L9.2 13.2Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"/>
                    </svg>
                    503 · Sedang diselenggara
                </p>

                @if (filled($retryAfter ?? null))
                    <p class="retry-note">Sila cuba semula dalam kira-kira seminit.</p>
                @endif

                <p class="contact"><span class="contact-mark" aria-hidden="true">✦</span><span>Jika memerlukan bantuan segera, sila hubungi kami melalui WhatsApp atau emel.</span><span class="contact-mark" aria-hidden="true">✦</span></p>
            </section>
        </main>
    </body>
</html>
