<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Upravnik Zgrade - Sistem za upravljanje zgradama</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Styles -->
        <style>
            *, ::after, ::before { box-sizing: border-box; margin: 0; padding: 0; }
            
            :root {
                --primary: #3b82f6;
                --primary-dark: #2563eb;
                --secondary: #0f172a;
                --accent: #06b6d4;
                --gray-50: #f8fafc;
                --gray-100: #f1f5f9;
                --gray-200: #e2e8f0;
                --gray-300: #cbd5e1;
                --gray-400: #94a3b8;
                --gray-500: #64748b;
                --gray-600: #475569;
                --gray-700: #334155;
                --gray-800: #1e293b;
                --gray-900: #0f172a;
            }
            
            html {
                line-height: 1.5;
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
            }
            
            body {
                font-family: 'Inter', sans-serif;
                background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
                min-height: 100vh;
                color: white;
                overflow-x: hidden;
            }
            
            /* Animated background */
            .bg-pattern {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-image: 
                    radial-gradient(circle at 25% 25%, rgba(59, 130, 246, 0.1) 0%, transparent 50%),
                    radial-gradient(circle at 75% 75%, rgba(6, 182, 212, 0.1) 0%, transparent 50%);
                pointer-events: none;
                z-index: 0;
            }
            
            .floating-shapes {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                overflow: hidden;
                pointer-events: none;
                z-index: 0;
            }
            
            .shape {
                position: absolute;
                border-radius: 50%;
                background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(6, 182, 212, 0.1));
                animation: float 20s infinite ease-in-out;
            }
            
            .shape:nth-child(1) { width: 400px; height: 400px; top: -100px; left: -100px; animation-delay: 0s; }
            .shape:nth-child(2) { width: 300px; height: 300px; top: 50%; right: -100px; animation-delay: -5s; }
            .shape:nth-child(3) { width: 200px; height: 200px; bottom: -50px; left: 30%; animation-delay: -10s; }
            
            @keyframes float {
                0%, 100% { transform: translateY(0) rotate(0deg); }
                50% { transform: translateY(-30px) rotate(180deg); }
            }
            
            .container {
                position: relative;
                z-index: 1;
                max-width: 1200px;
                margin: 0 auto;
                padding: 2rem;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }
            
            /* Header */
            .header {
                text-align: center;
                margin-bottom: 4rem;
            }
            
            .logo-container {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 100px;
                height: 100px;
                background: linear-gradient(135deg, var(--primary), var(--accent));
                border-radius: 24px;
                margin-bottom: 2rem;
                box-shadow: 0 20px 40px rgba(59, 130, 246, 0.3);
                animation: pulse 3s infinite ease-in-out;
            }
            
            @keyframes pulse {
                0%, 100% { transform: scale(1); box-shadow: 0 20px 40px rgba(59, 130, 246, 0.3); }
                50% { transform: scale(1.05); box-shadow: 0 25px 50px rgba(59, 130, 246, 0.4); }
            }
            
            .logo-icon {
                width: 60px;
                height: 60px;
                fill: white;
            }
            
            .title {
                font-size: 3.5rem;
                font-weight: 800;
                margin-bottom: 1rem;
                background: linear-gradient(135deg, #ffffff 0%, #94a3b8 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                letter-spacing: -0.02em;
            }
            
            .subtitle {
                font-size: 1.25rem;
                color: var(--gray-400);
                max-width: 600px;
                margin: 0 auto 3rem;
                line-height: 1.8;
            }
            
            /* CTA Button */
            .cta-container {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 1.5rem;
            }
            
            .cta-button {
                display: inline-flex;
                align-items: center;
                gap: 0.75rem;
                padding: 1.25rem 3rem;
                background: linear-gradient(135deg, var(--primary), var(--primary-dark));
                color: white;
                font-size: 1.125rem;
                font-weight: 600;
                border-radius: 16px;
                text-decoration: none;
                transition: all 0.3s ease;
                box-shadow: 0 10px 30px rgba(59, 130, 246, 0.4);
            }
            
            .cta-button:hover {
                transform: translateY(-3px);
                box-shadow: 0 15px 40px rgba(59, 130, 246, 0.5);
            }
            
            .cta-button svg {
                width: 24px;
                height: 24px;
                transition: transform 0.3s ease;
            }
            
            .cta-button:hover svg {
                transform: translateX(5px);
            }
            
            /* Features Grid */
            .features {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
                gap: 1.5rem;
                margin-top: 4rem;
            }
            
            .feature-card {
                background: rgba(255, 255, 255, 0.03);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.08);
                border-radius: 20px;
                padding: 2rem;
                transition: all 0.3s ease;
            }
            
            .feature-card:hover {
                background: rgba(255, 255, 255, 0.06);
                border-color: rgba(59, 130, 246, 0.3);
                transform: translateY(-5px);
            }
            
            .feature-icon {
                width: 56px;
                height: 56px;
                background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(6, 182, 212, 0.2));
                border-radius: 14px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 1.25rem;
            }
            
            .feature-icon svg {
                width: 28px;
                height: 28px;
                stroke: var(--primary);
            }
            
            .feature-title {
                font-size: 1.125rem;
                font-weight: 600;
                margin-bottom: 0.75rem;
                color: white;
            }
            
            .feature-description {
                font-size: 0.9375rem;
                color: var(--gray-400);
                line-height: 1.7;
            }
            
            /* Footer */
            .footer {
                text-align: center;
                margin-top: 4rem;
                padding-top: 2rem;
                border-top: 1px solid rgba(255, 255, 255, 0.08);
            }
            
            .footer p {
                color: var(--gray-500);
                font-size: 0.875rem;
            }
            
            /* Responsive */
            @media (max-width: 768px) {
                .title { font-size: 2.5rem; }
                .subtitle { font-size: 1.1rem; }
                .container { padding: 1.5rem; }
                .features { grid-template-columns: 1fr; }
            }
        </style>
    </head>
    <body>
        <div class="bg-pattern"></div>
        <div class="floating-shapes">
            <div class="shape"></div>
            <div class="shape"></div>
            <div class="shape"></div>
        </div>
        
        <div class="container">
            <div class="header">
                <div class="logo-container">
                    <svg class="logo-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M3 21H21M4 18H20M6 18V13M10 18V13M14 18V13M18 18V13M12 7L20 13H4L12 7ZM12 7V3M12 3H9M12 3H15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                    </svg>
                </div>
                
                <h1 class="title">Upravnik Zgrade</h1>
                <p class="subtitle">
                    Moderni sistem za upravljanje stambenim zgradama. 
                    Pojednostavite administraciju, pratite troškove i komunicirajte sa vlasnicima na jednom mestu.
                </p>
                
                <div class="cta-container">
                    <a href="/admin" class="cta-button">
                        <span>Uđi u aplikaciju</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                </div>
            </div>
            
            <div class="features">
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" />
                        </svg>
                    </div>
                    <h3 class="feature-title">Upravljanje jedinicama</h3>
                    <p class="feature-description">Evidencija svih stanova, lokala i garaža sa detaljnim informacijama o vlasnicima i stanarima.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                        </svg>
                    </div>
                    <h3 class="feature-title">Finansije i naplata</h3>
                    <p class="feature-description">Automatsko generisanje uplatnica, praćenje transakcija i pregled stanja rezervnog fonda.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </div>
                    <h3 class="feature-title">Dokumentacija</h3>
                    <p class="feature-description">Centralizovano čuvanje svih ugovora, zapisnika i važnih dokumenata zgrade.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                    </div>
                    <h3 class="feature-title">Izveštaji</h3>
                    <p class="feature-description">Detaljni finansijski izveštaji, statistike i analitika za transparentno poslovanje.</p>
                </div>
            </div>
            
            <div class="footer">
                <p>&copy; {{ date('Y') }} Upravnik Zgrade. Sva prava zadržana.</p>
            </div>
        </div>
    </body>
</html>
